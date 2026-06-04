import { upsertPassage, savePhoto, getPassagesByStatus, getPhotosByPassage, markStatus, countPendingAll, openOfflineDB } from './offline-queue.js';
import { processPhoto } from './photo-pipeline.js';
import { flushPipeline, syncProduits } from './upload-pipeline.js';

/**
 * Plages soft de validation des mesures (D-63).
 * En dehors de ces plages, un toast warning est affiché mais la saisie continue.
 */
const SOFT_RANGES = {
    ph_avant:     [5.0, 9.0],
    chlore_libre: [0, 10],
    chlore_total: [0, 15],
    tac:          [50, 300],
    sel_g_l:      [0, 8],
    th:           [0, 50],
};

const RANGE_LABELS = {
    ph_avant:     'pH',
    chlore_libre: 'Chlore libre',
    chlore_total: 'Chlore total',
    tac:          'TAC',
    sel_g_l:      'Sel',
    th:           'TH',
};

/**
 * Alpine.data('passageForm') factory.
 *
 * Implémente le cœur PASS-01, PASS-02, PASS-03, PASS-06 :
 * - Génère client_uuid via crypto.randomUUID() au mount (D-39)
 * - Auto-save draft en IDB à chaque interaction (debounced 500ms)
 * - Photos via processPhoto() Plan 02-04 (HEIC→JPEG, EXIF, Canvas 0.80)
 * - Submit → status pending → _flushQueue() backoff 2s/8s/30s
 * - Écoute online + visibilitychange (fallback iOS — Pitfall 1)
 * - Gestion 409 (passage déjà clos — D-40)
 *
 * @param {Object} initialData - { clientId?: number, piscineId?: number }
 */
