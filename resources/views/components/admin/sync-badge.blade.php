{{--
    Sync badge — bouton topbar "N en attente" (PASS-06).
    Plan 02-05, UI-SPEC §"Admin topbar — Badge sync 'N passages en attente'".

    Visible uniquement si pendingCount > 0.
    Clic → dispatch 'sync-drawer:open' (écouté par syncDrawerStore.init()).
    Dot animate-pulse ambre.
--}}

<div x-data aria-live="polite" class="flex items-center gap-1.5">

    {{-- Badge pending --}}
    <template x-if="$store.offlineQueue.pendingCount > 0">
        <button
            type="button"
            @click="window.dispatchEvent(new CustomEvent('sync-drawer:open'))"
            class="flex items-center gap-1.5 h-9 px-3 rounded-xl ring-1 text-sm font-semibold transition-colors hover:ring-warn/50"
            style="background-color: oklch(0.90 0.07 82 / 0.15); --tw-ring-color: oklch(0.75 0.10 75 / 0.30); color: oklch(0.5 0.11 72);"
            :aria-label="`${$store.offlineQueue.pendingCount} passage(s) en attente de synchronisation`">
            <span class="h-2 w-2 rounded-full bg-warn animate-pulse shrink-0"></span>
            <span x-text="`${$store.offlineQueue.pendingCount} en attente`"></span>
        </button>
    </template>

    {{-- Badge error (rouge) --}}
    <template x-if="$store.offlineQueue.errorCount > 0">
        <button
            type="button"
            @click="window.dispatchEvent(new CustomEvent('sync-drawer:open'))"
            class="flex items-center gap-1.5 h-9 px-3 rounded-xl bg-danger/10 ring-1 ring-danger/30 text-danger text-sm font-semibold hover:bg-danger/15"
            :aria-label="`${$store.offlineQueue.errorCount} passage(s) en erreur`">
            <span class="h-2 w-2 rounded-full bg-danger shrink-0"></span>
            <span x-text="`${$store.offlineQueue.errorCount} erreur(s)`"></span>
        </button>
    </template>

</div>
