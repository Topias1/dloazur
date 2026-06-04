@extends('layouts.admin')

@section('title', 'Mon agenda du jour · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <div class="px-5 sm:px-8 py-7 max-w-3xl space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">
                Mon agenda du jour
            </h1>
            <p class="text-ink-500 mt-1 capitalize">{{ $today }}</p>
        </div>

        {{-- Bloc 1 : Piscines attendues aujourd'hui --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <h2 class="font-display font-semibold text-base text-ink-900 mb-4">
                Aujourd'hui
                @if ($piscinesAujourdhui->isNotEmpty())
                    <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-sand-100 text-ink-500">
                        {{ $piscinesAujourdhui->count() }}
                    </span>
                @endif
            </h2>

            @forelse ($piscinesAujourdhui as $piscine)
                <div class="flex items-center justify-between gap-4 py-3 border-b border-sand-100 last:border-0">
                    <div class="min-w-0">
                        <p class="font-medium text-ink-900 truncate">{{ $piscine->client?->name ?? '—' }}</p>
                        <p class="text-sm text-ink-500 truncate">{{ $piscine->nom }}</p>
                    </div>
                    <a href="{{ route('admin.passages.create', ['client_id' => $piscine->client_id]) }}"
                        class="shrink-0 h-10 px-4 rounded-xl bg-azure-500 text-white text-sm font-semibold hover:bg-azure-600 transition-colors inline-flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Saisir
                    </a>
                </div>
            @empty
                <p class="text-ink-400 text-sm">Aucune piscine prévue aujourd'hui.</p>
            @endforelse
        </div>

        {{-- Bloc 2 : Flags à revoir (passages récents avec note interne) --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <h2 class="font-display font-semibold text-base text-ink-900 mb-4">
                À revoir
                @if ($aRevoir->isNotEmpty())
                    <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-warn/15 text-warn-700">
                        {{ $aRevoir->count() }}
                    </span>
                @endif
            </h2>

            @forelse ($aRevoir as $passage)
                <div class="flex items-start justify-between gap-4 py-3 border-b border-sand-100 last:border-0">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-medium text-ink-900">{{ $passage->client?->name ?? '—' }}</p>
                            @if ($passage->piscine)
                                <span class="text-ink-400 text-sm">·</span>
                                <p class="text-sm text-ink-500">{{ $passage->piscine->nom }}</p>
                            @endif
                            <span class="text-xs text-ink-400">{{ $passage->visited_at?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <p class="text-sm text-ink-700 mt-1 line-clamp-2">{{ $passage->notes_privees }}</p>
                    </div>
                    @if ($passage->client_id)
                        <a href="{{ route('admin.passages.create', ['client_id' => $passage->client_id]) }}"
                            class="shrink-0 h-9 px-3 rounded-xl bg-white ring-1 ring-sand-200 text-ink-700 text-sm font-semibold hover:bg-sand-50 transition-colors inline-flex items-center gap-1.5">
                            Saisir
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-ink-400 text-sm">Rien à revoir.</p>
            @endforelse
        </div>

    </div>

    <x-admin.mobile-bottom-nav />
@endsection
