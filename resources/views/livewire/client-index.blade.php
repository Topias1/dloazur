<div class="px-5 sm:px-8 py-7 space-y-7">

    {{-- Inline success (flash from client-form save) --}}
    @if (session('status') === 'client-saved')
        <p role="status" class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium"
            style="background: oklch(0.96 0.03 155); color: oklch(0.42 0.12 155); box-shadow: inset 0 0 0 1px oklch(0.80 0.08 155);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Client enregistré.
        </p>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">Clients</h1>
        <a href="{{ route('admin.clients.create') }}"
            class="h-11 px-5 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors inline-flex items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Nouveau client
        </a>
    </div>

    {{-- Search --}}
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 pointer-events-none"
            width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <circle cx="11" cy="11" r="7"/>
            <path d="m21 21-4.3-4.3"/>
        </svg>
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Rechercher un client…"
            class="w-full h-10 pl-10 pr-4 rounded-xl bg-white ring-1 ring-navy-900/10 focus:ring-2 focus:ring-azure-500 outline-none"
            aria-label="Rechercher un client">
    </div>

    {{-- Client list --}}
    <div class="space-y-3"
        wire:loading.class="opacity-50 pointer-events-none"
        wire:target="search">
        {{-- Loading skeleton --}}
        <div wire:loading wire:target="search"
            class="h-12 bg-sand-100 rounded-xl animate-pulse"
            aria-hidden="true"></div>
        @forelse ($clients as $client)
            <a href="{{ route('admin.clients.show', $client) }}"
                class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-4 flex items-center gap-4 hover:bg-sand-50 transition-colors min-h-[64px]">

                {{-- Avatar initiales --}}
                @php
                    $initials = \Illuminate\Support\Str::of($client->name)
                        ->explode(' ')
                        ->map(fn($w) => \Illuminate\Support\Str::substr($w, 0, 1))
                        ->take(2)
                        ->implode('');
                @endphp
                <span class="h-11 w-11 rounded-full bg-azure-50 text-azure-700 font-display font-semibold grid place-items-center shrink-0 text-sm uppercase">
                    {{ $initials }}
                </span>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-ink-900 truncate">{{ $client->name }}</p>
                    <p class="text-sm text-ink-500 truncate">{{ $client->email ?? '—' }} · {{ $client->phone ?? '—' }}</p>
                </div>

                {{-- Chip piscine --}}
                @if ($client->piscines->isNotEmpty())
                    <span class="rounded-full bg-sand-100 text-ink-500 text-xs px-2.5 py-0.5 shrink-0 hidden sm:inline-flex">
                        {{ $client->piscines->first()->type ?: 'piscine' }} · {{ $client->piscines->first()->volume_m3 }} m³
                    </span>
                @endif

                {{-- Chevron --}}
                <svg class="text-ink-400 shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m9 18 6-6-6-6"/>
                </svg>

            </a>
        @empty
            @if ($search === '')
                {{-- Empty state — no clients yet --}}
                <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-8 text-center">
                    <h2 class="font-display font-semibold text-xl text-ink-950">Aucun client pour l'instant.</h2>
                    <p class="text-ink-500 mt-2">Ajoute ton premier client pour commencer à saisir des passages.</p>
                    <a href="{{ route('admin.clients.create') }}"
                        class="mt-4 inline-flex h-11 px-5 rounded-xl bg-azure-500 text-white font-semibold items-center hover:bg-azure-600 transition-colors">
                        Ajouter un client
                    </a>
                </div>
            @else
                {{-- No search results --}}
                <div class="rounded-2xl bg-white ring-1 ring-sand-200 p-6 text-center">
                    <p class="text-ink-700">Aucun résultat pour « {{ $search }} ».</p>
                    <p class="text-ink-500 text-sm mt-1">Vérifiez l'orthographe ou essayez un autre nom.</p>
                </div>
            @endif
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($clients->hasPages())
        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    @endif

</div>
