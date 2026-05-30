@extends('layouts.app')

@section('content')
    {{-- Réalisations page — SITE-03 --}}
    {{-- h1 sr-only pour a11y (Plan 01-06) : le titre visuel de la section est un h2 --}}
    <h1 class="sr-only">Réalisations · Piscines traitées par Dlo Azur Piscines</h1>
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

        {{-- Case studies section (D-13: 2-3 written case studies — fact-gated, Pierre-supplied) --}}
        <section class="mx-auto max-w-content px-5 sm:px-8 py-16">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">CHANTIERS</p>
            <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] tracking-[-0.005em] text-ink-950 mb-10">
                Réalisations récentes
            </h2>

            <!-- [CHANTIER RÉEL REQUIS — Pierre ADAM doit fournir: commune, type, mesures avant/après] -->

            {{-- Case study 1 --}}
            <article class="rounded-2xl ring-1 ring-navy-900/8 bg-white p-6 sm:p-8 space-y-4 mb-6">
                <h3 class="font-display font-bold text-xl text-ink-950">
                    Piscine liner · Fort-de-France · Eau verte persistante malgré traitement hebdomadaire
                </h3>
                <p class="text-ink-700 leading-relaxed max-w-[65ch]">
                    <!-- [CHANTIER RÉEL REQUIS — Pierre ADAM doit fournir: commune confirmée, mesures pH/chlore/TAC avant] -->
                    Propriétaire à Fort-de-France confronté à une eau verte depuis plusieurs semaines malgré un entretien régulier. Diagnostic : stabilisant saturé bloquant l'efficacité du chlore. Protocole appliqué : vidange partielle (30 %), choc chlore, ajustement pH, traitement anti-algues curatif.
                    <!-- [MESURES AVANT/APRÈS REQUISES — Pierre ADAM doit fournir: pH, chlore libre, TAC mesurés] -->
                </p>
                <p class="text-sm font-semibold" style="color: var(--color-success);">Résultat : eau saine</p>
            </article>

            {{-- Case study 2 --}}
            <article class="rounded-2xl ring-1 ring-navy-900/8 bg-white p-6 sm:p-8 space-y-4 mb-6">
                <h3 class="font-display font-bold text-xl text-ink-950">
                    Spa hors sol · Schoelcher · Mousse persistante et eau laiteuse après usage intensif
                </h3>
                <p class="text-ink-700 leading-relaxed max-w-[65ch]">
                    <!-- [CHANTIER RÉEL REQUIS — Pierre ADAM doit fournir: commune confirmée, mesures avant] -->
                    Spa hors sol à Schoelcher : eau laiteuse avec mousse en surface après réception en famille. Cause identifiée : surcharge organique (cosmétiques, crème solaire) et pH trop bas favorisant la prolifération bactérienne. Protocole : choc oxygène actif, nettoyage filtre cartouche, rééquilibrage TAC et pH.
                    <!-- [MESURES AVANT/APRÈS REQUISES — Pierre ADAM doit fournir: pH, TAC, aspect visuel avant/après] -->
                </p>
                <p class="text-sm font-semibold" style="color: var(--color-success);">Résultat : eau saine</p>
            </article>

            {{-- Case study 3 --}}
            <article class="rounded-2xl ring-1 ring-navy-900/8 bg-white p-6 sm:p-8 space-y-4">
                <h3 class="font-display font-bold text-xl text-ink-950">
                    Piscine béton · Le Lamentin · Remise en état après 6 semaines d'inactivité saison humide
                </h3>
                <p class="text-ink-700 leading-relaxed max-w-[65ch]">
                    <!-- [CHANTIER RÉEL REQUIS — Pierre ADAM doit fournir: commune confirmée, mesures avant] -->
                    Bassin béton au Lamentin laissé sans entretien pendant la saison des pluies : dépôt de boue au fond, parois recouvertes d'algues noires, filtre encrassé. Intervention en 2 passages : premier passage chimie et brossage intensif, second passage aspiration fond et révision complète filtre/pompe.
                    <!-- [MESURES AVANT/APRÈS REQUISES — Pierre ADAM doit fournir: pH, chlore, TAC mesurés avant/après] -->
                </p>
                <p class="text-sm font-semibold" style="color: var(--color-success);">Résultat : eau saine</p>
            </article>
        </section>

        @include('vitrine.partials.final-cta')
    </div>
@endsection
