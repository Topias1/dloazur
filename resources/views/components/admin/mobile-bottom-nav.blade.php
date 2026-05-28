{{--
    Mobile bottom navigation (md:hidden).
    4 items: Tableau de bord (active), Clients (greyed), Passages (greyed), Factures (greyed).
    Uses 'nav-label' one-off utility (Inter 600 11px) per UI-SPEC.
    Greyed items: aria-disabled="true", opacity-60, cursor-default.
--}}

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-sand-50/95 backdrop-blur border-t border-navy-900/10 grid grid-cols-4 h-18 px-2"
    aria-label="Navigation mobile">

    {{-- Active: Tableau de bord --}}
    <a href="{{ route('admin.dashboard') }}"
        class="flex flex-col items-center justify-center gap-1 text-azure-600"
        aria-current="page">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 12 12 3l9 9M5 10v10h14V10"/>
        </svg>
        <span class="text-[11px] font-semibold">Accueil</span>
    </a>

    {{-- Active: Clients (Plan 02-02) --}}
    <a href="{{ route('admin.clients.index') }}"
        class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('admin.clients.*') ? 'text-azure-600' : 'text-ink-400' }}"
        @if(request()->routeIs('admin.clients.*')) aria-current="page" @endif>
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
        </svg>
        <span class="text-[11px] font-semibold">Clients</span>
    </a>

    {{-- Greyed: Passages --}}
    <span aria-disabled="true"
        class="flex flex-col items-center justify-center gap-1 text-ink-400 opacity-60 cursor-default">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/>
        </svg>
        <span class="text-[11px] font-medium">Passages</span>
    </span>

    {{-- Greyed: Factures --}}
    <span aria-disabled="true"
        class="flex flex-col items-center justify-center gap-1 text-ink-400 opacity-60 cursor-default">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6"/>
        </svg>
        <span class="text-[11px] font-medium">Factures</span>
    </span>

</nav>
