{{--
    Diagnostic piscine — wizard Livewire / Alpine (Plans 05-01 + 05-03)
    Surfaces : S2 (arbre symptôme), S3 (wizard chimie), S4 (disclaimer gate),
               S5 minimal (feuille résultat + dosage), S6 (escalade WhatsApp),
               S7 (lead capture)

    Architecture (D-01) :
    - Alpine    : navigation entre steps (step, mode, nodeId, wizardStep, sizeMode) — jamais lié Livewire
    - Livewire  : état serveur (disclaimerAccepted, champs mesures, lead, compute/persist)
    - wire:ignore.self sur la racine Alpine pour éviter le reset du step (Pitfall 1)

    DIAG-02 invariant : aucune formule arithmétique dans ce fichier ni dans le JS émis.
    L'arbre config est passé en Blade statique côté serveur (@js) — lecture seule pour Alpine.
    La surface × profondeur est géométrie, pas dosage — elle est calculée côté serveur dans volumeEffectif().

    Tokens : @theme exclusivement (resources/css/app.css) — jamais #000/#fff, jamais px arbitraire.
    Seul hex autorisé : #25D366 (WhatsApp channel).
    Color register : PRODUCT (sand surfaces, azure sur l'action primaire unique, warn pour la sécurité).
--}}

<div
    id="diagnostic-wizard-component"
    x-data="{
        step: 'mode',
        mode: null,
        nodeId: 'start',
        history: [],
        triedActions: [],
        resultId: null,

        // ── Wizard chimie (S3) ──────────────────────────────────
        wizardStep: 1,
        sizeMode: 'volume',    // 'volume' | 'surface'
        derivedVolume: 0,      // surface × profondeur (display only — calcul serveur)
        hasSel: false,

        // Récupère un nœud de question depuis le config
        getNode(id) {
            return @js($tree['questions'] ?? []) [id] ?? null;
        },

        // Récupère une feuille résultat depuis le config
        getResult(id) {
            return @js($tree['results'] ?? []) [id] ?? null;
        },

        // Navigue vers le nœud ou la feuille suivant
        advance(option) {
            const next = option.next;
            if (!next) return;
            this.history.push({ step: this.step, nodeId: this.nodeId, mode: this.mode });
            if (this.step === 'mode') {
                this.mode = option.value;
                // Synchroniser le mode côté serveur (wire:model.live non utilisé volontairement)
            }
            if (next.kind === 'result') {
                this.resultId = next.id;
                this.step = 'result';
                // Synchronise la feuille atteinte côté serveur pour l'escalade préemptive (Plan 05-04)
                $wire.setSymptomResult(next.id);
            } else if (next.kind === 'question') {
                this.nodeId = next.id;
                this.step = 'tree';
            } else if (next.kind === 'wizard') {
                this.step = 'wizard';
                this.wizardStep = 1;
            }
        },

        // Retour en arrière
        back() {
            if (this.history.length === 0) return;
            const prev = this.history.pop();
            this.step = prev.step;
            this.nodeId = prev.nodeId;
            this.mode = prev.mode;
            this.resultId = null;
        },

        // Basculer une action déjà tentée (multiselect chips)
        toggleTried(val) {
            const idx = this.triedActions.indexOf(val);
            if (idx >= 0) {
                this.triedActions.splice(idx, 1);
            } else {
                this.triedActions.push(val);
            }
            // Synchroniser côté serveur pour la persistance
            $wire.updateTriedActions(this.triedActions);
        },

        // Vérifier si une action est déjà tentée
        isTried(val) {
            return this.triedActions.includes(val);
        },

        // Mettre à jour le volume dérivé (affichage — calcul surface×profondeur purement géométrique)
        updateDerivedVolume() {
            const s = parseFloat(String($wire.surface ?? '').replace(',', '.')) || 0;
            const p = parseFloat(String($wire.profondeur ?? '').replace(',', '.')) || 0;
            this.derivedVolume = s && p ? Math.round(s * p * 10) / 10 : 0;
        },

        // Passer au step 2 du wizard chimie
        goToStep2() {
            this.wizardStep = 2;
        },

        // Retour au step 1
        backToStep1() {
            this.wizardStep = 1;
        },

        // ── Carnet local-only — DIAG-07 (Plan 05-06) ────────────────
        // 0 réseau, 0 synchro, 0 compte. Stocke uniquement le texte du résultat.
        // DIAG-02 : aucune formule de dose ici.

        carnetSaved: false,       // true dès que l'entrée courante est dans le carnet
        carnetEntryId: null,      // id de l'entrée carnet courante
        showCarnet: false,        // bascule l'affichage S9
        showClearConfirm: false,  // confirm inline destructif
        carnetEntries: [],        // liste chargée depuis localStorage

        // Sauvegarde l'entrée courante dans le carnet (appelé à la fin de computeAndPersist)
        saveToCarnet(serverId, symptome, diagnostic, confidence, mesuresCles, resultText) {
            if (this.carnetSaved) return; // évite les doublons
            if (!window.diagnosticCarnet) return;
            const id = serverId ? String(serverId) : 'local_' + Date.now();
            this.carnetEntryId = id;
            window.diagnosticCarnet.save({
                id,
                date:        new Date().toISOString(),
                symptome:    symptome || 'Diagnostic',
                diagnostic:  diagnostic || '',
                confidence:  confidence || 'indicatif',
                mesuresCles: mesuresCles || '',
                resultText:  resultText || '',
                serverId:    serverId ?? null,
                retested:    false,
                retestOk:    null,
            });
            this.carnetSaved = true;
        },

        // Charge les entrées du carnet depuis localStorage
        loadCarnetEntries() {
            if (!window.diagnosticCarnet) { this.carnetEntries = []; return; }
            this.carnetEntries = window.diagnosticCarnet.all();
        },

        // Formate une date ISO en lisible
        formatCarnetDate(isoDate) {
            try {
                return new Date(isoDate).toLocaleDateString('fr-FR', {
                    day: '2-digit', month: 'short', year: 'numeric'
                });
            } catch { return isoDate; }
        },

        // Libellé lisible du niveau de confiance (carnet, DIAG-07)
        confidenceLabel(level) {
            return ({ eleve: 'Confiance élevée', moyen: 'Confiance moyenne', indicatif: 'Indicatif' })[level] || 'Indicatif';
        },

        // Efface tout le carnet
        clearCarnet() {
            if (!window.diagnosticCarnet) return;
            window.diagnosticCarnet.clear();
            this.carnetEntries = [];
            this.showClearConfirm = false;
        },

        // Reprend un diagnostic passé (re-hydrate les données pour le parcours)
        resumeFromCarnet(entry) {
            // On retourne au mode de sélection avec l'entrée visible
            // (la re-saisie d'un nouveau diagnostic est en ligne — DIAG-02)
            this.showCarnet = false;
            this.step = 'mode';
            this.nodeId = 'start';
            this.history = [];
            this.resultId = null;
        },

        // ── Boucle de re-test (DIAG-06 réactif, Plan 05-06) ─────────
        // En session, in-browser, 0 push, 0 scheduler (V0)

        retestShown: false,   // le prompt re-test est visible
        retestAnswered: false, // l'utilisateur a répondu

        // Affiche le prompt re-test (appelé depuis un bouton « J'ai appliqué le plan »)
        showRetestPrompt() {
            this.retestShown = true;
        },

        // Re-test réussi : marque l'entrée carnet, note visuelle positive
        onRetestOui() {
            this.retestAnswered = true;
            if (this.carnetEntryId && window.diagnosticCarnet) {
                window.diagnosticCarnet.markRetested(this.carnetEntryId, true);
            }
        },

        // Re-test raté : déclenche l'escalade réactive via le hook Livewire (BLUEPRINT §4.2)
        onRetestNon() {
            this.retestAnswered = true;
            $wire.triggerRetestFailed(); // hook Plan 04 — déclenche escaladeNiveau = 'reactif'
            if (this.carnetEntryId && window.diagnosticCarnet) {
                window.diagnosticCarnet.markRetested(this.carnetEntryId, false);
            }
        }
    }"
    wire:ignore.self
    x-cloak
    class="w-full"
