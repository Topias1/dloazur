@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">SCHOELCHER</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Pisciniste à Schoelcher&nbsp;· Dlo Azur Piscines
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                Entretien, analyse d'eau et dépannage piscine pour les propriétés de Schoelcher, commune côtière au bord de la mer des Caraïbes.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                    Demander un devis gratuit
                </a>
                <a href="tel:+596696940054"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-navy-900 ring-1 ring-sand-50/20 text-white font-bold shadow-md hover:bg-navy-800 transition">
                    Appeler Pierre
                </a>
            </div>
            <nav aria-label="Fil d'Ariane" class="mt-6 flex items-center flex-wrap min-h-[44px] gap-2 text-sm text-sand-100/60">
                <a href="{{ route('home') }}" class="hover:text-sand-50 transition-colors">Accueil</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <span class="text-sand-50" aria-current="page">Schoelcher</span>
            </nav>
        </div>
    </section>

    {{-- Main content --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">ZONE D'INTERVENTION</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Votre pisciniste de confiance à Schoelcher
        </h2>

        <!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Schoelcher] -->
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-6">
            À Schoelcher, <!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Schoelcher] --> Pierre intervient sur les piscines de maisons individuelles, de villas en bord de mer et de propriétés situées sur les hauteurs de la commune.
        </p>

        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Schoelcher est une commune côtière directement exposée aux embruns marins. La proximité de la mer des Caraïbes influence la chimie de l'eau des piscines environnantes : les aérosols salins peuvent déposer des ions chlorure supplémentaires dans le bassin, ce qui modifie les équilibres et nécessite une attention particulière lors de l'analyse chimique.
        </p>

        <p class="text-ink-700 leading-relaxed max-w-[65ch]">
            Avec la présence de nombreuses résidences estudiantines et d'habitations récentes proches du campus universitaire, Schoelcher compte une population variée de propriétaires de piscines qui ont besoin d'un pisciniste fiable et ponctuel. Dlo Azur Piscines répond à cette demande avec des forfaits adaptés à chaque profil.
        </p>

        {{-- Service links — city → service direction --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">NOS PRESTATIONS</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Services disponibles à Schoelcher
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
                Une eau cristalline pour votre piscine à Schoelcher.
            </h2>
            <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">
                Appelez Pierre ou demandez un devis gratuit : réponse rapide, intervention sur mesure.
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
