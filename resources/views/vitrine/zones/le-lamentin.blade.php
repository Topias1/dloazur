@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">LE LAMENTIN</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Pisciniste au Lamentin&nbsp;· Dlo Azur Piscines
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                Entretien et dépannage de piscines pour les particuliers et résidences du Lamentin. Eau saine, équipements vérifiés, zéro contrainte.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                    Demander un devis gratuit
                </a>
                <a href="tel:+596696940054"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-navy-900 ring-1 ring-sand-50/20 text-white font-bold shadow-md hover:bg-navy-800 transition">
                    Nous appeler
                </a>
            </div>
            <nav aria-label="Fil d'Ariane" class="mt-6 flex items-center flex-wrap min-h-[44px] gap-2 text-sm text-sand-100/60">
                <a href="{{ route('home') }}" class="hover:text-sand-50 transition-colors">Accueil</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <span class="text-sand-50" aria-current="page">Le Lamentin</span>
            </nav>
        </div>
    </section>

    {{-- Main content --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">ZONE D'INTERVENTION</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Votre pisciniste de confiance au Lamentin
        </h2>

        <!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Le Lamentin] -->
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-6">
            Au Lamentin, <!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Le Lamentin] --> Pierre intervient sur les piscines de particuliers, de lotissements résidentiels et de propriétés situées aux abords de la zone aéroportuaire.
        </p>

        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Le Lamentin est la commune la plus peuplée de la Martinique, avec une forte densité de pavillons et de résidences disposant de piscines privatives. La commune combine zones résidentielles calmes et proximité d'axes routiers animés, un contexte où un entretien régulier évite les mauvaises surprises lors des grandes occasions (fêtes, locations saisonnières, retour de vacances).
        </p>

        <p class="text-ink-700 leading-relaxed max-w-[65ch]">
            En saison humide, les pluies abondantes qui traversent le Lamentin diluent les produits de traitement et déstabilisent l'équilibre chimique de l'eau. Un suivi professionnel bimensuel permet de maintenir une eau de baignade saine en toute circonstance.
        </p>

        {{-- Service links — city → service direction --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">NOS PRESTATIONS</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Services disponibles au Lamentin
        </h2>
        <ul class="space-y-4 mt-4">
            <li>
                <a href="{{ route('services.entretien-recurrent') }}" class="group flex items-start gap-4 rounded-2xl ring-1 ring-navy-900/8 bg-white p-5 hover:ring-azure-400 transition">
                    <span class="mt-0.5 text-azure-500 font-bold text-lg shrink-0">→</span>
                    <div>
                        <span class="font-semibold text-ink-950 group-hover:text-azure-600 transition-colors">Entretien régulier de piscine</span>
                        <p class="text-sm text-ink-600 mt-0.5">Forfaits hebdomadaires, bimensuels ou à la demande.</p>
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('services.eau-verte-urgence') }}" class="group flex items-start gap-4 rounded-2xl ring-1 ring-navy-900/8 bg-white p-5 hover:ring-azure-400 transition">
                    <span class="mt-0.5 text-azure-500 font-bold text-lg shrink-0">→</span>
                    <div>
                        <span class="font-semibold text-ink-950 group-hover:text-azure-600 transition-colors">Traitement eau verte d'urgence</span>
                        <p class="text-sm text-ink-600 mt-0.5">Intervention sous 48 h, eau claire en 5 à 7 jours.</p>
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('services.analyse-eau') }}" class="group flex items-start gap-4 rounded-2xl ring-1 ring-navy-900/8 bg-white p-5 hover:ring-azure-400 transition">
                    <span class="mt-0.5 text-azure-500 font-bold text-lg shrink-0">→</span>
                    <div>
                        <span class="font-semibold text-ink-950 group-hover:text-azure-600 transition-colors">Analyse de l'eau</span>
                        <p class="text-sm text-ink-600 mt-0.5">pH, chlore, TAC, sel : ajustement professionnel sur place.</p>
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('services.spa') }}" class="group flex items-start gap-4 rounded-2xl ring-1 ring-navy-900/8 bg-white p-5 hover:ring-azure-400 transition">
                    <span class="mt-0.5 text-azure-500 font-bold text-lg shrink-0">→</span>
                    <div>
                        <span class="font-semibold text-ink-950 group-hover:text-azure-600 transition-colors">Entretien spa et jacuzzi</span>
                        <p class="text-sm text-ink-600 mt-0.5">Traitement de l'eau et maintenance équipements.</p>
                    </div>
                </a>
            </li>
        </ul>
    </section>

    {{-- CTA band --}}
    <section class="relative bg-azure-600 text-white overflow-hidden">
        <div class="absolute inset-0 ripple" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
                Votre piscine au Lamentin, entretenue sans effort.
            </h2>
            <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">
                Appelez-nous ou demandez un devis gratuit : réponse rapide, intervention sur mesure.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="tel:+596696940054"
                   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-white text-azure-700 font-bold text-lg shadow-lg hover:bg-azure-50 transition">
                    0696 94 00 54
                </a>
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-white/15 ring-1 ring-white/30 text-white font-bold text-lg hover:bg-white/25 transition-colors">
                    Devis gratuit
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