>

    {{-- ═══════════════════════════════════════════════════════════
         S0 — Choix du mode (Symptôme / Chimie)
         Affiché si step === 'mode'
    ═══════════════════════════════════════════════════════════ --}}
    <div x-show="step === 'mode' && !showCarnet" x-transition.opacity.duration.200ms>
        <div class="py-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">DIAGNOSTIC PISCINE</p>
            <h2 class="font-display font-bold text-ink-950" style="font-size: clamp(1.875rem, 3vw, 2.5rem); line-height: 1.1;">
                Par où veux-tu commencer ?
            </h2>
            <p class="mt-3 text-ink-600 leading-relaxed max-w-[55ch]">
                Choisis le parcours adapté à ta situation.
            </p>

            <div class="mt-8 flex flex-col gap-4">
                <button
                    type="button"
                    data-mode-symptom
                    @click="advance({ value: 'symptom', next: { kind: 'question', id: 'start' } })"
                    class="group flex items-center gap-4 min-h-[60px] h-15 px-5 rounded-2xl bg-white ring-1 ring-sand-200 hover:ring-azure-500 hover:bg-azure-50 active:bg-azure-100 transition-colors text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                >
                    <span class="shrink-0 h-10 w-10 rounded-xl bg-azure-50 ring-1 ring-azure-200 grid place-items-center text-azure-600">
                        <x-icon.sparkle :size="20" />
                    </span>
                    <span>
                        <span class="block font-display font-semibold text-ink-950 text-base">Trouver mon problème</span>
                        <span class="block text-sm text-ink-500">Eau verte, trouble, électrolyseur... questions par symptôme</span>
                    </span>
                    <x-icon.arrow-right :size="16" class="ml-auto text-ink-400 group-hover:text-azure-600 group-hover:translate-x-0.5 transition-all shrink-0" />
                </button>

                <button
                    type="button"
                    data-mode-chemistry
                    @click="advance({ value: 'chemistry', next: { kind: 'wizard', id: 'chemistry' } })"
                    class="group flex items-center gap-4 min-h-[60px] h-15 px-5 rounded-2xl bg-white ring-1 ring-sand-200 hover:ring-azure-500 hover:bg-azure-50 active:bg-azure-100 transition-colors text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                >
                    <span class="shrink-0 h-10 w-10 rounded-xl bg-sand-100 ring-1 ring-sand-200 grid place-items-center text-ink-600">
                        <x-icon.sun :size="20" />
                    </span>
                    <span>
                        <span class="block font-display font-semibold text-ink-950 text-base">Analyser mon eau</span>
                        <span class="block text-sm text-ink-500">pH, chlore, TAC, sel... calcul de doses côté serveur</span>
                    </span>
                    <x-icon.arrow-right :size="16" class="ml-auto text-ink-400 group-hover:text-azure-600 group-hover:translate-x-0.5 transition-all shrink-0" />
                </button>

                {{-- S9 — Carnet local : accès depuis la sélection mode --}}
                <button
                    type="button"
                    data-mode-carnet
                    x-init="loadCarnetEntries()"
                    x-show="carnetEntries.length > 0"
                    @click="loadCarnetEntries(); showCarnet = true;"
                    class="group flex items-center gap-4 min-h-[52px] h-13 px-5 rounded-2xl bg-white ring-1 ring-sand-200 hover:ring-lagon-500 hover:bg-lagon-50/30 active:bg-lagon-100/20 transition-colors text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-lagon-500"
                    aria-label="Mes diagnostics passés, carnet local"
                >
                    <span class="shrink-0 h-10 w-10 rounded-xl grid place-items-center" style="background: oklch(0.720 0.113 207 / 0.12); color: oklch(0.620 0.100 209);">
                        <x-icon.calendar :size="20" />
                    </span>
                    <span>
                        <span class="block font-display font-semibold text-ink-950 text-base">Mes diagnostics passés</span>
                        <span class="block text-sm" style="color: oklch(0.500 0.060 209);" x-text="carnetEntries.length + ' diagnostic' + (carnetEntries.length > 1 ? 's' : '') + ' enregistré' + (carnetEntries.length > 1 ? 's' : '') + ' sur cet appareil'"></span>
                    </span>
                    <x-icon.arrow-right :size="16" class="ml-auto text-ink-400 group-hover:translate-x-0.5 transition-all shrink-0" style="color: oklch(0.620 0.100 209);" />
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S4 — Disclaimer gate (register: product)
         Inline progressive step (jamais une modale — UI-SPEC S4)
         Affiché au nœud 'start' avant que le visiteur commence l'arbre
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="step === 'tree' && nodeId === 'start' && !$wire.disclaimerAccepted"
        x-transition.opacity.duration.200ms
    >
        <div class="py-6">
            <div class="rounded-2xl bg-white ring-1 ring-sand-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="shrink-0 h-9 w-9 rounded-xl ring-1 grid place-items-center" style="background: oklch(0.965 0.045 85); border-color: oklch(0.800 0.130 80 / 0.3);">
                            <x-icon.shield :size="18" style="color: oklch(0.800 0.130 80);" />
                        </span>
                        <div>
                            <h2 class="font-display font-semibold text-ink-950 text-lg">Avant de commencer</h2>
                            <p class="text-sm text-ink-500 mt-0.5">Conseils indicatifs · Dlo Azur Piscines</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm text-ink-700 leading-relaxed">
                        <p>
                            <strong class="text-ink-900">Conseils indicatifs</strong> : ces recommandations ne remplacent pas l'avis d'un pisciniste.
                            En cas de doute, contacte un professionnel.
                        </p>
                        <p>
                            Vérifie toujours la notice de tes produits. Les doses indiquées sont des valeurs orientatives
                            à vérifier selon le titre de tes produits.
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <button
                        type="button"
                        wire:click="acceptDisclaimer"
                        @click="$wire.disclaimerAccepted = true"
                        class="w-full h-13 px-6 rounded-xl bg-azure-500 text-white font-bold hover:bg-azure-600 active:bg-azure-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400"
                        aria-label="J'ai compris, commencer le diagnostic"
                    >
                        J'ai compris, voir les recommandations
                    </button>
                    <p class="mt-3 text-center text-xs text-ink-400">
                        En continuant, tu acceptes que ces conseils sont indicatifs.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S2 — Arbre de décision symptôme (register: product)
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="step === 'tree' && (nodeId !== 'start' || $wire.disclaimerAccepted)"
        x-transition.opacity.duration.200ms
    >
        <template x-if="getNode(nodeId)">
            <div class="py-6">
                <button
                    type="button"
                    x-show="history.length > 0"
                    @click="back()"
                    class="mb-5 inline-flex items-center gap-1.5 h-9 px-3 -ml-1 rounded-xl text-sm text-ink-500 hover:text-ink-900 hover:bg-sand-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                    aria-label="Retour à la question précédente"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Retour
                </button>

                <div class="mb-6">
                    <h2
                        class="font-display font-semibold text-ink-950"
                        style="font-size: clamp(1.875rem, 3vw, 2.5rem); line-height: 1.1;"
                        x-text="getNode(nodeId)?.question ?? ''"
                    ></h2>
                    <p
                        class="mt-2 text-sm text-ink-500"
                        x-show="getNode(nodeId)?.subtitle"
                        x-text="getNode(nodeId)?.subtitle ?? ''"
                    ></p>
                </div>

                {{-- Nœud "tried" (multiselect chips) --}}
                <div x-show="nodeId === 'tried'" class="mb-6">
                    <p class="text-sm font-semibold text-ink-700 mb-3">
                        Qu'as-tu déjà tenté ? <span class="font-normal text-ink-400">(pour ne pas te reproposer ce qui n'a pas marché)</span>
                    </p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <template x-for="action in ['Chlore choc', 'Brossage des parois', 'Anti-algues', 'Ajusté le pH', 'Backwash filtre', 'Rien encore']" :key="action">
                            <button
                                type="button"
                                @click="toggleTried(action)"
                                :class="isTried(action) ? 'bg-azure-500 text-white ring-azure-600' : 'bg-white text-ink-700 ring-sand-200 hover:ring-azure-400'"
                                class="inline-flex items-center gap-1.5 h-9 px-4 rounded-xl ring-1 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                            >
                                <x-icon.check :size="13" x-show="isTried(action)" class="shrink-0" />
                                <span x-text="action"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Options — grande tuile tactile ≥ h-13 (52px) --}}
                <div class="flex flex-col gap-3">
                    <template x-for="(option, idx) in (getNode(nodeId)?.options ?? [])" :key="idx">
                        <button
                            type="button"
                            @click="advance(option)"
                            class="group flex items-center gap-3 min-h-[52px] h-13 px-4 rounded-2xl bg-white ring-1 ring-sand-200 hover:ring-azure-500 hover:bg-azure-50 active:scale-[0.99] transition-all text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                        >
                            <span class="shrink-0 text-xl leading-none w-8 text-center" x-text="option.emoji ?? ''"></span>
                            <span class="font-semibold text-ink-900 text-sm leading-snug" x-text="option.label"></span>
                            <x-icon.arrow-right :size="14" class="ml-auto text-ink-300 group-hover:text-azure-500 group-hover:translate-x-0.5 transition-all shrink-0" />
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S3 — Wizard chimie : saisie des mesures (register: product)
         2 steps : 1 = infos piscine, 2 = mesures
         Alpine drive step nav — wire:click uniquement pour "Calculer"
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="step === 'wizard'"
        x-transition.opacity.duration.200ms
    >
        <div class="py-6">

            {{-- Bouton retour --}}
            <button
                type="button"
                @click="back()"
                class="mb-5 inline-flex items-center gap-1.5 h-9 px-3 -ml-1 rounded-xl text-sm text-ink-500 hover:text-ink-900 hover:bg-sand-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                aria-label="Retour"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                Retour
            </button>

            {{-- Indicateur de step (1/2) --}}
            <div class="flex items-center gap-2 mb-6">
                <span
                    :class="wizardStep === 1 ? 'bg-azure-500 text-white' : 'bg-sand-200 text-ink-500'"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-colors"
                >1</span>
                <span class="h-px w-6 bg-sand-200"></span>
                <span
                    :class="wizardStep === 2 ? 'bg-azure-500 text-white' : 'bg-sand-200 text-ink-500'"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-colors"
                >2</span>
                <span class="ml-2 text-xs text-ink-400" x-text="wizardStep === 1 ? 'Informations piscine' : 'Mesures de l\'eau'"></span>
            </div>

            {{-- ══ Step 1 : Infos piscine ══ --}}
            <div x-show="wizardStep === 1" x-transition.opacity.duration.150ms>

                <h2 class="font-display font-bold text-ink-950 mb-6" style="font-size: clamp(1.875rem, 3vw, 2.5rem); line-height: 1.1;">
                    Ta piscine
                </h2>

                {{-- Disclaimer requis avant le mode chimie --}}
                <div x-show="!$wire.disclaimerAccepted" class="mb-6 rounded-xl p-4 ring-1" style="background: oklch(0.965 0.045 85); outline: 1px solid oklch(0.800 0.130 80 / 0.25);">
                    <p class="text-sm font-semibold text-ink-800 mb-1">Avant de commencer</p>
                    <p class="text-sm text-ink-700 mb-3">
                        Ces recommandations sont indicatives, elles ne remplacent pas l'avis d'un pisciniste.
                        Vérifie toujours la notice de tes produits.
                    </p>
                    <button
                        type="button"
                        wire:click="acceptDisclaimer"
                        @click="$wire.disclaimerAccepted = true"
                        class="w-full h-13 px-6 rounded-xl bg-azure-500 text-white font-bold hover:bg-azure-600 active:bg-azure-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400"
                    >
                        J'ai compris, voir les recommandations
                    </button>
                </div>

                <div class="space-y-5">

                    {{-- Toggle mode saisie volume --}}
                    <div>
                        <label class="block text-sm font-semibold text-ink-700 mb-2">Volume de la piscine</label>
                        <div class="flex rounded-xl ring-1 ring-sand-200 overflow-hidden mb-3">
                            <button
                                type="button"
                                @click="sizeMode = 'volume'; $wire.set('sizeMode', 'volume')"
                                :class="sizeMode === 'volume' ? 'bg-azure-500 text-white' : 'bg-white text-ink-700 hover:bg-sand-50'"
                                class="flex-1 h-10 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                            >
                                Volume en m³
                            </button>
                            <button
                                type="button"
                                @click="sizeMode = 'surface'; $wire.set('sizeMode', 'surface')"
                                :class="sizeMode === 'surface' ? 'bg-azure-500 text-white' : 'bg-white text-ink-700 hover:bg-sand-50'"
                                class="flex-1 h-10 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                            >
                                Surface × profondeur
                            </button>
                        </div>

                        {{-- Volume direct --}}
                        <div x-show="sizeMode === 'volume'">
                            <input
                                wire:model.lazy="volume"
                                type="number"
                                inputmode="decimal"
                                step="0.5"
                                min="1"
                                max="1000"
                                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('volume') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                                placeholder="ex: 25"
                                aria-label="Volume en m³"
                            >
                            @error('volume')
                                <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 25</p>
                            @enderror
                        </div>

                        {{-- Surface + profondeur --}}
                        <div x-show="sizeMode === 'surface'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-ink-500 mb-1">Surface (m²)</label>
                                <input
                                    wire:model.lazy="surface"
                                    type="number"
                                    inputmode="decimal"
                                    step="0.5"
                                    min="1"
                                    max="5000"
                                    @input="updateDerivedVolume()"
                                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('surface') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                                    placeholder="ex: 32"
                                    aria-label="Surface en m²"
                                >
                                @error('surface')
                                    <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 32</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-500 mb-1">Profondeur moyenne (m)</label>
                                <input
                                    wire:model.lazy="profondeur"
                                    type="number"
                                    inputmode="decimal"
                                    step="0.1"
                                    min="0.5"
                                    max="5"
                                    @input="updateDerivedVolume()"
                                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('profondeur') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                                    placeholder="ex: 1,5"
                                    aria-label="Profondeur en mètres"
                                >
                                @error('profondeur')
                                    <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 1,5</p>
                                @enderror
                            </div>
                            {{-- Volume dérivé (affichage) --}}
                            <p
                                x-show="derivedVolume > 0"
                                class="text-sm text-ink-600"
                            >
                                Volume estimé : <strong class="text-ink-900 tabular-nums" x-text="derivedVolume + ' m³'"></strong>
                            </p>
                        </div>
                    </div>

                    {{-- Type de filtre — constrained select (FLOCULANT-BRANCH-SPEC §2) --}}
                    <div>
                        <label for="diag-filtration" class="block text-sm font-semibold text-ink-700 mb-1.5">
                            Type de filtre <span class="text-danger" aria-hidden="true">*</span>
                            <span class="font-normal text-ink-400 text-xs">(obligatoire avant toute recommandation produit)</span>
                        </label>
                        <select
                            id="diag-filtration"
                            wire:model="filtration"
                            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('filtration') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 outline-none transition"
                            aria-required="true"
                        >
                            <option value="">· Sélectionner ·</option>
                            <option value="sable">Sable</option>
                            <option value="verre">Verre (média alternatif)</option>
                            <option value="cartouche">Cartouche</option>
                            <option value="diatomees">Diatomées</option>
                        </select>
                        @error('filtration')
                            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                        @enderror
                        @if ($filtrationHint && ! in_array($filtrationHint, ['sable', 'verre', 'cartouche', 'diatomees']))
                            <p class="mt-1 text-xs text-ink-400">Valeur de ta fiche piscine : « {{ $filtrationHint }} » : choisis la correspondance ci-dessus.</p>
                        @endif
                    </div>

                    {{-- Piscine au sel --}}
                    <div>
                        <label class="block text-sm font-semibold text-ink-700 mb-2">Piscine traitée au sel ?</label>
                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="hasSel = true; $wire.set('sel', true)"
                                :class="hasSel ? 'bg-azure-500 text-white ring-azure-600' : 'bg-white text-ink-700 ring-sand-200 hover:ring-azure-400'"
                                class="flex-1 h-11 rounded-xl ring-1 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                            >
                                Oui
                            </button>
                            <button
                                type="button"
                                @click="hasSel = false; $wire.set('sel', false)"
                                :class="!hasSel ? 'bg-azure-500 text-white ring-azure-600' : 'bg-white text-ink-700 ring-sand-200 hover:ring-azure-400'"
                                class="flex-1 h-11 rounded-xl ring-1 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                            >
                                Non
                            </button>
                        </div>
                    </div>

                    {{-- CTA vers Step 2 --}}
                    <button
                        type="button"
                        @click="goToStep2()"
                        class="w-full h-13 px-6 rounded-xl bg-azure-500 text-white font-bold hover:bg-azure-600 active:bg-azure-700 transition-colors flex items-center justify-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400"
                    >
                        Continuer
                        <x-icon.arrow-right :size="16" />
                    </button>
                </div>
            </div>

            {{-- ══ Step 2 : Mesures ══ --}}
            <div x-show="wizardStep === 2" x-transition.opacity.duration.150ms>

                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display font-bold text-ink-950" style="font-size: clamp(1.875rem, 3vw, 2.5rem); line-height: 1.1;">
                        Tes mesures
                    </h2>
                    <button
                        type="button"
                        @click="backToStep1()"
                        class="inline-flex items-center gap-1.5 h-9 px-3 rounded-xl text-sm text-ink-500 hover:text-ink-900 hover:bg-sand-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                        aria-label="Retour aux informations piscine"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                        Étape 1
                    </button>
                </div>

                <p class="text-sm text-ink-500 mb-6 leading-relaxed">
                    Laisse vide les valeurs que tu n'as pas mesurées, les recommandations s'adaptent.
                </p>

                {{-- Erreurs globales --}}
                @error('throttle')
                    <div class="mb-4 rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                        <p class="text-sm text-danger">{{ $message }}</p>
                    </div>
                @enderror
                @error('compute')
                    <div class="mb-4 rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                        <p class="text-sm text-danger">{{ $message }}</p>
                    </div>
                @enderror
                @error('disclaimer')
                    <div class="mb-4 rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                        <p class="text-sm text-danger">{{ $message }}</p>
                    </div>
                @enderror

                <div class="space-y-5">

                    {{-- pH --}}
                    <div>
                        <label for="diag-ph" class="block text-sm font-semibold text-ink-700 mb-1.5">
                            pH <span class="font-normal text-ink-400 text-xs">(optimal : 7,2 – 7,4)</span>
                        </label>
                        <input
                            id="diag-ph"
                            wire:model.lazy="ph"
                            type="number"
                            inputmode="decimal"
                            step="0.1"
                            min="0"
                            max="14"
                            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('ph') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                            placeholder="ex: 7,4"
                            aria-describedby="ph-hint"
                        >
                        <p id="ph-hint" class="mt-1 text-xs text-ink-400">ex: 7,4</p>
                        @error('ph')
                            <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 7,4</p>
                        @enderror
                    </div>

                    {{-- Chlore libre --}}
                    <div>
                        <label for="diag-chlore" class="block text-sm font-semibold text-ink-700 mb-1.5">
                            Chlore libre (mg/L) <span class="font-normal text-ink-400 text-xs">(optimal : 1 – 3)</span>
                        </label>
                        <input
                            id="diag-chlore"
                            wire:model.lazy="chlore"
                            type="number"
                            inputmode="decimal"
                            step="0.1"
                            min="0"
                            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('chlore') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                            placeholder="ex: 1,2"
                        >
                        <p class="mt-1 text-xs text-ink-400">ex: 1,2</p>
                        @error('chlore')
                            <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 1,2</p>
                        @enderror
                    </div>

                    {{-- TAC (alcalinité) --}}
                    <div>
                        <label for="diag-alcalinite" class="block text-sm font-semibold text-ink-700 mb-1.5">
                            Alcalinité / TAC (mg/L) <span class="font-normal text-ink-400 text-xs">(optimal : 80 – 120)</span>
                        </label>
                        <input
                            id="diag-alcalinite"
                            wire:model.lazy="alcalinite"
                            type="number"
                            inputmode="decimal"
                            step="1"
                            min="0"
                            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('alcalinite') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                            placeholder="ex: 100"
                        >
                        <p class="mt-1 text-xs text-ink-400">TAC = Titre Alcalimétrique Complet. ex: 100</p>
                        @error('alcalinite')
                            <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 100</p>
                        @enderror
                    </div>

                    {{-- Stabilisant --}}
                    <div>
                        <label for="diag-stabilisant" class="block text-sm font-semibold text-ink-700 mb-1.5">
                            Stabilisant / Acide cyanurique (mg/L) <span class="font-normal text-ink-400 text-xs">(optimal : 30 – 50)</span>
                        </label>
                        <input
                            id="diag-stabilisant"
                            wire:model.lazy="stabilisant"
                            type="number"
                            inputmode="decimal"
                            step="1"
                            min="0"
                            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('stabilisant') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                            placeholder="ex: 40"
                        >
                        <p class="mt-1 text-xs text-ink-400">Protège le chlore des UV tropicaux. ex: 40</p>
                        @error('stabilisant')
                            <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 40</p>
                        @enderror
                    </div>

                    {{-- Sel ppm (affiché uniquement si piscine au sel) --}}
                    <div x-show="hasSel">
                        <label for="diag-sel-ppm" class="block text-sm font-semibold text-ink-700 mb-1.5">
                            Taux de sel (ppm) <span class="font-normal text-ink-400 text-xs">(optimal : 3000 – 5000)</span>
                        </label>
                        <input
                            id="diag-sel-ppm"
                            wire:model.lazy="selPpm"
                            type="number"
                            inputmode="decimal"
                            step="10"
                            min="0"
                            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('selPpm') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                            placeholder="ex: 4000"
                        >
                        <p class="mt-1 text-xs text-ink-400">ex: 4000</p>
                        @error('selPpm')
                            <p class="mt-1 text-sm text-danger">La valeur doit être un nombre. Exemple : 4000</p>
                        @enderror
                    </div>

                    {{-- Champs optionnels (audit P1 intake) --}}
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-ink-500 hover:text-ink-800 transition-colors list-none flex items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500 rounded-lg">
                            <svg class="group-open:rotate-90 transition-transform" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                            Mesures complémentaires (facultatif)
                        </summary>
                        <div class="mt-4 space-y-5">
                            {{-- Chlore total --}}
                            <div>
                                <label for="diag-chlore-total" class="block text-sm font-semibold text-ink-700 mb-1.5">
                                    Chlore total (mg/L)
                                </label>
                                <input
                                    id="diag-chlore-total"
                                    wire:model.lazy="chloreTotal"
                                    type="number"
                                    inputmode="decimal"
                                    step="0.1"
                                    min="0"
                                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                                    placeholder="ex: 1,5"
                                >
                                <p class="mt-1 text-xs text-ink-400">Utile pour détecter les chloramines (odeur forte). ex: 1,5</p>
                            </div>
                            {{-- TH --}}
                            <div>
                                <label for="diag-th" class="block text-sm font-semibold text-ink-700 mb-1.5">
                                    Dureté calcique / TH (mg/L)
                                </label>
                                <input
                                    id="diag-th"
                                    wire:model.lazy="th"
                                    type="number"
                                    inputmode="decimal"
                                    step="1"
                                    min="0"
                                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition tabular-nums"
                                    placeholder="ex: 200"
                                >
                                <p class="mt-1 text-xs text-ink-400">Dureté de l'eau : adapte le type de chlore recommandé. ex: 200</p>
                            </div>
                        </div>
                    </details>

                    {{-- CTA — « Calculer mon plan d'action » (seul wire:click du wizard) --}}
                    <button
                        type="button"
                        wire:click="computeAndPersist"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="w-full h-13 px-6 rounded-xl bg-azure-500 text-white font-bold hover:bg-azure-600 active:bg-azure-700 transition-colors flex items-center justify-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400"
                    >
                        <span wire:loading.remove wire:target="computeAndPersist">Calculer mon plan d'action</span>
                        <span wire:loading wire:target="computeAndPersist">Calcul de ton plan…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S5 + S6 + S7 — Résultats chimie (dosage serveur)
         Affiché après computeAndPersist réussi (savedDiagnosticId non null)
    ═══════════════════════════════════════════════════════════ --}}
    @if ($savedDiagnosticId)
    <div
        x-show="step === 'wizard'"
        x-transition.opacity.duration.300ms
        x-init="
            // Sauvegarde automatique dans le carnet au premier affichage (DIAG-07)
            // DIAG-02 : on stocke le texte du résultat, jamais les formules
            saveToCarnet(
                @js($savedDiagnosticId),
                @js($mode === 'chemistry' ? 'Analyse chimique' : 'Diagnostic symptôme'),
                @js(empty($recommandations) ? 'Eau équilibrée, aucune correction' : count($recommandations) . ' correction(s) identifiée(s)'),
                @js($confidenceIndex ?? 'indicatif'),
                @js(collect($recommandations)->map(fn($r) => ($r['param'] ?? '') . ($r['current'] ? ' : ' . $r['current'] : ''))->filter()->join(', ')),
                @js(empty($recommandations) ? 'Ton eau est équilibrée.' : count($recommandations) . ' correction(s) : ' . collect($recommandations)->pluck('param')->filter()->join(', '))
            )
        "
    >
        <div class="py-6">

            {{-- ① Titre + confidence chip (S5, register: product) --}}
            <div class="mb-6">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">RÉSULTATS DE L'ANALYSE</p>
                <h2 class="font-display font-bold text-ink-950 mb-3" style="font-size: clamp(1.875rem, 3vw, 2.5rem); line-height: 1.1;">
                    @if (empty($recommandations))
                        Ton eau est équilibrée
                    @else
                        {{ count($recommandations) }} correction{{ count($recommandations) > 1 ? 's' : '' }} identifiée{{ count($recommandations) > 1 ? 's' : '' }}
                    @endif
                </h2>

                {{-- Confidence chip (S5 — register: product — CDC §5.5) --}}
                @if (empty($recommandations))
                    <div class="inline-flex items-center gap-1.5 h-7 px-3 rounded-full ring-1 text-xs font-bold" style="background: oklch(0.700 0.150 155 / 0.12); border-color: oklch(0.700 0.150 155 / 0.25); color: oklch(0.700 0.150 155);">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: oklch(0.700 0.150 155);"></span>
                        Eau saine
                    </div>
                @else
                    {{-- Chip confiance (CDC §5.5 : success pour élevé, warn pour moyen/indicatif) --}}
                    @if ($confidenceIndex === 'eleve')
                        <div
                            class="inline-flex items-center gap-1.5 h-7 px-3 rounded-full ring-1 text-xs font-bold mb-1"
                            style="background: oklch(0.700 0.150 155 / 0.12); border-color: oklch(0.700 0.150 155 / 0.30); color: oklch(0.700 0.150 155);"
                            role="status"
                            aria-label="Confiance élevée"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" style="background: oklch(0.700 0.150 155);"></span>
                            Confiance élevée
                        </div>
                        <p class="text-xs text-ink-500 mt-1">basé sur tes mesures.</p>
                    @elseif ($confidenceIndex === 'moyen')
                        <div
                            class="inline-flex items-center gap-1.5 h-7 px-3 rounded-full ring-1 text-xs font-bold mb-1"
                            style="background: oklch(0.965 0.045 85); border-color: oklch(0.800 0.130 80 / 0.30); color: oklch(0.800 0.130 80);"
                            role="status"
                            aria-label="Confiance moyenne"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" style="background: oklch(0.800 0.130 80);"></span>
                            Confiance moyenne
                        </div>
                        <p class="text-xs text-ink-500 mt-1">affine en mesurant le stabilisant / le TAC.</p>
                    @else
                        <div
                            class="inline-flex items-center gap-1.5 h-7 px-3 rounded-full ring-1 text-xs font-bold mb-1"
                            style="background: oklch(0.965 0.045 85); border-color: oklch(0.800 0.130 80 / 0.30); color: oklch(0.800 0.130 80);"
                            role="status"
                            aria-label="Indicatif"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" style="background: oklch(0.800 0.130 80);"></span>
                            Indicatif
                        </div>
                        <p class="text-xs text-ink-500 mt-1">diagnostic visuel sans mesure, pour confirmer, mesure ton eau ou demande à Pierre.</p>
                    @endif
                @endif
            </div>

            {{-- Pas de correction --}}
            @if (empty($recommandations))
                <p class="text-ink-700 leading-relaxed mb-8">
                    Aucune correction nécessaire d'après tes mesures. Continue ta filtration habituelle et re-teste dans quelques jours.
                </p>
            @else
                {{-- ③ Safety block ambre --}}
                <div class="mb-6 rounded-xl p-4 flex items-start gap-3" style="background: oklch(0.965 0.045 85); outline: 1px solid oklch(0.800 0.130 80 / 0.25);" role="status" aria-label="Bloc sécurité">
                    <x-icon.shield :size="18" class="shrink-0 mt-0.5" style="color: oklch(0.800 0.130 80);" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide mb-1.5" style="color: oklch(0.800 0.130 80);">Sécurité, avant de manipuler</p>
                        <p class="text-sm leading-relaxed text-ink-800">
                            Porte des gants et des lunettes. Ne mélange jamais deux produits chimiques. Verse toujours le produit dans l'eau, jamais l'inverse. Respecte le délai avant baignade indiqué sur chaque produit.
                        </p>
                    </div>
                </div>

                {{-- ④ Cards dosage (liste ordonnée différenciée) --}}
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">PLAN D'ACTION</p>
                    <div class="rounded-3xl bg-white ring-1 ring-sand-200 overflow-hidden">
                        @foreach ($recommandations as $idx => $card)
                        <div class="{{ $idx > 0 ? 'border-t border-sand-100' : '' }} p-5">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-full bg-azure-50 text-azure-600 text-xs font-bold tabular-nums mt-0.5">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-display font-semibold text-ink-950 text-base">{{ $card['param'] ?? '' }}</p>
                                    @if (!empty($card['current']))
                                        <p class="text-sm text-ink-500 mt-0.5">Actuel : <span class="tabular-nums">{{ $card['current'] }}</span></p>
                                    @endif
                                    @if (!empty($card['target']))
                                        <p class="text-sm text-ink-500">Cible : <span class="tabular-nums">{{ $card['target'] }}</span></p>
                                    @endif
                                </div>
                            </div>
                            @if (!empty($card['product']))
                                <div class="ml-10">
                                    <p class="text-xs font-bold uppercase tracking-wide text-ink-500 mb-1">PRODUIT</p>
                                    <p class="text-sm font-semibold text-ink-900">{{ $card['product'] }}</p>
                                </div>
                            @endif
                            @if (!empty($card['dose']))
                                <div class="ml-10 mt-2">
                                    <p class="text-xs font-bold uppercase tracking-wide text-ink-500 mb-1">DOSE</p>
                                    <p class="text-sm font-bold text-azure-600 tabular-nums">{{ $card['dose'] }}</p>
                                </div>
                            @endif
                            @if (!empty($card['note']))
                                <div class="ml-10 mt-3 text-xs text-ink-500 leading-relaxed border-t border-sand-100 pt-3">
                                    {{ $card['note'] }}
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <p class="text-sm font-semibold text-ink-600 mb-4 flex items-center gap-2">
                    <x-icon.arrow-right :size="14" class="text-azure-500 shrink-0" />
                    Re-teste avant la dose suivante.
                </p>

                {{-- Boucle de re-test légère (DIAG-06 réactif, Plan 05-06)
                     In-session, 0 push, 0 scheduler — V0
                     Oui  : note positive (lagon) + marque carnet
                     Non  : déclenche escalade réactive (Plan 04 hook) + marque carnet --}}
                <div
                    class="mb-8"
                    x-show="!retestAnswered"
                    x-transition.opacity.duration.200ms
                >
                    <div class="rounded-2xl p-4 ring-1 ring-sand-200 bg-white">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] mb-2" style="color: oklch(0.620 0.100 209);">RE-TEST</p>

                        {{-- Bouton "J'ai appliqué le plan" (déclenche le prompt) --}}
                        <div x-show="!retestShown">
                            <p class="text-sm text-ink-700 mb-3">
                                Tu as appliqué le plan ? Re-teste ton eau et reviens ici pour voir si ça a marché.
                            </p>
                            <button
                                type="button"
                                @click="showRetestPrompt()"
                                class="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-sm font-semibold ring-1 ring-sand-200 bg-white hover:bg-sand-50 text-ink-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                            >
                                <x-icon.check :size="14" class="text-ink-400 shrink-0" />
                                J'ai appliqué le plan, re-tester
                            </button>
                        </div>

                        {{-- Prompt "As-tu re-teste ? Ca a marche ?" --}}
                        <div x-show="retestShown" x-transition.opacity.duration.150ms>
                            <p class="text-sm font-semibold text-ink-900 mb-3">
                                As-tu re-teste ? Ca a marche ?
                            </p>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="onRetestOui()"
                                    class="flex-1 h-11 rounded-xl text-sm font-semibold ring-1 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1"
                                    style="background: oklch(0.700 0.150 155 / 0.12); border-color: oklch(0.700 0.150 155 / 0.30); color: oklch(0.700 0.150 155);"
                                    aria-label="Oui, ca a marche"
                                >
                                    Oui, ca a marche
                                </button>
                                <button
                                    type="button"
                                    @click="onRetestNon()"
                                    class="flex-1 h-11 rounded-xl text-sm font-semibold ring-1 ring-sand-200 bg-white hover:bg-sand-50 text-ink-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                                    aria-label="Non, pas encore regle"
                                >
                                    Non, pas encore regle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Message positif apres re-test reussi (lagon accent) --}}
                <div
                    class="mb-8 rounded-2xl p-4 ring-1"
                    x-show="retestAnswered && !$wire.retestFailed"
                    x-transition.opacity.duration.200ms
                    style="background: oklch(0.700 0.150 155 / 0.08); border-color: oklch(0.700 0.150 155 / 0.25);"
                    role="status"
                >
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 h-9 w-9 rounded-xl grid place-items-center" style="background: oklch(0.700 0.150 155 / 0.15);">
                            <x-icon.check :size="18" style="color: oklch(0.700 0.150 155);" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold" style="color: oklch(0.700 0.150 155);">Super, ca a marche !</p>
                            <p class="text-xs text-ink-500 mt-0.5">Le diagnostic est sauvegarde dans ton carnet local pour reference future.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ⑤ PDF téléchargeable (guarded — Plan 05-05 crée la route) --}}
            @if(Route::has('diagnostic.pdf'))
            <div class="mb-4">
                <a
                    href="{{ route('diagnostic.pdf', $savedDiagnosticId) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-azure-600 hover:text-azure-800 underline underline-offset-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500 rounded"
                >
                    <x-icon.arrow-right :size="14" class="shrink-0" />
                    Télécharger le rapport (PDF)
                </a>
            </div>
            @endif

            {{-- ⑥ S6 — Pic d'escalade WhatsApp (register: brand, UI-SPEC S6) --}}
            @if ($escaladeNiveau === 'preemptif')
                {{-- Hard-stop callout pour acide chlorhydrique / 230V (full ring, pas de side-stripe — UI-SPEC S6) --}}
                @if (in_array($escaladeRaison, ['acide-chlorhydrique', '230V']))
                <div
                    class="mb-4 rounded-2xl p-4 ring-2 flex items-start gap-3"
                    style="background: oklch(0.620 0.210 25 / 0.07); border-color: oklch(0.620 0.210 25 / 0.40);"
                    role="alert"
                    aria-live="assertive"
                >
                    <x-icon.shield :size="18" class="shrink-0 mt-0.5" style="color: oklch(0.620 0.210 25);" />
                    <p class="text-sm leading-relaxed" style="color: oklch(0.620 0.210 25);">
                        <strong>Cette étape est risquée pour un particulier.</strong>
                        On te recommande de faire appel à Pierre plutôt que de la tenter seul.
                    </p>
                </div>
                @endif
            @endif

            {{-- Carte d'escalade (BRAND register — marine, one-gesture CTA WhatsApp vert) --}}
            <div
                class="rounded-2xl p-5 mb-8 escalade-card"
                style="background: oklch(0.232 0.052 251);"
            >
                <p class="text-xs font-bold uppercase tracking-[0.18em] mb-2" style="color: oklch(0.720 0.113 207);">DEMANDER UNE INTERVENTION</p>
                @if ($escaladeNiveau === 'preemptif')
                    <h3 class="font-display font-semibold text-lg mb-1" style="color: oklch(0.987 0.005 85);">
                        Ce cas dépasse le DIY, Pierre est là
                    </h3>
                    <p class="text-sm mb-4 leading-relaxed" style="color: oklch(0.967 0.008 84 / 0.70);">
                        Envoie ton diagnostic à Pierre sur WhatsApp, il arrive avec le contexte complet :
                        symptôme, mesures, filtre, ce que tu as déjà tenté, et le niveau de confiance.
                    </p>
                @else
                    <h3 class="font-display font-semibold text-lg mb-1" style="color: oklch(0.987 0.005 85);">
                        Pierre peut intervenir rapidement
                    </h3>
                    <p class="text-sm mb-4 leading-relaxed" style="color: oklch(0.967 0.008 84 / 0.70);">
                        Un plan ne suffit pas ou tu préfères être accompagné ?
                        Pierre (Dlo Azur Piscines) intervient en Martinique, envoie-lui ton diagnostic directement sur WhatsApp.
                    </p>
                @endif

                {{-- Contexte récap (ce que Pierre reçoit) --}}
                <div class="mb-4 text-xs leading-relaxed space-y-0.5" style="color: oklch(0.967 0.008 84 / 0.55);">
                    <p>Pierre recevra : symptôme · mesures (+ fiabilité) · filtre + volume · actions tentées · diagnostic · confiance{{ $commune ? ' · ' . $commune : '' }}</p>
                </div>

                {{-- CTA WhatsApp — one gesture, pre-filled rich context (DIAG-06) --}}
                {{-- Alpine encodeURIComponent encode le payload riche côté client (RESEARCH Pattern 5) --}}
                <a
                    href="https://wa.me/596696940054?text={{ urlencode($whatsappSummary) }}"
                    x-on:click.prevent="window.open('https://wa.me/596696940054?text=' + encodeURIComponent(@js($whatsappSummary)), '_blank', 'noopener,noreferrer')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 min-h-[44px] h-13 px-5 rounded-xl font-bold text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                    style="background-color: #25D366; color: oklch(0.987 0.005 85);"
                    aria-label="Demander une intervention à Pierre sur WhatsApp"
                >
                    <x-icon.whatsapp :size="18" />
                    Demander une intervention à Pierre
                </a>
            </div>

            {{-- Style bloom unique sur la carte escalade (non-looping, one-time reveal — UI-SPEC S6) --}}
            @once
            @push('head')
            <style>
            @keyframes escalade-bloom {
                0%   { box-shadow: 0 0 0 0 oklch(0.232 0.052 251 / 0.0); }
                40%  { box-shadow: 0 0 0 12px oklch(0.232 0.052 251 / 0.18); }
                100% { box-shadow: 0 0 0 20px oklch(0.232 0.052 251 / 0.00); }
            }
            .escalade-card {
                animation: escalade-bloom 900ms ease-out 150ms 1 forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .escalade-card { animation: none; }
            }
            </style>
            @endpush
            @endonce

            {{-- ⑥ S7 — Lead capture (register: product) —————————————————————————— --}}
            @if ($leadSent)
                {{-- Succès lead --}}
                <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-8 text-center">
                    <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-4" style="background: oklch(0.700 0.150 155 / 0.15);">
                        <x-icon.check :size="22" style="color: oklch(0.700 0.150 155);" />
                    </div>
                    <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">C'est noté, merci !</h3>
                    <p class="text-ink-600 mb-4">Pierre a reçu ton diagnostic et te recontacte vite. Tu peux aussi lui écrire tout de suite sur WhatsApp.</p>
                    <a
                        href="https://wa.me/596696940054"
                        rel="noopener noreferrer"
                        target="_blank"
                        class="inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-[#25D366] text-white font-semibold hover:brightness-95 transition-colors"
                    >
                        <x-icon.whatsapp :size="16" />
                        Écrire à Pierre sur WhatsApp
                    </a>
                </div>
            @else
                <div class="rounded-2xl bg-white ring-1 ring-sand-200 p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-1">COORDONNÉES</p>
                    <h3 class="font-display font-semibold text-xl text-ink-950 mb-1">Vos coordonnées</h3>
                    <p class="text-sm text-ink-500 mb-6">Pierre te recontacte si tu as des questions sur le diagnostic. Laisse tes coordonnées et il te répond rapidement.</p>

                    {{-- Honeypot (visually-hidden, aria-hidden) — T-05-07 --}}
                    <div aria-hidden="true" tabindex="-1" style="display:none">
                        <x-honeypot livewire-model="extraFields" />
                    </div>

                    {{-- Erreurs globales lead --}}
                    @error('throttle')
                        <div class="mb-4 rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                            <p class="text-sm text-danger">{{ $message }}</p>
                        </div>
                    @enderror
                    @error('send')
                        <div class="mb-4 rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                            <p class="text-sm text-danger">{{ $message }}</p>
                        </div>
                    @enderror

                    <div class="space-y-4">
                        {{-- Prénom --}}
                        <div>
                            <label for="lead-prenom" class="block text-sm font-semibold text-ink-700 mb-1.5">
                                Prénom <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="lead-prenom"
                                type="text"
                                wire:model.lazy="prenom"
                                autocomplete="given-name"
                                required
                                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('prenom') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                                placeholder="Ton prénom"
                            >
                            @error('prenom')
                                <p class="mt-1 text-sm text-danger">Indique ton prénom</p>
                            @enderror
                        </div>

                        {{-- Commune --}}
                        <div>
                            <label for="lead-commune" class="block text-sm font-semibold text-ink-700 mb-1.5">
                                Commune <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="lead-commune"
                                type="text"
                                wire:model.lazy="commune"
                                required
                                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('commune') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                                placeholder="Ta commune en Martinique"
                            >
                            @error('commune')
                                <p class="mt-1 text-sm text-danger">Indique ta commune</p>
                            @enderror
                        </div>

                        {{-- Email (facultatif) --}}
                        <div>
                            <label for="lead-email" class="block text-sm font-semibold text-ink-700 mb-1.5">
                                E-mail <span class="text-ink-400 font-normal text-xs">(facultatif)</span>
                            </label>
                            <input
                                id="lead-email"
                                type="email"
                                wire:model.lazy="email"
                                autocomplete="email"
                                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('email') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                                placeholder="toi@exemple.com"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-danger">L'e-mail doit contenir un @. Exemple : toi@exemple.com</p>
                            @enderror
                        </div>

                        {{-- Site web (facultatif) --}}
                        <div>
                            <label for="lead-site-web" class="block text-sm font-semibold text-ink-700 mb-1.5">
                                Site web <span class="text-ink-400 font-normal text-xs">(facultatif)</span>
                            </label>
                            <input
                                id="lead-site-web"
                                type="url"
                                wire:model.lazy="siteWeb"
                                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 @error('siteWeb') ring-danger @else ring-sand-200 @enderror focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                                placeholder="https://monsite.com"
                            >
                            @error('siteWeb')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <button
                            type="button"
                            wire:click="submitLead"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed"
                            class="w-full h-13 px-6 rounded-xl bg-azure-500 text-white font-bold hover:bg-azure-600 active:bg-azure-700 transition-colors flex items-center justify-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400"
                        >
                            <span wire:loading.remove wire:target="submitLead">Envoyer mes coordonnées</span>
                            <span wire:loading wire:target="submitLead">Envoi en cours…</span>
                        </button>

                        <p class="text-center text-xs text-ink-400">
                            Ou contacte Pierre directement sur
                            <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="font-semibold text-ink-600 hover:text-ink-900 underline">WhatsApp</a>.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Recommencer --}}
            <div class="mt-6 text-center">
                <button
                    type="button"
                    @click="step = 'mode'; wizardStep = 1; history = []; nodeId = 'start'; resultId = null; triedActions = [];"
                    class="text-sm text-ink-400 hover:text-ink-700 transition-colors"
                >
                    Recommencer le diagnostic
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         S5 minimal — Feuille résultat arbre symptôme (register: product)
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="step === 'result'"
        x-transition.opacity.duration.300ms
    >
        <template x-if="getResult(resultId)">
            <div class="py-6">
                <button
                    type="button"
                    @click="back()"
                    class="mb-5 inline-flex items-center gap-1.5 h-9 px-3 -ml-1 rounded-xl text-sm text-ink-500 hover:text-ink-900 hover:bg-sand-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                    aria-label="Retour à la question précédente"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Revenir
                </button>

                {{-- ① Diagnostic + confidence --}}
                <div class="mb-6">
                    <div class="flex items-start gap-3 flex-wrap">
                        <h2
                            class="font-display font-bold text-ink-950 leading-[1.1]"
                            style="font-size: clamp(1.875rem, 3vw, 2.5rem);"
                            x-text="getResult(resultId)?.diagnostic ?? ''"
                        ></h2>
                        <span
                            x-show="getResult(resultId)?.confidence"
                            :class="{
                                'bg-success/10 ring-success/20': getResult(resultId)?.confidence === 'eleve',
                                'bg-warn-bg ring-warn/20': getResult(resultId)?.confidence === 'moyen'
                            }"
                            class="inline-flex items-center gap-1 mt-1 h-7 px-2.5 rounded-full ring-1 text-xs font-bold uppercase tracking-wide shrink-0"
                            :style="getResult(resultId)?.confidence === 'eleve' ? 'color: oklch(0.700 0.150 155)' : 'color: oklch(0.800 0.130 80)'"
                        >
                            <span
                                :style="getResult(resultId)?.confidence === 'eleve' ? 'background: oklch(0.700 0.150 155)' : 'background: oklch(0.800 0.130 80)'"
                                class="inline-block h-1.5 w-1.5 rounded-full"
                            ></span>
                            <span x-text="getResult(resultId)?.confidence === 'eleve' ? 'Confiance élevée' : 'Confiance moyenne'"></span>
                        </span>
                    </div>
                </div>

                {{-- ② Analyse --}}
                <p
                    x-show="getResult(resultId)?.analyse"
                    x-text="getResult(resultId)?.analyse ?? ''"
                    class="text-ink-700 leading-relaxed mb-6 max-w-[65ch]"
                ></p>

                {{-- ③ Safety block ambre --}}
                <div
                    x-show="getResult(resultId)?.safety_block"
                    class="mb-6 rounded-xl p-4 flex items-start gap-3"
                    style="background: oklch(0.965 0.045 85); outline: 1px solid oklch(0.800 0.130 80 / 0.25);"
                    role="status"
                    aria-label="Bloc sécurité"
                >
                    <x-icon.shield :size="18" class="shrink-0 mt-0.5" style="color: oklch(0.800 0.130 80);" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide mb-1.5" style="color: oklch(0.800 0.130 80);">Sécurité, avant de manipuler</p>
                        <p
                            class="text-sm leading-relaxed text-ink-800"
                            x-text="getResult(resultId)?.safety_block ?? ''"
                        ></p>
                    </div>
                </div>

                {{-- ④ Plan d'action --}}
                <div x-show="(getResult(resultId)?.plan ?? []).length > 0" class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">PLAN D'ACTION</p>
                    <div class="rounded-3xl bg-white ring-1 ring-sand-200 overflow-hidden">
                        <template x-for="(step_item, idx) in (getResult(resultId)?.plan ?? [])" :key="idx">
                            <div
                                :class="idx > 0 ? 'border-t border-sand-100' : ''"
                                class="flex items-start gap-3 px-5 py-3.5"
                            >
                                <span
                                    class="shrink-0 inline-flex h-6 w-6 items-center justify-center rounded-full bg-azure-50 text-azure-600 text-xs font-bold tabular-nums mt-0.5"
                                    x-text="idx + 1"
                                ></span>
                                <span class="text-sm text-ink-800 leading-relaxed" x-text="step_item"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <p
                    x-show="getResult(resultId)?.retest_reminder"
                    class="text-sm font-semibold text-ink-600 mb-8 flex items-center gap-2"
                >
                    <x-icon.arrow-right :size="14" class="text-azure-500 shrink-0" />
                    <span x-text="getResult(resultId)?.retest_reminder ?? ''"></span>
                </p>

                {{-- S6 — Pic d'escalade WhatsApp arbre symptôme (register: brand — UI-SPEC S6) --}}
                {{-- Hard-stop callout si la feuille porte acide/230V --}}
                <template x-if="getResult(resultId)?.escalade?.niveau === 'preemptif' && ['acide-chlorhydrique','230V'].includes(getResult(resultId)?.escalade?.raison)">
                    <div
                        class="mb-4 rounded-2xl p-4 ring-2 flex items-start gap-3"
                        style="background: oklch(0.620 0.210 25 / 0.07); border-color: oklch(0.620 0.210 25 / 0.40);"
                        role="alert"
                        aria-live="assertive"
                    >
                        <x-icon.shield :size="18" class="shrink-0 mt-0.5" style="color: oklch(0.620 0.210 25);" />
                        <p class="text-sm leading-relaxed" style="color: oklch(0.620 0.210 25);">
                            <strong>Cette étape est risquée pour un particulier.</strong>
                            On te recommande de faire appel à Pierre plutôt que de la tenter seul.
                        </p>
                    </div>
                </template>

                <div
                    class="rounded-2xl p-5 escalade-card-tree"
                    style="background: oklch(0.232 0.052 251);"
                >
                    <p class="text-xs font-bold uppercase tracking-[0.18em] mb-2" style="color: oklch(0.720 0.113 207);">DEMANDER UNE INTERVENTION</p>
                    <h3
                        class="font-display font-semibold text-lg mb-1"
                        style="color: oklch(0.987 0.005 85);"
                        x-text="getResult(resultId)?.escalade?.niveau === 'preemptif' ? 'Ce cas dépasse le DIY, Pierre est là' : 'Pierre peut intervenir rapidement'"
                    ></h3>
                    <p class="text-sm mb-4 leading-relaxed" style="color: oklch(0.967 0.008 84 / 0.70);">
                        Envoie ton diagnostic à Pierre sur WhatsApp, il arrive avec le contexte complet :
                        symptôme, mesures, filtre, ce que tu as déjà tenté.
                    </p>

                    {{-- CTA WhatsApp riche — Alpine encodeURIComponent (RESEARCH Pattern 5 / DIAG-06) --}}
                    <a
                        :href="'https://wa.me/596696940054?text=' + encodeURIComponent(
                            'Bonjour Pierre, j\'ai utilisé l\'outil diagnostic de Dlo Azur Piscines.\n\n'
                            + 'Parcours : Diagnostic par symptôme\n'
                            + 'Diagnostic : ' + (getResult(resultId)?.diagnostic ?? '') + '\n'
                            + 'Analyse : ' + (getResult(resultId)?.analyse ?? '') + '\n'
                            + (triedActions.length > 0 ? '\nDéjà tenté (sans succès) : ' + triedActions.join(', ') + '\n' : '')
                            + '\nPeux-tu m\'aider ou intervenir ?'
                        )"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 min-h-[44px] h-13 px-5 rounded-xl font-bold text-sm hover:brightness-95 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                        style="background-color: #25D366; color: oklch(0.987 0.005 85);"
                        aria-label="Demander une intervention à Pierre sur WhatsApp"
                    >
                        <x-icon.whatsapp :size="18" />
                        Demander une intervention à Pierre
                    </a>
                </div>

                @once
                @push('head')
                <style>
                .escalade-card-tree {
                    animation: escalade-bloom 900ms ease-out 150ms 1 forwards;
                }
                @media (prefers-reduced-motion: reduce) {
                    .escalade-card-tree { animation: none; }
                }
                </style>
                @endpush
                @endonce

                <div class="mt-6 text-center">
                    <button
                        type="button"
                        @click="step = 'mode'; nodeId = 'start'; history = []; resultId = null; triedActions = [];"
                        class="text-sm text-ink-400 hover:text-ink-700 transition-colors"
                    >
                        Recommencer le diagnostic
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S9 — Carnet local-only (register: product — DIAG-07)
         Liste anti-chronologique des diagnostics enregistrés sur cet appareil.
         0 réseau, 0 synchro, 0 compte. Lisible hors ligne.
         DIAG-02 : affiche le texte du résultat uniquement, jamais de formules.
         XSS T-05-20 : toutes les valeurs via x-text (jamais innerHTML).
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="showCarnet"
        x-transition.opacity.duration.200ms
        x-init="if (showCarnet) loadCarnetEntries()"
    >
        <div class="py-6">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-6">
                <button
                    type="button"
                    @click="showCarnet = false; step = 'mode';"
                    class="inline-flex items-center gap-1.5 h-9 px-3 -ml-1 rounded-xl text-sm text-ink-500 hover:text-ink-900 hover:bg-sand-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                    aria-label="Retour"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Retour
                </button>
            </div>

            <p class="text-xs font-bold uppercase tracking-[0.18em] mb-2" style="color: oklch(0.620 0.100 209);">CARNET LOCAL</p>
            <h2 class="font-display font-bold text-ink-950 mb-1" style="font-size: clamp(1.875rem, 3vw, 2.5rem); line-height: 1.1;">
                Mes diagnostics passes
            </h2>
            <p class="text-sm text-ink-500 mb-6">
                Enregistres sur cet appareil uniquement. Rien n'est envoye.
            </p>

            {{-- Liste vide --}}
            <template x-if="carnetEntries.length === 0">
                <div class="rounded-2xl bg-white ring-1 ring-sand-200 p-8 text-center">
                    <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-4" style="background: oklch(0.720 0.113 207 / 0.12);">
                        <x-icon.calendar :size="22" style="color: oklch(0.620 0.100 209);" />
                    </div>
                    <h3 class="font-display font-semibold text-lg text-ink-950 mb-2">Aucun diagnostic pour l'instant</h3>
                    <p class="text-sm text-ink-500 mb-6 max-w-[50ch] mx-auto">
                        Tes diagnostics resteront ici, sur cet appareil, rien n'est envoye.
                        Lance ton premier diagnostic pour garder une trace de tes mesures et reprendre le suivi plus tard.
                    </p>
                    <button
                        type="button"
                        @click="showCarnet = false; step = 'mode';"
                        class="inline-flex items-center gap-2 h-11 px-6 rounded-xl bg-azure-500 text-white font-bold text-sm hover:bg-azure-600 active:bg-azure-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400"
                    >
                        Lancer un diagnostic
                    </button>
                </div>
            </template>

            {{-- Liste anti-chronologique --}}
            <template x-if="carnetEntries.length > 0">
                <div>
                    <div class="space-y-3 mb-6">
                        <template x-for="entry in carnetEntries" :key="entry.id">
                            <div class="rounded-2xl bg-white ring-1 ring-sand-200 overflow-hidden">
                                <div class="p-5">

                                    {{-- En-tete carte : date + confidence chip --}}
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-2 text-xs text-ink-400">
                                            <x-icon.calendar :size="13" class="shrink-0" />
                                            <span x-text="formatCarnetDate(entry.date)"></span>
                                        </div>
                                        {{-- Chip confiance (success pour eleve, warn pour moyen/indicatif) --}}
                                        <span
                                            class="inline-flex items-center gap-1 h-6 px-2 rounded-full ring-1 text-xs font-bold uppercase tracking-wide shrink-0"
                                            :style="entry.confidence === 'eleve'
                                                ? 'background: oklch(0.700 0.150 155 / 0.12); border-color: oklch(0.700 0.150 155 / 0.30); color: oklch(0.700 0.150 155);'
                                                : 'background: oklch(0.965 0.045 85); border-color: oklch(0.800 0.130 80 / 0.30); color: oklch(0.800 0.130 80);'"
                                        >
                                            <span
                                                class="inline-block h-1.5 w-1.5 rounded-full"
                                                :style="entry.confidence === 'eleve' ? 'background: oklch(0.700 0.150 155)' : 'background: oklch(0.800 0.130 80)'"
                                            ></span>
                                            <span x-text="confidenceLabel(entry.confidence)"></span>
                                        </span>
                                    </div>

                                    {{-- Symptome / mode --}}
                                    <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-1" x-text="entry.symptome"></p>

                                    {{-- Diagnostic (texte du résultat) --}}
                                    <p class="text-sm font-semibold text-ink-900 mb-2" x-text="entry.diagnostic"></p>

                                    {{-- Mesures clés --}}
                                    <p
                                        x-show="entry.mesuresCles"
                                        class="text-xs text-ink-400 mb-3"
                                        x-text="entry.mesuresCles"
                                    ></p>

                                    {{-- Actions : Reprendre + Voir PDF --}}
                                    <div class="flex items-center gap-3 pt-3 border-t border-sand-100">
                                        <button
                                            type="button"
                                            @click="resumeFromCarnet(entry)"
                                            class="inline-flex items-center gap-1.5 h-9 px-3 rounded-xl text-sm font-semibold ring-1 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-lagon-500"
                                            style="border-color: oklch(0.720 0.113 207 / 0.35); color: oklch(0.620 0.100 209); background: oklch(0.720 0.113 207 / 0.08);"
                                        >
                                            <x-icon.arrow-right :size="13" class="shrink-0" />
                                            Reprendre ce diagnostic
                                        </button>

                                        {{-- Voir le PDF — uniquement si le diagnostic a un ID serveur --}}
                                        @if(Route::has('diagnostic.pdf'))
                                        <a
                                            x-show="entry.serverId"
                                            :href="'{{ route('diagnostic.pdf', ['diagnostic' => '__ID__']) }}'.replace('__ID__', entry.serverId)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 h-9 px-3 rounded-xl text-sm font-semibold text-ink-500 hover:text-ink-800 ring-1 ring-sand-200 hover:ring-sand-300 bg-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                                        >
                                            Voir le PDF
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Action destructive : effacer l'historique (inline confirm Alpine — jamais une modale reflexive) --}}
                    <div class="border-t border-sand-100 pt-6">
                        <div x-show="!showClearConfirm">
                            <button
                                type="button"
                                @click="showClearConfirm = true"
                                class="text-sm text-ink-400 hover:text-danger transition-colors underline underline-offset-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger rounded"
                            >
                                Effacer l'historique
                            </button>
                        </div>

                        {{-- Confirmation inline (Destructive-action — UI-SPEC S9) --}}
                        <div
                            x-show="showClearConfirm"
                            x-transition.opacity.duration.150ms
                            class="rounded-2xl p-4 ring-1 bg-white"
                            style="border-color: oklch(0.620 0.210 25 / 0.30);"
                        >
                            <p class="text-sm font-semibold text-ink-900 mb-1">Effacer tout l'historique de cet appareil ?</p>
                            <p class="text-xs text-ink-500 mb-4">
                                Tes diagnostics sont stockes uniquement ici. Cette action est definitive et ne peut pas etre annulee.
                            </p>
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="clearCarnet()"
                                    class="flex-1 h-10 rounded-xl text-sm font-bold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1"
                                    style="background: oklch(0.620 0.210 25 / 0.12); border: 1px solid oklch(0.620 0.210 25 / 0.30); color: oklch(0.620 0.210 25);"
                                    aria-label="Effacer l'historique, action définitive"
                                >
                                    Effacer l'historique
                                </button>
                                <button
                                    type="button"
                                    @click="showClearConfirm = false"
                                    class="flex-1 h-10 rounded-xl text-sm font-semibold ring-1 ring-sand-200 bg-white text-ink-700 hover:bg-sand-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                                >
                                    Garder
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </div>

</div>

{{-- x-cloak : masquer avant l'init Alpine --}}
@once
@push('head')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush
@endonce
