{{--
    Sync drawer — panneau glissant liste les passages en attente de synchronisation.
    Plan 02-05, UI-SPEC §"Sync drawer".

    Architecture :
    - Utilise Alpine.store('syncDrawer') (enregistré dans app.js via syncDrawerStore()).
    - Mobile : glisse depuis le bas (translate-y).
    - Desktop : glisse depuis la droite (translate-x).
    - role="dialog" aria-modal="true" + backdrop.
--}}

<div x-data x-cloak>

    {{-- Backdrop --}}
    <div
        x-show="$store.syncDrawer.open"
        x-transition.opacity.duration.200ms
        @click="$store.syncDrawer.open = false"
        class="fixed inset-0 z-40 bg-navy-950/40"
        aria-hidden="true"></div>

    {{-- Drawer panel --}}
    <div
        x-show="$store.syncDrawer.open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full"
        x-transition:enter-end="translate-y-0 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 sm:translate-x-0"
        x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full"
        role="dialog"
        aria-modal="true"
        aria-label="Passages en attente de synchronisation"
        class="fixed bottom-0 inset-x-0 sm:inset-x-auto sm:right-0 sm:top-0 sm:bottom-0 sm:w-[420px] z-50 bg-white rounded-t-3xl sm:rounded-none sm:rounded-l-3xl shadow-2xl flex flex-col">

        <header class="flex items-center justify-between p-4 border-b border-sand-200 shrink-0">
            <h2 class="font-display font-semibold text-lg text-ink-950">Synchro en attente</h2>
            <button
                type="button"
                @click="$store.syncDrawer.open = false"
                class="h-10 w-10 rounded-xl hover:bg-sand-100 grid place-items-center text-ink-500"
                aria-label="Fermer le panneau">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </header>

        {{-- Liste des passages en queue --}}
        <ul class="flex-1 overflow-y-auto p-4 space-y-2" role="list">

            <template x-if="$store.syncDrawer.loading">
                <li class="text-center text-ink-400 text-sm py-8">Chargement…</li>
            </template>

            <template x-for="item in $store.syncDrawer.items" :key="item.id">
                <li class="flex items-center gap-3 rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-ink-900 truncate"
                           x-text="item.visited_at ? new Date(item.visited_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Passage en cours'"></p>
                        <p class="text-xs text-ink-500"
                           x-text="`${item.actionsCount} action(s) · ${item.attempts} tentative(s)`"></p>
                    </div>
                    <span
                        class="rounded-full text-xs font-semibold px-2.5 py-0.5 shrink-0"
                        :class="$store.syncDrawer.statusClass(item.status)"
                        x-text="$store.syncDrawer.statusLabel(item.status)"></span>
                    <template x-if="item.status === 'error'">
                        <button
                            type="button"
                            @click="$store.syncDrawer.retry(item.id)"
                            class="h-8 px-3 rounded-xl bg-azure-500 text-white text-sm font-semibold shrink-0 hover:bg-azure-600">
                            Réessayer
                        </button>
                    </template>
                </li>
            </template>

            <template x-if="$store.syncDrawer.items.length === 0 && !$store.syncDrawer.loading">
                <li class="text-center text-ink-500 text-sm py-8">Tout est synchronisé.</li>
            </template>

        </ul>

        <div class="p-4 border-t border-sand-200 shrink-0">
            <button
                type="button"
                @click="$store.syncDrawer.flushAll()"
                class="h-11 w-full rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors">
                Synchroniser maintenant
            </button>
        </div>

    </div>
</div>
