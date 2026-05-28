{{--
    Admin topbar component (D-17, UI-SPEC §Admin shell topbar).
    Sticky, bg-sand-100/90 backdrop-blur.
    'Nouveau passage' button: disabled in Phase 1 (aria-disabled, cursor-not-allowed, opacity-50).
    Search input: disabled in Phase 1.
--}}

<header class="sticky top-0 z-30 bg-sand-100/90 backdrop-blur border-b border-navy-900/8">
    <div class="px-5 sm:px-8 h-16 flex items-center justify-between gap-4">

        {{-- Mobile logo (shown when sidebar is hidden) --}}
        <a href="{{ route('admin.dashboard') }}" class="lg:hidden flex items-center gap-2 shrink-0">
            <span class="text-azure-500">
                <x-icon.drop :size="24" />
            </span>
            <span class="font-display font-semibold text-ink-950">Dlo Azur</span>
        </a>

        {{-- Desktop search (greyed out — Phase 2 wires actual search) --}}
        <div class="hidden lg:flex items-center gap-2 flex-1 max-w-md">
            <div class="relative w-full">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-400"
                    width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input disabled
                    class="w-full h-10 pl-10 pr-4 rounded-xl bg-white/60 ring-1 ring-navy-900/10 text-ink-400 placeholder:text-ink-400 outline-none cursor-not-allowed opacity-60"
                    placeholder="Recherche disponible en Phase 2…"
                    aria-label="Recherche (disponible en Phase 2)">
            </div>
        </div>

        {{-- Nouveau passage CTA — disabled Phase 1 --}}
        <button type="button" disabled aria-disabled="true"
            class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-azure-500/40 text-white font-semibold cursor-not-allowed opacity-50"
            title="Disponible en Phase 2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span class="hidden sm:inline">Nouveau passage</span>
            <span class="sm:hidden">Passage</span>
        </button>

    </div>
</header>
