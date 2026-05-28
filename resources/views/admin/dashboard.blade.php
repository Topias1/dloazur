@extends('layouts.admin')

@section('title', 'Tableau de bord · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="$user" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    {{--
        Dashboard stub (D-19):
        - Greeting: "Bonjour Pierre," (first name extracted from user name)
        - Sub: "Tableau de bord opérationnel en Phase 2."
        - 4 stat cards with '—' values (Phase 1 placeholder)
        Phase 2 will replace '—' values with real queries.
    --}}

    <div class="px-5 sm:px-8 py-7 space-y-7">

        {{-- Greeting --}}
        <div>
            <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">
                Bonjour {{ Str::of($user->name)->before(' ') }},
            </h1>
            <p class="text-ink-500 mt-1">Tableau de bord opérationnel en Phase 2.</p>
        </div>

        {{-- Stat cards (Phase 1 stub — all values '—') --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-admin.stat-card label="Clients actifs" value="—" />
            <x-admin.stat-card label="Passages cette semaine" value="—" />
            <x-admin.stat-card label="À synchroniser" value="—" />
            <x-admin.stat-card label="Factures en attente" value="—" />
        </div>

        {{-- Phase 2 placeholder hint --}}
        <div class="rounded-2xl bg-azure-50 ring-1 ring-azure-200/60 p-6 text-center">
            <p class="text-sm text-azure-700 font-medium">
                Les données opérationnelles arrivent en Phase 2 — clients, passages, suivi de l'eau.
            </p>
        </div>

    </div>

    {{-- Mobile bottom navigation --}}
    <x-admin.mobile-bottom-nav />

@endsection
