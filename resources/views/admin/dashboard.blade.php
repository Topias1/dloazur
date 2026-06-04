@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('sidebar')
    <x-admin.sidebar :user="$user" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <div class="px-5 sm:px-8 py-7 space-y-7">

        {{-- Greeting --}}
        <div>
            <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">
                Bonjour {{ Str::of($user->name)->before(' ') }},
            </h1>
            <p class="text-ink-500 mt-1">Voici ton agenda du jour.</p>
        </div>

        {{-- D-10: Agenda du jour — hoisted, full width, visually dominant --}}
        <div class="rounded-2xl bg-navy-900 text-navy-50 p-6 space-y-5">
            <h2 class="font-display font-semibold text-base text-white mb-1">Aujourd'hui</h2>

            @if (isset($piscinesAujourdhui) && $piscinesAujourdhui->isNotEmpty())
                <div class="space-y-0">
                    @foreach ($piscinesAujourdhui as $piscine)
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-white/10 last:border-0">
                            <div class="min-w-0">
                                <p class="font-medium text-white truncate">{{ $piscine->client?->name ?? '—' }}</p>
                                <p class="text-sm text-navy-200 truncate">{{ $piscine->nom }}</p>
                            </div>
                            <a href="{{ route('admin.passages.create', ['client_id' => $piscine->client_id]) }}"
                                class="shrink-0 h-9 px-3 rounded-xl bg-azure-500 text-white text-sm font-semibold hover:bg-azure-600 transition-colors inline-flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Saisir
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-navy-300 text-sm">Aucune piscine prévue aujourd'hui.</p>
            @endif

            @if (isset($aRevoir) && $aRevoir->isNotEmpty())
                <div>
                    <h3 class="font-semibold text-sm text-navy-200 mb-3 uppercase tracking-wide">
                        À revoir
                        <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-warn/20 text-warn-200">
                            {{ $aRevoir->count() }}
                        </span>
                    </h3>
                    <div class="space-y-0">
                        @foreach ($aRevoir as $passage)
                            <div class="flex items-start justify-between gap-4 py-3 border-b border-white/10 last:border-0">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-white">{{ $passage->client?->name ?? '—' }}</p>
                                    <p class="text-sm text-navy-300 line-clamp-1">{{ $passage->notes_privees }}</p>
                                </div>
                                @if ($passage->client_id)
                                    <a href="{{ route('admin.passages.create', ['client_id' => $passage->client_id]) }}"
                                        class="shrink-0 h-8 px-3 rounded-lg bg-white/10 text-white text-sm font-medium hover:bg-white/20 transition-colors inline-flex items-center">
                                        Saisir
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-1">
                <a href="{{ route('admin.agenda.index') }}"
                    class="text-sm text-azure-400 hover:text-azure-300 font-medium inline-flex items-center gap-1 transition-colors">
                    Voir l'agenda complet
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- D-10: Actionable cards — clickable, not decorative --}}
        {{--
            Règle couleurs (UI-SPEC §Dashboard admin Stat cards — Règle ambre, PATTERNS.md Critical Flag #5) :
            - "À synchroniser" : state="offline" → AMBRE oklch(0.5_0.11_72) — jamais text-danger
            - "Eau à surveiller" : state="warn" si > 0 → ROUGE text-danger — jamais ambre
        --}}
        <div class="grid grid-cols-2 gap-4">
            {{-- À synchroniser → passages pending --}}
            <a href="{{ route('admin.passages.index', ['status' => 'pending']) }}"
                @class([
                    'rounded-2xl p-5 flex flex-col gap-3 ring-1 transition-all hover:shadow-md cursor-pointer group',
                    'bg-warn/15 ring-warn-200 hover:ring-warn/40' => $aSynchroniser > 0,
                    'bg-white ring-navy-900/8 hover:ring-azure-300/50' => $aSynchroniser === 0,
                ])>
                <div class="flex items-start justify-between">
                    <span @class([
                        'text-3xl font-display font-bold leading-none',
                        'text-warn-700' => $aSynchroniser > 0,
                        'text-ink-400' => $aSynchroniser === 0,
                    ])>{{ $aSynchroniser }}</span>
                    <svg @class([
                        'transition-transform group-hover:translate-x-0.5',
                        'text-warn' => $aSynchroniser > 0,
                        'text-ink-300' => $aSynchroniser === 0,
                    ]) width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>
                <span @class([
                    'text-sm font-medium',
                    'text-warn-700' => $aSynchroniser > 0,
                    'text-ink-500' => $aSynchroniser === 0,
                ])>À synchroniser</span>
            </a>

            {{-- Eau à surveiller → passages needs_attention --}}
            <a href="{{ route('admin.passages.index', ['needs_attention' => '1']) }}"
                @class([
                    'rounded-2xl p-5 flex flex-col gap-3 ring-1 transition-all hover:shadow-md cursor-pointer group',
                    'bg-danger/5 ring-danger/30 hover:ring-danger/50' => $eauASurveiller > 0,
                    'bg-white ring-navy-900/8 hover:ring-azure-300/50' => $eauASurveiller === 0,
                ])>
                <div class="flex items-start justify-between">
                    <span @class([
                        'text-3xl font-display font-bold leading-none',
                        'text-danger' => $eauASurveiller > 0,
                        'text-ink-400' => $eauASurveiller === 0,
                    ])>{{ $eauASurveiller }}</span>
                    <svg @class([
                        'transition-transform group-hover:translate-x-0.5',
                        'text-danger/60' => $eauASurveiller > 0,
                        'text-ink-300' => $eauASurveiller === 0,
                    ]) width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </div>
                <span @class([
                    'text-sm font-medium',
                    'text-danger' => $eauASurveiller > 0,
                    'text-ink-500' => $eauASurveiller === 0,
                ])>Eau à surveiller</span>
            </a>
        </div>

        {{-- D-10: Vanity counts demoted to a text strip --}}
        <div class="flex items-center gap-6 text-sm text-ink-500">
            <span><span class="font-semibold text-ink-700">{{ $clientsCount }}</span> clients actifs</span>
            <span class="text-ink-300">·</span>
            <span><span class="font-semibold text-ink-700">{{ $passagesThisWeek }}</span> passages cette semaine</span>
        </div>

    </div>

    {{-- Mobile bottom navigation --}}
    <x-admin.mobile-bottom-nav />

@endsection
