@extends('layouts.app')

@section('content')
    {{-- Réalisations page — SITE-03 --}}
    <div class="pt-24">
        @include('vitrine.partials.realisations-grid')

        {{-- Additional photo slots (placeholders — Pierre to provide real photos) --}}
        <section class="mx-auto max-w-content px-5 sm:px-8 pb-20 sm:pb-28">
            <h2 class="font-display font-bold text-2xl text-ink-950 mb-8">Plus de chantiers</h2>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-[14rem]">
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                        alt="Piscine cristalline après entretien hebdomadaire Dlo Azur"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                    {{-- TODO: replace with real photo from Pierre --}}
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/balai-detail.jpg') }}"
                        alt="Aspiration du fond de piscine, nettoyage complet"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                    {{-- TODO: replace with real photo from Pierre --}}
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/montage-hors-sol.jpg') }}"
                        alt="Installation complète d'une piscine hors-sol en Martinique"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                    {{-- TODO: replace with real photo from Pierre --}}
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/piscine-hors-sol.jpg') }}"
                        alt="Piscine hors-sol entretenue régulièrement, eau parfaitement équilibrée"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                    {{-- TODO: replace with real photo from Pierre --}}
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                        alt="Résultat après traitement eau verte d'urgence par Dlo Azur"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                    {{-- TODO: replace with real photo from Pierre --}}
                </figure>
                <figure class="rounded-2xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/entretien-dos-logo.jpg') }}"
                        alt="Pierre ADAM en intervention, logo Dlo Azur visible dans le dos"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                    {{-- TODO: replace with real photo from Pierre --}}
                </figure>
            </div>
        </section>

        @include('vitrine.partials.final-cta')
    </div>
@endsection