export function passageForm(initialData = {}) {
    return {
        // ---- identité ----
        clientUuid: '',
        idbId: null,
        clientId: initialData.clientId ?? null,
        piscineId: initialData.piscineId ?? null,
        clients: initialData.clients ?? [], // liste offline-safe pour le sélecteur quand la saisie est ouverte sans client

        // ---- mesures (strings pour input number, converties au submit) ----
        ph_avant:     '',
        ph_apres:     '',
        chlore_libre: '',
        chlore_total: '',
        tac:          '',
        sel_g_l:      '',
        th:           '',

        // ---- actions menées ----
        actions: [],
        actionsAvailable: [
            'Mesure pH',
            'Mesure chlore',
            'Nettoyage skimmers',
            'Brossage parois',
            'Aspirateur fond',
            'Lavage filtre',
            'Ajout chlore',
            'Ajout pH-',
            'Vidange partielle',
            'Vérification équipements',
        ],

        // ---- notes ----
        notes:        '',
        notesPrivees: '',

        // ---- produits utilisés (chimie) — admin-5 ----
        produitsDisponibles: initialData.produits ?? [],  // [{id, libelle, prix_ht}] pré-injectés offline-safe
        produitIds:      [],   // ids cochés
        produitQuantites: {},  // { [produit_id]: quantite_string }

        // ---- photos : [{ clientUuid, passage_client_uuid, previewUrl, status, idbId }] ----
        photos: [],

        // ---- état UI ----
        online:      navigator.onLine,
        warnings:    [],
        saving:      false,
        saved:       false,  // écran de confirmation après un enregistrement réussi
        saveResult:  null,   // 'synced' (parti au serveur) | 'queued' (en attente réseau)
        conflictMsg: '',
        uploadError: '',     // motif d'un refus serveur permanent (422) — corrige & réenregistre
        visitedAt:   new Date().toISOString(),

        // ---- timer debounce partagé (IIFE) ----
        _saveTimer: null,

        async init() {
            // 0. Défaut pH 7.4 (mesure routinière). Les autres mesures restent vides et sont
            //    saisies réellement sur le terrain — on ne fabrique jamais un relevé manquant.
            if (this.ph_avant === '') this.ph_avant = '7.4';

            // 1. Générer client_uuid et persister immédiatement en IDB (D-39)
            this.clientUuid = crypto.randomUUID();
            this.idbId = await this._saveToIDB('draft');

            // 2. Listeners réseau (Pitfall 1 — fallback iOS pour Background Sync API)
            window.addEventListener('online', () => {
                this.online = true;
                this._flushQueue();
            });
            window.addEventListener('offline', () => {
                this.online = false;
            });
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && navigator.onLine) {
                    this._flushQueue();
                }
            });

            // 3. Écouter l'event custom du sync-drawer "Synchroniser maintenant"
            window.addEventListener('passage-form:flush', () => {
                this._flushQueue();
            });

            // 4. Watchers mesures : validation soft + auto-save draft
            this.$watch('ph_avant',     () => { this._checkRange('ph_avant',     this.ph_avant);     this._debouncedSave(); });
            this.$watch('ph_apres',     () => { this._debouncedSave(); });
            this.$watch('chlore_libre', () => { this._checkRange('chlore_libre', this.chlore_libre); this._debouncedSave(); });
            this.$watch('chlore_total', () => { this._checkRange('chlore_total', this.chlore_total); this._debouncedSave(); });
            this.$watch('tac',          () => { this._checkRange('tac',          this.tac);          this._debouncedSave(); });
            this.$watch('sel_g_l',      () => { this._checkRange('sel_g_l',      this.sel_g_l);      this._debouncedSave(); });
            this.$watch('th',           () => { this._checkRange('th',           this.th);           this._debouncedSave(); });
            this.$watch('actions',           () => this._debouncedSave());
            this.$watch('notes',             () => this._debouncedSave());
            this.$watch('notesPrivees',      () => this._debouncedSave());
            this.$watch('produitIds',        () => this._debouncedSave());
            this.$watch('produitQuantites',  () => this._debouncedSave());

            // 5. Refresh badge initial
            this.$store.offlineQueue.refresh().catch(() => {});
        },

        // -------- Mesures helpers (steppers ±) --------

        /**
         * Incrémente le champ de mesure.
         * @param {string} field
         * @param {number} step
         * @param {number} precision - décimales de toFixed
         */
        incr(field, step = 0.1, precision = 1) {
            const v = this._num(this[field]) ?? 0;
            this[field] = (v + step).toFixed(precision);
        },

        /**
         * Décrémente le champ de mesure (plancher à 0).
         */
        decr(field, step = 0.1, precision = 1) {
            const v = this._num(this[field]) ?? 0;
            const next = Math.max(0, v - step);
            this[field] = next.toFixed(precision);
        },

        /**
         * Parse une saisie terrain (virgule FR « 7,2 » ou point « 7.2 ») en nombre.
         * Retourne null si vide / non numérique — jamais NaN dans le payload.
         */
        _num(value) {
            if (value === '' || value === null || value === undefined) return null;
            const n = parseFloat(String(value).replace(',', '.'));
            return Number.isNaN(n) ? null : n;
        },

        _checkRange(field, val) {
            const num = parseFloat(val);
            if (isNaN(num) || val === '') return;
            const range = SOFT_RANGES[field];
            if (!range) return;
            const [min, max] = range;
            if (num < min || num > max) {
                const label = RANGE_LABELS[field] ?? field;
                this._pushWarning(
                    `${label} ${num} est hors de la plage recommandée [${min}, ${max}]. La saisie est enregistrée — vérifie ta lecture.`
                );
            }
        },

        _pushWarning(msg) {
            const id = Date.now() + Math.random();
            this.warnings.push({ id, msg });
            setTimeout(() => {
                this.warnings = this.warnings.filter((w) => w.id !== id);
            }, 4000);
        },

        // -------- Actions checkbox helpers --------

        toggleAction(name) {
            if (this.actions.includes(name)) {
                this.actions = this.actions.filter((a) => a !== name);
            } else {
                this.actions = [...this.actions, name];
            }
        },

        isActionSelected(name) {
            return this.actions.includes(name);
        },

        // -------- Produits helpers (chimie — admin-5) --------

        toggleProduit(id) {
            if (this.produitIds.includes(id)) {
                this.produitIds = this.produitIds.filter((x) => x !== id);
                // Nettoyer la quantité à la dé-sélection
                const q = { ...this.produitQuantites };
                delete q[id];
                this.produitQuantites = q;
            } else {
                this.produitIds = [...this.produitIds, id];
            }
        },

        isProduitSelected(id) {
            return this.produitIds.includes(id);
        },

        // -------- Sélecteur client (saisie ouverte sans client_id) --------

        // Piscines du client sélectionné (pour le second sélecteur si le client en a plusieurs).
        get selectedClientPiscines() {
            const client = this.clients.find((c) => String(c.id) === String(this.clientId));
            return client?.piscines ?? [];
        },

        // Auto-sélectionne la piscine quand le client n'en a qu'une.
        onClientChange() {
            const piscines = this.selectedClientPiscines;
            this.piscineId = piscines.length === 1 ? piscines[0].id : null;
        },

        // -------- Photos --------

        /**
         * Handler <input type="file" accept="image/*" capture="environment"> @change.
         * Chaque fichier passe par processPhoto() (HEIC→JPEG, EXIF, Canvas ≤2048 JPEG 0.80).
         * Le blob compressé est persisté en IDB store 'photos' (D-42).
         */
        async onPhotoSelect(event) {
            const files = Array.from(event.target.files ?? []);
            event.target.value = ''; // reset pour autoriser re-upload du même fichier

            for (const file of files) {
                const photoUuid = crypto.randomUUID();
                const tempPreview = URL.createObjectURL(file);

                const photoEntry = {
                    clientUuid:           photoUuid,
                    passage_client_uuid:  this.clientUuid,
                    previewUrl:           tempPreview,
                    status:               'processing',
                    idbId:                null,
                };
                this.photos.push(photoEntry);

                try {
                    const blob = await processPhoto(file);
                    const idbId = await savePhoto({
                        client_uuid:          photoUuid,
                        passage_client_uuid:  this.clientUuid,
                        blob,
                        status:               'pending',
                        attempts:             0,
                        captured_at:          new Date().toISOString(),
                    });
                    photoEntry.idbId    = idbId;
                    photoEntry.status   = 'pending';
                    // Remplacer la preview par le blob compressé (économise la RAM)
                    URL.revokeObjectURL(tempPreview);
                    photoEntry.previewUrl = URL.createObjectURL(blob);
                } catch (e) {
                    console.error('[passage-form] photo processing failed', e);
                    photoEntry.status = 'error';
                }
            }
        },

        // -------- IDB save (auto-save draft) --------

        _debouncedSave() {
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this._saveToIDB('draft'), 500);
        },

        async _saveToIDB(status = 'draft') {
            const payload = this._toPayload();
            const record = {
                client_uuid:     this.clientUuid,
                payload_json:    JSON.stringify(payload),
                status,
                attempts:        0,
                created_at:      payload.visited_at,
                last_attempt_at: null,
            };
            // Only attach the inline keyPath when we already have one. An autoIncrement
            // store generates the key when `id` is ABSENT; a present-but-undefined `id`
            // throws DataError ("not a valid key"), so never include it on first save.
            if (this.idbId != null) record.id = this.idbId;
            const id = await upsertPassage(record);
            if (!this.idbId) this.idbId = id;
            await this.$store.offlineQueue.refresh();
            return id;
        },

        _toPayload() {
            return {
                client_uuid:   this.clientUuid,
                client_id:     this.clientId,
                piscine_id:    this.piscineId,
                visited_at:    this.visitedAt,
                ph_avant:      this._num(this.ph_avant),
                ph_apres:      this._num(this.ph_apres),
                chlore_libre:  this._num(this.chlore_libre),
                chlore_total:  this._num(this.chlore_total),
                tac:           this._num(this.tac),
                sel_g_l:       this._num(this.sel_g_l),
                th:            this._num(this.th),
                actions:       this.actions,
                notes:         this.notes        || null,
                notes_privees: this.notesPrivees || null,
                produits:      this.produitIds.map((id) => ({
                    produit_id: id,
                    quantite:   this._num(this.produitQuantites[id] ?? ''),
                })),
            };
        },

        // -------- Submit + flush queue --------

        async submit() {
            // Garde anti-orphelin (intégrité) : un passage doit appartenir à un client.
            if (!this.clientId) {
                this.conflictMsg = 'Choisis un client avant d\'enregistrer ce passage.';
                return;
            }
            this.saving = true;
            this.uploadError = ''; // on repart d'une ardoise propre à chaque tentative
            try {
                await this._saveToIDB('pending');
                if (navigator.onLine) {
                    await this._flushQueue();
                    const mine = (p) => p.client_uuid === this.clientUuid;
                    // 'error' = refus permanent (422 — données invalides) : ré-essayer
                    // n'y changera rien. On NE prétend PAS que c'est sauvegardé ; le
                    // message d'erreur (uploadError) est déjà affiché et l'opérateur
                    // corrige puis ré-enregistre (un nouveau submit repasse le record
                    // en 'pending' avec la valeur corrigée).
                    const failed = (await getPassagesByStatus('error')).some(mine);
                    if (failed) {
                        return; // reste sur le formulaire, erreur visible, pas d'écran « enregistré »
                    }
                    // 'pending' restant = échec transitoire (réseau/5xx) déjà ré-essayé :
                    // il repartira au prochain flush (online / visibilitychange).
                    const stillPending = (await getPassagesByStatus('pending')).some(mine);
                    this.saveResult = stillPending ? 'queued' : 'synced';
                } else {
                    this.saveResult = 'queued';
                }
                this.saved = true;
            } finally {
                this.saving = false;
            }
        },

        /**
         * Flush tous les passages 'pending' en IDB vers le serveur.
         * Délègue à flushPipeline() (upload-pipeline.js) — logique unique, pas de doublon.
         * Appelé au submit, au retour online, et au visibilitychange.
         *
         * After pipeline completes, upload photos for any newly-synced passage
         * (photo upload is passageForm-specific — only relevant when the create
         * screen is active, which is the only place photos are added to IDB).
         */
        async _flushQueue() {
            // Run passage flush pipeline (shared with store)
            const results = await flushPipeline();

            // Upload photos for passages that were just synced by this flush
            // (flushPipeline marks them synced; we find them and upload photos)
            const db = await openOfflineDB();
            const allPassages = await db.getAll('passages');
            for (const p of allPassages) {
                if (p.status === 'synced') {
                    await this._uploadPhotosForPassage(p.client_uuid);
                }
            }

            await this.$store.offlineQueue.refresh();
        },

        /**
         * Délègue à syncProduits() d'upload-pipeline.js.
         */
        async _syncProduits(item) {
            return syncProduits(item);
        },

        /**
         * Upload toutes les photos (non synced) d'un passage (D-46).
         */
        async _uploadPhotosForPassage(passageClientUuid) {
            const photos = await getPhotosByPassage(passageClientUuid);
            for (const photo of photos.filter((p) => p.status !== 'synced')) {
                await this._uploadPhoto(photo);
            }
        },

        /**
         * Upload une photo vers POST /api/passages/{passage_client_uuid}/photos (FormData).
         * NE PAS définir Content-Type — laisser le navigateur poser le boundary multipart.
         */
        async _uploadPhoto(photo) {
            const delays = [2000, 8000, 30000];
            for (let attempt = 0; attempt < delays.length; attempt++) {
                try {
                    await markStatus('photos', photo.id, 'uploading');

                    const fd = new FormData();
                    fd.append('photo',       photo.blob, `${photo.client_uuid}.jpg`);
                    fd.append('client_uuid', photo.client_uuid);
                    fd.append('captured_at', photo.captured_at);

                    const res = await fetch(`/api/passages/${photo.passage_client_uuid}/photos`, {
                        method:      'POST',
                        headers:     this._headers(false), // pas de Content-Type pour FormData
                        credentials: 'same-origin',
                        body:        fd,
                    });

                    if (res.ok) {
                        await markStatus('photos', photo.id, 'synced');
                        // Mettre à jour le statut dans le tableau Alpine si la photo est affichée
                        const local = this.photos.find((p) => p.clientUuid === photo.client_uuid);
                        if (local) local.status = 'synced';
                        return;
                    }

                    throw new Error('Photo upload failed ' + res.status);

                } catch (e) {
                    console.warn('[passage-form] photo upload attempt', attempt + 1, 'failed', e);
                    if (attempt < delays.length - 1) {
                        await new Promise((r) => setTimeout(r, delays[attempt]));
                    } else {
                        await markStatus('photos', photo.id, 'error', (photo.attempts || 0) + 1);
                        const local = this.photos.find((p) => p.clientUuid === photo.client_uuid);
                        if (local) local.status = 'error';
                    }
                }
            }
        },

        /**
         * Construit les headers fetch.
         * isJson=true → ajoute Content-Type: application/json.
         * isJson=false → pas de Content-Type (FormData avec boundary auto).
         * Pitfall 5 : CSRF token lu depuis <meta name="csrf-token">.
         */
        _headers(isJson) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const h = {
                'X-CSRF-TOKEN':       csrf,
                'Accept':             'application/json',
                'X-Requested-With':   'XMLHttpRequest',
            };
            if (isJson) h['Content-Type'] = 'application/json';
            return h;
        },

        dismissConflict() {
            this.conflictMsg = '';
        },

        dismissUploadError() {
            this.uploadError = '';
        },
    };
}
