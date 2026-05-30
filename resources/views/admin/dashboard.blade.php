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
            <p class="text-ink-500 mt-1">Ta semaine en un coup d'œil.</p>
        </div>

        {{-- Stat cards (Plan 02-03 — valeurs réelles D-62, D-63) --}}
        {{--
            Règle couleurs stat-cards (UI-SPEC §Dashboard admin Stat cards — Règle ambre, PATTERNS.md Critical Flag #5) :
            - "À synchroniser" : state="offline" → AMBRE oklch(0.5_0.11_72) — jamais text-danger
            - "Eau à surveiller" : state="warn" si > 0 → ROUGE text-danger — jamais ambre
        --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-admin.stat-card
                label="Passages cette semaine"
                :value="$passagesThisWeek"
            />
            <x-admin.stat-card
                label="À synchroniser"
                :value="$aSynchroniser"
                state="offline"
            />
            <x-admin.stat-card
                label="Clients actifs"
                :value="$clientsCount"
            />
            <x-admin.stat-card
                label="Eau à surveiller"
                :value="$eauASurveiller"
                :state="$eauASurveiller > 0 ? 'warn' : 'default'"
            />
        </div>

    </div>

    {{-- Mobile bottom navigation --}}
    <x-admin.mobile-bottom-nav />

@endsection
