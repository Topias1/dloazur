@extends('layouts.admin')

@section('title', $client->name)

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <div class="px-5 sm:px-8 py-7 max-w-3xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.clients.index') }}"
                class="h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200 flex items-center justify-center text-ink-500 hover:text-ink-900 transition-colors"
                aria-label="Retour à la liste">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="font-display font-semibold text-xl text-ink-950">{{ $client->name }}</h1>
                <p class="text-sm text-ink-500">{{ $client->email }} · {{ $client->phone }}</p>
            </div>
            <a href="{{ route('admin.clients.edit', $client) }}"
                class="h-11 px-5 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors inline-flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Modifier
            </a>
        </div>

        {{-- Informations client --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <h2 class="font-display font-semibold text-base text-ink-900 mb-4">Informations client</h2>
            <dl class="space-y-3">
                @if ($client->address)
                    <div>
                        <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Adresse</dt>
                        <dd class="text-ink-900 mt-0.5">{{ $client->address }}</dd>
                    </div>
                @endif
                @if ($client->notes)
                    <div>
                        <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Notes</dt>
                        <dd class="text-ink-900 mt-0.5 whitespace-pre-wrap">{{ $client->notes }}</dd>
                    </div>
                @endif
                @if (!$client->address && !$client->notes)
                    <p class="text-ink-400 text-sm">Aucune information complémentaire.</p>
                @endif
            </dl>
        </div>

        {{-- Piscine (1-to-1 UI v1, D-64) --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <h2 class="font-display font-semibold text-base text-ink-900 mb-4">Piscine</h2>
            @if ($client->piscines->isNotEmpty())
                @php $piscine = $client->piscines->first(); @endphp
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Volume</dt>
                        <dd class="text-ink-900 mt-0.5">{{ $piscine->volume_m3 }} m³</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Type</dt>
                        <dd class="text-ink-900 mt-0.5">{{ $piscine->type ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Filtration</dt>
                        <dd class="text-ink-900 mt-0.5">{{ $piscine->filtration ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Traitement</dt>
                        <dd class="text-ink-900 mt-0.5">{{ $piscine->traitement ?: '—' }}</dd>
                    </div>
                    @if ($piscine->equipements)
                        <div class="col-span-2">
                            <dt class="text-xs text-ink-500 uppercase tracking-wider font-semibold">Équipements</dt>
                            <dd class="text-ink-900 mt-0.5">{{ implode(', ', $piscine->equipements) }}</dd>
                        </div>
                    @endif
                </dl>
            @else
                <p class="text-ink-400 text-sm mb-4">Pas encore de piscine enregistrée.</p>
                <livewire:piscine-form :clientId="$client->id" />
            @endif
        </div>

        {{-- Historique passages --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <h2 class="font-display font-semibold text-base text-ink-900 mb-4">Historique des passages</h2>
            @forelse ($client->passages()->orderBy('visited_at', 'desc')->paginate(10) as $passage)
                <div class="py-3 border-b border-sand-100 last:border-0">
                    <p class="text-sm text-ink-700">{{ $passage->visited_at?->format('d/m/Y') ?? '—' }}</p>
                </div>
            @empty
                <p class="text-ink-400 text-sm">Aucun passage pour ce client.</p>
            @endforelse
        </div>

    </div>
    <x-admin.mobile-bottom-nav />
@endsection
