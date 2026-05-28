@extends('layouts.app')

@section('content')
    {{-- Réalisations page — SITE-03 --}}
    {{-- h1 sr-only pour a11y (Plan 01-06) : le titre visuel de la section est un h2 --}}
    <h1 class="sr-only">Réalisations — Piscines traitées par Dlo Azur Piscines</h1>
    <div class="pt-24">
        @include('vitrine.partials.realisations-grid')

        {{-- Additional photo slots --}}
        <section class="mx-auto max-w-content px-5 sm:px-8 pb-20 sm:pb-28">
            <h2 class="font-display font-bold text-2xl text-ink-950 mb-8">Plus de chantiers</h2>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-[14rem]">
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                        alt="Piscine hors-sol à Sainte-Anne, eau cristalline après entretien Dlo Azur"
                        width="3840" height="2160"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/balai-detail.jpg') }}"
                        alt="Aspiration du fond de piscine à l'épuisette, nettoyage complet par Dlo Azur"
                        width="1200" height="800"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/montage-hors-sol.jpg') }}"
                        alt="Installation complète d'une piscine hors-sol en Martinique"
                        width="1062" height="720"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/piscine-hors-sol.jpg') }}"
                        alt="Piscine hors-sol entretenue régulièrement, eau parfaitement équilibrée"
                        width="3840" height="2160"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                        alt="Avant / après : résultat traitement eau verte d'urgence par Dlo Azur"
                        width="1440" height="1908"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/entretien-dos-logo.jpg') }}"
                        alt="Pierre ADAM en intervention d'entretien, Dlo Azur Piscines"
                        width="1620" height="2160"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                </figure>
            </div>
        </section>

        @include('vitrine.partials.final-cta')
    </div>
@endsection
