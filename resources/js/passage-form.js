import { upsertPassage, savePhoto, getPassagesByStatus, getPhotosByPassage, markStatus, countPendingAll } from './offline-queue.js';
import { processPhoto } from './photo-pipeline.js';

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

        // ---- photos : [{ clientUuid, passage_client_uuid, previewUrl, status, idbId }] ----
        photos: [],

        // ---- état UI ----
        online:      navigator.onLine,
        warnings:    [],
        saving:      false,
        conflictMsg: '',
        visitedAt:   new Date().toISOString(),

        // ---- timer debounce partagé (IIFE) ----
        _saveTimer: null,

        async init() {
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
            this.$watch('actions',      () => this._debouncedSave());
            this.$watch('notes',        () => this._debouncedSave());
            this.$watch('notesPrivees', () => this._debouncedSave());

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
            const v = parseFloat(this[field]) || 0;
            this[field] = (v + step).toFixed(precision);
        },

        /**
         * Décrémente le champ de mesure (plancher à 0).
         */
        decr(field, step = 0.1, precision = 1) {
            const v = parseFloat(this[field]) || 0;
            const next = Math.max(0, v - step);
            this[field] = next.toFixed(precision);
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
                    `${label} ${num} est hors de la plage recommandée [${min}, ${max}]. La saisie est enregistrée — vérifiez votre lecture.`
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
                ph_avant:      this.ph_avant     === '' ? null : parseFloat(this.ph_avant),
                ph_apres:      this.ph_apres     === '' ? null : parseFloat(this.ph_apres),
                chlore_libre:  this.chlore_libre === '' ? null : parseFloat(this.chlore_libre),
                chlore_total:  this.chlore_total === '' ? null : parseFloat(this.chlore_total),
                tac:           this.tac          === '' ? null : parseFloat(this.tac),
                sel_g_l:       this.sel_g_l      === '' ? null : parseFloat(this.sel_g_l),
                th:            this.th           === '' ? null : parseFloat(this.th),
                actions:       this.actions,
                notes:         this.notes        || null,
                notes_privees: this.notesPrivees || null,
            };
        },

        // -------- Submit + flush queue --------

        async submit() {
            this.saving = true;
            try {
                await this._saveToIDB('pending');
                if (navigator.onLine) {
                    await this._flushQueue();
                }
            } finally {
                this.saving = false;
            }
        },

        /**
         * Flush tous les passages 'pending' en IDB vers le serveur.
         * Appelé au submit, au retour online, et au visibilitychange.
         */
        async _flushQueue() {
            const pending = await getPassagesByStatus('pending');
            for (const item of pending) {
                await this._uploadPassage(item);
            }
            await this.$store.offlineQueue.refresh();
        },

        /**
         * Upload un passage vers POST /api/passages avec backoff 2s→8s→30s (D-45).
         * Gestion 409 : passage déjà clos (D-40).
         */
        async _uploadPassage(item) {
            const delays = [2000, 8000, 30000];
            for (let attempt = 0; attempt < delays.length; attempt++) {
                try {
                    await markStatus('passages', item.id, 'uploading');
                    const res = await fetch('/api/passages', {
                        method:      'POST',
                        headers:     this._headers(true),
                        credentials: 'same-origin',
                        body:        item.payload_json,
                    });

                    if (res.status === 409) {
                        // Passage déjà clos (D-40) — marquer synced pour purger la queue
                        await markStatus('passages', item.id, 'synced');
                        this.conflictMsg = "Ce passage a déjà été clos. Tes modifications n'ont pas été enregistrées.";
                        return;
                    }

                    if (res.ok) {
                        await markStatus('passages', item.id, 'synced');
                        await this._uploadPhotosForPassage(item.client_uuid);
                        return;
                    }

                    // Erreur serveur (422, 500, etc.) → retry
                    throw new Error('Server error ' + res.status);

                } catch (e) {
                    console.warn('[passage-form] upload attempt', attempt + 1, 'failed', e);
                    if (attempt < delays.length - 1) {
                        await new Promise((r) => setTimeout(r, delays[attempt]));
                    } else {
                        await markStatus('passages', item.id, 'error', (item.attempts || 0) + 1);
                    }
                }
            }
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
    };
}
