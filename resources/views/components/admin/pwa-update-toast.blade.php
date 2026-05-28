{{--
    Toast mise à jour PWA — Plan 02-05.
    UI-SPEC §"Mise à jour PWA (Update prompt)".

    Visible quand Alpine.store('pwaUpdate').available === true
    (déclenché par onNeedRefresh dans app.js — registerType 'prompt').

    Boutons :
    - "Recharger" → $store.pwaUpdate.apply() (appelle updateSW(true) via virtual:pwa-register)
    - "Plus tard" → ferme le toast sans appliquer la mise à jour
--}}

<div
    x-data
    x-show="$store.pwaUpdate.available"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="fixed top-4 left-4 right-4 z-[60] max-w-sm mx-auto"
    role="status"
    aria-live="polite">
    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-md px-4 py-3 flex items-center justify-between gap-3">
        <p class="text-sm text-ink-700 font-medium">Mise à jour disponible</p>
        <div class="flex items-center gap-1.5 shrink-0">
            <button
                type="button"
                @click="$store.pwaUpdate.apply()"
                class="h-8 px-3 rounded-xl bg-azure-500 text-white text-sm font-semibold hover:bg-azure-600 transition-colors">
                Recharger
            </button>
            <button
                type="button"
                @click="$store.pwaUpdate.available = false"
                class="h-8 px-3 rounded-xl text-ink-500 text-sm hover:bg-sand-100 transition-colors">
                Plus tard
            </button>
        </div>
    </div>
</div>
