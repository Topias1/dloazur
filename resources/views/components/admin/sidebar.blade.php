{{--
    Admin sidebar component (D-18).
    Props: $user (App\Models\User) — authenticated operator.

    Active item: Tableau de bord (linked to admin.dashboard, bg-white/10)
    Greyed items (Phase 2/3): Clients, Passages, Factures, Catalogue
      aria-disabled="true", tabindex="-1", opacity-60, cursor-default, 'bientôt' badge.
    User pill at bottom: avatar 'P', name, role 'Pisciniste', logout form.
--}}
@props(['user'])

<aside class="hidden lg:flex flex-col bg-navy-900 text-navy-100 px-4 py-6 sticky top-0 h-screen">

    {{-- Logo + brand --}}
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-2 mb-8">
        <span class="text-azure-400">
            <x-icon.drop :size="28" />
        </span>
        <span class="leading-none">
            <span class="block font-display font-semibold text-white text-lg">Dlo Azur</span>
            <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-azure-400">Métier</span>
        </span>
    </a>

    {{-- Navigation --}}
    <nav class="space-y-1 text-sm font-medium">

        {{-- Active: Tableau de bord --}}
        <a href="{{ route('admin.dashboard') }}"
            @class([
                'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
                'bg-white/10 text-white'    => request()->routeIs('admin.dashboard'),
                'hover:bg-white/8 hover:text-white' => !request()->routeIs('admin.dashboard'),
            ])>
            {{-- Home icon --}}
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 12 12 3l9 9M5 10v10h14V10"/>
            </svg>
            Tableau de bord
        </a>

        {{-- Active: Mon agenda (admin-1, Plan 07-02) --}}
        <a href="{{ route('admin.agenda.index') }}"
            @class([
                'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
                'bg-white/10 text-white'             => request()->routeIs('admin.agenda.*'),
                'hover:bg-white/8 hover:text-white'  => !request()->routeIs('admin.agenda.*'),
            ])
            @if(request()->routeIs('admin.agenda.*')) aria-current="page" @endif>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/>
            </svg>
            Mon agenda
        </a>

        {{-- Active: Clients (Plan 02-02) --}}
        <a href="{{ route('admin.clients.index') }}"
            @class([
                'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
                'bg-white/10 text-white'             => request()->routeIs('admin.clients.*'),
                'hover:bg-white/8 hover:text-white'  => !request()->routeIs('admin.clients.*'),
            ])
            @if(request()->routeIs('admin.clients.*')) aria-current="page" @endif>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            </svg>
            Clients
        </a>

        {{-- Active: Passages (Plan 02-05) --}}
        <a href="{{ route('admin.passages.create') }}"
            x-data
            @class([
                'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
                'bg-white/10 text-white'            => request()->routeIs('admin.passages.*'),
                'hover:bg-white/8 hover:text-white' => !request()->routeIs('admin.passages.*'),
            ])
            @if(request()->routeIs('admin.passages.*')) aria-current="page" @endif>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/>
            </svg>
            Passages
            {{-- Badge pending IDB (PASS-06) --}}
            <span
                x-show="$store.offlineQueue.pendingCount > 0"
                x-cloak
                class="ml-auto h-5 min-w-5 rounded-full bg-warn text-xs font-bold grid place-items-center px-1"
                style="color: oklch(0.32 0.09 70);"
                x-text="$store.offlineQueue.pendingCount"
                aria-live="polite"
                aria-label="passages en attente de synchronisation"></span>
        </a>

        {{-- Active: Blog (Phase 6, Plan 06-03) --}}
        <a href="{{ route('admin.blog.index') }}"
            @class([
                'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
                'bg-white/10 text-white'             => request()->routeIs('admin.blog.*'),
                'hover:bg-white/8 hover:text-white'  => !request()->routeIs('admin.blog.*'),
            ])
            @if(request()->routeIs('admin.blog.*')) aria-current="page" @endif>
            {{-- feather file-text icon (UI-SPEC Surface 1 §Sidebar) --}}
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <path d="M14 2v6h6"/>
                <path d="M16 13H8M16 17H8M10 9H8"/>
            </svg>
            Blog
        </a>

        {{-- Active: Récap mensuel --}}
        <a href="{{ route('admin.recap.index') }}"
            @class([
                'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
                'bg-white/10 text-white'             => request()->routeIs('admin.recap.*'),
                'hover:bg-white/8 hover:text-white'  => !request()->routeIs('admin.recap.*'),
            ])
            @if(request()->routeIs('admin.recap.*')) aria-current="page" @endif>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18"/>
            </svg>
            Récap mensuel
        </a>

        {{-- Greyed: Factures (Phase 3) --}}
        <span aria-disabled="true" tabindex="-1"
            class="flex items-center gap-3 h-11 px-3 rounded-xl text-navy-300 opacity-60 cursor-default">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <path d="M14 2v6h6M9 13h6M9 17h6"/>
            </svg>
            Factures
            <span class="ml-auto text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/8 text-navy-200">bientôt</span>
        </span>

        {{-- Greyed: Catalogue (Phase 3) --}}
        <span aria-disabled="true" tabindex="-1"
            class="flex items-center gap-3 h-11 px-3 rounded-xl text-navy-300 opacity-60 cursor-default">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="2" y="3" width="7" height="7"/>
                <rect x="15" y="3" width="7" height="7"/>
                <rect x="15" y="14" width="7" height="7"/>
                <rect x="2" y="14" width="7" height="7"/>
            </svg>
            Catalogue
            <span class="ml-auto text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/8 text-navy-200">bientôt</span>
        </span>

    </nav>

    {{-- User pill at bottom (D-18, UI-SPEC §Sidebar) --}}
    <div class="mt-auto pt-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-2">
            {{-- Avatar: initial 'P' on azure-500 --}}
            <span class="grid h-9 w-9 place-items-center rounded-full bg-azure-500 text-white font-display font-semibold shrink-0">
                P
            </span>
            <div class="flex-1 min-w-0 text-sm">
                <p class="text-white font-semibold leading-tight truncate">{{ $user->name }}</p>
                <p class="text-navy-300 text-xs">Pisciniste</p>
            </div>
            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit"
                    title="Déconnexion"
                    class="text-navy-300 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10"
                    aria-label="Déconnexion">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</aside>
