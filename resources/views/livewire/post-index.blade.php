<div class="px-5 sm:px-8 py-7 space-y-7">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">Blog</h1>
        <a href="{{ route('admin.blog.create') }}"
            class="h-11 px-5 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors inline-flex items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Nouvel article
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
            placeholder="Rechercher un article…"
            class="w-full h-10 pl-10 pr-4 rounded-xl bg-white ring-1 ring-navy-900/10 focus:ring-2 focus:ring-azure-500 outline-none"
            aria-label="Rechercher un article">
    </div>

    {{-- Article list --}}
    <div class="space-y-3">
        @forelse ($posts as $post)
            <a href="{{ route('admin.blog.edit', $post) }}"
                class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-4 flex items-center gap-4 hover:bg-sand-50 transition-colors min-h-[64px]">

                {{-- Cover thumbnail or fallback --}}
                @php $coverUrl = $post->getFirstMediaUrl('cover', 'thumbnail'); @endphp
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        alt="{{ $post->title }}"
                        class="h-11 w-14 rounded-lg object-cover ring-1 ring-navy-900/8 shrink-0">
                @else
                    <span class="h-11 w-14 rounded-lg bg-azure-50 ring-1 ring-navy-900/8 flex items-center justify-center shrink-0 text-azure-400">
                        <x-icon.drop :size="22" />
                    </span>
                @endif

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-ink-900 truncate">{{ $post->title }}</p>
                    <p class="text-sm text-ink-500 truncate tabular-nums">
                        @if ($post->date)
                            {{ $post->date->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </p>
                </div>

                {{-- Status badge — shrink-0; hidden on very small screens --}}
                <span class="shrink-0 hidden sm:inline-flex">
                    <x-admin.status-badge :status="$post->status" />
                </span>

                {{-- Mobile: badge below title (visible when sm:hidden badge is hidden) --}}
                {{-- Chevron --}}
                <svg class="text-ink-400 shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m9 18 6-6-6-6"/>
                </svg>

            </a>
        @empty
            @if ($search === '')
                {{-- Empty state — no articles yet --}}
                <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-8 text-center">
                    <h2 class="font-display font-semibold text-xl text-ink-950">Aucun article pour l'instant.</h2>
                    <p class="text-ink-500 mt-2">Écrivez votre premier article : il apparaîtra sur le blog une fois publié.</p>
                    <a href="{{ route('admin.blog.create') }}"
                        class="mt-4 inline-flex h-11 px-5 rounded-xl bg-azure-500 text-white font-semibold items-center hover:bg-azure-600 transition-colors">
                        Écrire un article
                    </a>
                </div>
            @else
                {{-- No search results --}}
                <div class="rounded-2xl bg-white ring-1 ring-sand-200 p-6 text-center">
                    <p class="text-ink-700">Aucun résultat pour « {{ $search }} ».</p>
                    <p class="text-ink-500 text-sm mt-1">Vérifiez l'orthographe ou essayez un autre titre.</p>
                </div>
            @endif
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($posts->hasPages())
        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @endif

</div>
