{{--
    Diagnostic piscine — wizard Livewire / Alpine (Plan 05-01)
    Surfaces : S2 (arbre symptôme), S4 (disclaimer gate), S5 minimal (feuille résultat)

    Architecture (D-01) :
    - Alpine    : navigation entre steps (step, mode, nodeId, tried) — jamais lié Livewire
    - Livewire  : état serveur (disclaimerAccepted, triedActions) + actions (acceptDisclaimer)
    - wire:ignore.self sur la racine Alpine pour éviter le reset du step (Pitfall 1)

    DIAG-02 invariant : aucune formule arithmétique dans ce fichier ni dans le JS emis.
    L'arbre config est passé en Blade statique côté serveur (@js) — lecture seule pour Alpine.

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
            }
            if (next.kind === 'result') {
                this.resultId = next.id;
                this.step = 'result';
            } else if (next.kind === 'question') {
                this.nodeId = next.id;
                this.step = 'tree';
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
        },

        // Vérifier si une action est déjà tentée
        isTried(val) {
            return this.triedActions.includes(val);
        }
    }"
    wire:ignore.self
    x-cloak
    class="w-full"
>

    {{-- ═══════════════════════════════════════════════════════════
         S0 — Choix du mode (Symptôme / Chimie)
         Affiché si step === 'mode' — reproduit les tuiles de la landing mais dans le wizard
    ═══════════════════════════════════════════════════════════ --}}
    <div x-show="step === 'mode'" x-transition.opacity.duration.200ms>
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
                    @click="advance({ value: 'chemistry', next: { kind: 'question', id: 'start' } })"
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
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S4 — Disclaimer gate (register: product)
         Inline progressive step (jamais une modale — UI-SPEC S4)
         Affiché au nœud 'start' avant que le visiteur commence
         NB : pour la version MVP, le disclaimer est montré au tout début du parcours.
    ═══════════════════════════════════════════════════════════ --}}
    {{-- Le disclaimer est affiché avant le premier nœud si non encore accepté --}}
    <div
        x-show="step === 'tree' && nodeId === 'start' && !$wire.disclaimerAccepted"
        x-transition.opacity.duration.200ms
    >
        <div class="py-6">
            {{-- Card disclaimer — fond sand, pas de rouge alarmant (UI-SPEC S4) --}}
            <div class="rounded-2xl bg-white ring-1 ring-sand-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="shrink-0 h-9 w-9 rounded-xl bg-warn-bg ring-1 ring-warn/30 grid place-items-center" style="background: oklch(0.965 0.045 85);">
                            <x-icon.shield :size="18" class="text-warn" style="color: oklch(0.800 0.130 80);" />
                        </span>
                        <div>
                            <h2 class="font-display font-semibold text-ink-950 text-lg">Avant de commencer</h2>
                            <p class="text-sm text-ink-500 mt-0.5">Conseils indicatifs — Dlo Azur Piscines</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm text-ink-700 leading-relaxed">
                        <p>
                            <strong class="text-ink-900">Conseils indicatifs</strong> — ces recommandations ne remplacent pas l'avis d'un pisciniste.
                            En cas de doute, contacte un professionnel.
                        </p>
                        <p>
                            Vérifie toujours la notice de tes produits. Les doses indiquées sont des valeurs orientatives
                            à vérifier selon le titre de tes produits.
                        </p>
                        <p class="text-ink-600">
                            Ce diagnostic est fourni à titre indicatif par <strong class="text-ink-800">Pierre ADAM — Dlo Azur Piscines</strong>.
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
         Question par question, Alpine traverse le config PHP
         Affiché si step === 'tree' ET disclaimer accepté
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="step === 'tree' && (nodeId !== 'start' || $wire.disclaimerAccepted)"
        x-transition.opacity.duration.200ms
    >
        <template x-if="getNode(nodeId)">
            <div class="py-6">
                {{-- Bouton retour --}}
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

                {{-- Question --}}
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

                {{-- Options — grande tuile tactile ≥ h-13 (52px) --}}
                <div class="flex flex-col gap-3">
                    <template x-for="(option, idx) in (getNode(nodeId)?.options ?? [])" :key="idx">
                        <button
                            type="button"
                            @click="advance(option)"
                            class="group flex items-center gap-3 min-h-[52px] h-13 px-4 rounded-2xl bg-white ring-1 ring-sand-200 hover:ring-azure-500 hover:bg-azure-50 active:scale-[0.99] transition-all text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                        >
                            {{-- Emoji anchor --}}
                            <span class="shrink-0 text-xl leading-none w-8 text-center" x-text="option.emoji ?? ''"></span>
                            {{-- Label --}}
                            <span class="font-semibold text-ink-900 text-sm leading-snug" x-text="option.label"></span>
                            {{-- Flèche --}}
                            <x-icon.arrow-right :size="14" class="ml-auto text-ink-300 group-hover:text-azure-500 group-hover:translate-x-0.5 transition-all shrink-0" />
                        </button>
                    </template>
                </div>

                {{-- Nœud "tried" (multiselect chips) — déclenché par un nœud avec id='tried' --}}
                <div x-show="nodeId === 'tried'" class="mt-6">
                    <p class="text-sm font-semibold text-ink-700 mb-3">
                        Qu'as-tu déjà tenté ? <span class="font-normal text-ink-400">(pour ne pas te reproposer ce qui n'a pas marché)</span>
                    </p>
                    <div class="flex flex-wrap gap-2">
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
            </div>
        </template>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         S5 minimal — Feuille résultat (register: product)
         Gabarit fixe (CDC §7, UI-SPEC S5) :
         1. Diagnostic statement (Fredoka headline) + confidence chip
         2. Pourquoi (analyse)
         3. Safety block (ambre, avant tout geste chimique)
         4. Étapes ordonnées
         5. Quand appeler Pierre (escalade WhatsApp)
    ═══════════════════════════════════════════════════════════ --}}
    <div
        x-show="step === 'result'"
        x-transition.opacity.duration.300ms
    >
        <template x-if="getResult(resultId)">
            <div class="py-6">
                {{-- Bouton retour --}}
                <button
                    type="button"
                    @click="back()"
                    class="mb-5 inline-flex items-center gap-1.5 h-9 px-3 -ml-1 rounded-xl text-sm text-ink-500 hover:text-ink-900 hover:bg-sand-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-500"
                    aria-label="Retour à la question précédente"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Revenir
                </button>

                {{-- ① Diagnostic statement (Fredoka headline) + confidence chip --}}
                <div class="mb-6">
                    <div class="flex items-start gap-3 flex-wrap">
                        <h2
                            class="font-display font-bold text-ink-950 leading-[1.1]"
                            style="font-size: clamp(1.875rem, 3vw, 2.5rem);"
                            x-text="getResult(resultId)?.diagnostic ?? ''"
                        ></h2>
                        {{-- Confidence chip --}}
                        <span
                            x-show="getResult(resultId)?.confidence"
                            :class="{
                                'bg-success/10 ring-success/20 text-success': getResult(resultId)?.confidence === 'eleve',
                                'bg-warn-bg ring-warn/20 text-warn': getResult(resultId)?.confidence === 'moyen'
                            }"
                            class="inline-flex items-center gap-1 mt-1 h-7 px-2.5 rounded-full ring-1 text-xs font-bold uppercase tracking-wide shrink-0"
                            style="
                                color: oklch(var(--color-success) / 1);
                            "
                        >
                            <span
                                :class="{
                                    'bg-success': getResult(resultId)?.confidence === 'eleve',
                                    'bg-warn': getResult(resultId)?.confidence === 'moyen'
                                }"
                                class="inline-block h-1.5 w-1.5 rounded-full"
                                style="background: oklch(0.700 0.150 155);"
                            ></span>
                            <span x-text="getResult(resultId)?.confidence === 'eleve' ? 'Confiance élevée' : 'Confiance moyenne'"></span>
                        </span>
                    </div>
                </div>

                {{-- ② Pourquoi (analyse pédagogique) --}}
                <p
                    x-show="getResult(resultId)?.analyse"
                    x-text="getResult(resultId)?.analyse ?? ''"
                    class="text-ink-700 leading-relaxed mb-6 max-w-[65ch]"
                ></p>

                {{-- ③ Safety block ambre — affiché AVANT tout geste chimique (UI-SPEC S5, Audit §6 P0) --}}
                <div
                    x-show="getResult(resultId)?.safety_block"
                    class="mb-6 rounded-xl p-4 flex items-start gap-3"
                    style="background: oklch(0.965 0.045 85); outline: 1px solid oklch(0.800 0.130 80 / 0.25);"
                    role="status"
                    aria-label="Bloc sécurité"
                >
                    <x-icon.shield :size="18" class="shrink-0 mt-0.5" style="color: oklch(0.800 0.130 80);" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide mb-1.5" style="color: oklch(0.800 0.130 80);">Sécurité — avant de manipuler</p>
                        <p
                            class="text-sm leading-relaxed text-ink-800"
                            x-text="getResult(resultId)?.safety_block ?? ''"
                        ></p>
                    </div>
                </div>

                {{-- ④ Étapes ordonnées (plan) — liste différenciée, pas une grille uniforme --}}
                <div x-show="(getResult(resultId)?.plan ?? []).length > 0" class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">PLAN D'ACTION</p>
                    <div class="rounded-3xl bg-white ring-1 ring-sand-200 overflow-hidden">
                        <template x-for="(step_item, idx) in (getResult(resultId)?.plan ?? [])" :key="idx">
                            <div
                                :class="idx > 0 ? 'border-t border-sand-100' : ''"
                                class="flex items-start gap-3 px-5 py-3.5"
                            >
                                {{-- Numéro ou bullet --}}
                                <span
                                    class="shrink-0 inline-flex h-6 w-6 items-center justify-center rounded-full bg-azure-50 text-azure-600 text-xs font-bold font-variant-numeric tabular-nums mt-0.5"
                                    x-text="idx + 1"
                                ></span>
                                {{-- Texte de l'étape --}}
                                <span class="text-sm text-ink-800 leading-relaxed" x-text="step_item"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Reminder re-test --}}
                <p
                    x-show="getResult(resultId)?.retest_reminder"
                    class="text-sm font-semibold text-ink-600 mb-8 flex items-center gap-2"
                >
                    <x-icon.arrow-right :size="14" class="text-azure-500 shrink-0" />
                    <span x-text="getResult(resultId)?.retest_reminder ?? ''"></span>
                </p>

                {{-- ⑤ Quand appeler Pierre — escalade WhatsApp (S6, DIAG-06) --}}
                <div class="rounded-2xl bg-navy-900 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-2">DEMANDER UNE INTERVENTION</p>
                    <h3 class="font-display font-semibold text-sand-50 text-lg mb-1">Pierre peut intervenir rapidement</h3>
                    <p class="text-sm text-sand-100/70 mb-4 leading-relaxed">
                        Un plan ne suffit pas ou tu préfères être accompagné ?
                        Pierre (Dlo Azur Piscines) intervient en Martinique — envoie-lui ton diagnostic directement sur WhatsApp.
                    </p>
                    <a
                        :href="'https://wa.me/596696940054?text=' + encodeURIComponent(
                            'Bonjour Pierre, j\'ai fait un diagnostic sur le site Dlo Azur.\n\n'
                            + 'Problème détecté : ' + (getResult(resultId)?.diagnostic ?? '') + '\n'
                            + 'Analyse : ' + (getResult(resultId)?.analyse ?? '') + '\n\n'
                            + 'Peux-tu m\'aider ?'
                        )"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 min-h-[44px] h-13 px-5 rounded-xl bg-[#25D366] text-white font-bold text-sm hover:brightness-95 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                    >
                        <x-icon.whatsapp :size="18" />
                        Demander une intervention à Pierre
                    </a>
                </div>

                {{-- Recommencer --}}
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

    {{-- Honeypot (pour lead-capture, Plan 05-03) --}}
    <div aria-hidden="true" tabindex="-1" style="display:none">
        <x-honeypot livewire-model="extraFields" />
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
