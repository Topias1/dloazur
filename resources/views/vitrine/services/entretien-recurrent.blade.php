@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">ENTRETIEN RÉGULIER</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Entretien régulier de piscine<br class="hidden sm:block"> en Martinique
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                Une piscine impeccable toute l'année, sans y penser. Forfaits hebdomadaires, bimensuels ou à la demande, adaptés au climat tropical martiniquais.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                    Demander un devis
                </a>
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-md hover:brightness-95 transition">
                    <x-icon.whatsapp :size="20" />
                    Nous contacter par WhatsApp
                </a>
            </div>
            <nav aria-label="Fil d'Ariane" class="mt-6 flex items-center flex-wrap min-h-[44px] gap-2 text-sm text-sand-100/60">
                <a href="{{ route('home') }}" class="hover:text-sand-50 transition-colors">Accueil</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <a href="{{ route('services') }}" class="hover:text-sand-50 transition-colors">Services</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <span class="text-sand-50" aria-current="page">Entretien régulier</span>
            </nav>
        </div>
    </section>

    {{-- Content body --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">LE SECRET D'UNE EAU PARFAITE</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Pourquoi l'entretien régulier est indispensable en Martinique
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Le climat tropical martiniquais (chaleur intense, taux d'humidité élevé, brume de sable saharienne et pluies tropicales soudaines) est l'ennemi d'une piscine non entretenue. En quelques jours sans traitement, le pH dérape, les algues prolifèrent et la filtration se colmate.
        </p>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Un entretien hebdomadaire minimum est obligatoire pour maintenir une eau saine et protéger vos équipements. Dlo Azur Piscines intervient régulièrement sur votre bassin pour que vous n'ayez à vous en préoccuper.
        </p>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">CE QUI EST INCLUS</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Notre protocole de passage
        </h2>

        <div class="space-y-6 mb-8">
            <div>
                <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Nettoyage et entretien mécanique</h3>
                <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Enlèvement des impuretés en surface (feuilles, insectes, sable de la brume)</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Brossage des parois et nettoyage de la ligne d'eau</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Aspiration du fond du bassin</span></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Analyse et ajustement chimique de l'eau</h3>
                <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Mesure du pH, du taux de chlore ou de sel, de l'alcalinité et du stabilisant</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Ajustement des produits pour une eau saine (pH cible : 7,2–7,6)</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Prévention des eaux troubles et des algues, courantes sous notre climat</span></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Contrôle des équipements</h3>
                <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Vérification et nettoyage du préfiltre de la pompe</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Backwash (rétrolavage) du filtre à sable si nécessaire</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Contrôle des buses de refoulement et des skimmers</span></li>
                </ul>
            </div>
        </div>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">NOS FORMULES</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Un entretien sur mesure selon votre usage
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-6">
            Dlo Azur Piscines propose des forfaits adaptés à votre rythme de baignade et à votre piscine : hebdomadaire, bimensuel ou à la demande. Nous connaissons les défis posés par la chaleur martiniquaise, l'évaporation rapide et les fortes précipitations, et nous adaptons nos traitements en conséquence.
        </p>
        <!-- [À compléter — Pierre ADAM] : tarif plancher et formules exactes (hebdo / bimensuel / à la demande) -->
        <div class="rounded-2xl bg-azure-50 ring-1 ring-azure-200 px-6 py-5 max-w-[65ch]">
            <p class="font-semibold text-azure-800">Conseil pratique</p>
            <p class="mt-1 text-ink-700 leading-relaxed">Optez pour un contrat annuel et bénéficiez d'un bassin parfaitement entretenu toute l'année, sans y penser !</p>
        </div>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">POURQUOI NOUS CHOISIR</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Une expertise locale à votre service
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Pierre ADAM, fondateur de Dlo Azur Piscines, a passé plusieurs années en tant que directeur adjoint d'un magasin spécialisé piscines. Il connaît les spécificités du climat tropical, les défis de l'eau chaude, de la brume de sable et des pluies soudaines qui déséquilibrent la chimie du bassin.
        </p>
        <p class="text-ink-700 leading-relaxed max-w-[65ch]">
            Un service client réactif, professionnel et personnalisé. Interventions sur toute la Martinique : Fort-de-France, Le Lamentin, Schoelcher, Les Trois-Îlets et communes alentour.
        </p>

        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-12 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                Demander un devis gratuit
            </a>
            <a href="{{ route('services.eau-verte-urgence') }}" class="inline-flex items-center gap-2 min-h-[44px] h-12 px-6 rounded-xl ring-1 ring-azure-300 text-azure-700 font-semibold hover:bg-azure-50 transition">
                Urgence eau verte ?
            </a>
        </div>
    </section>

    {{-- CTA band --}}
    <section class="relative bg-azure-600 text-white overflow-hidden">
        <div class="absolute inset-0 ripple" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
                Votre piscine mérite un entretien professionnel.
            </h2>
            <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">
                Contactez-nous pour un devis gratuit et un premier passage rapide.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition">
                    <x-icon.whatsapp :size="22" />
                    0696 94 00 54
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-white/15 ring-1 ring-white/30 text-white font-bold text-lg hover:bg-white/25 transition-colors">
                    Devis gratuit
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
