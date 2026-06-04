@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">ANALYSE ET TRAITEMENT</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Analyse de l'eau<br class="hidden sm:block"> de piscine en Martinique
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                pH, chlore, TAC, stabilisant : un déséquilibre suffit à rendre votre eau trouble ou agresser vos équipements. Dlo Azur Piscines mesure, ajuste et explique.
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
                <span class="text-sand-50" aria-current="page">Analyse de l'eau</span>
            </nav>
        </div>
    </section>

    {{-- Content body --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">L'EAU SOUS LES TROPIQUES</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Pourquoi la chimie de l'eau est critique en Martinique
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            En Martinique, la chaleur élevée et l'humidité accélèrent la consommation de chlore et l'évaporation de l'eau. La brume de sable saharienne, les fortes pluies tropicales et la végétation dense introduisent en permanence des impuretés organiques dans le bassin.
        </p>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Un pH mal équilibré peut réduire de 50 % l'efficacité du chlore. Un filtre mal entretenu augmente votre consommation d'énergie et réduit elle aussi l'efficacité du désinfectant. 80 % de la qualité de l'eau dépend du bon entretien du bassin et de la filtration.
        </p>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">PARAMÈTRES SURVEILLÉS</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Les 4 paramètres à mesurer chaque semaine
        </h2>

        <div class="grid sm:grid-cols-2 gap-4 mb-8">
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 p-5">
                <p class="font-display font-semibold text-lg text-ink-950 mb-1">pH</p>
                <p class="text-ink-700 leading-relaxed text-sm">Cible : 7,2 – 7,6. En dessous, l'eau est corrosive ; au-dessus, le chlore perd son efficacité et les yeux irritent.</p>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 p-5">
                <p class="font-display font-semibold text-lg text-ink-950 mb-1">Désinfectant</p>
                <p class="text-ink-700 leading-relaxed text-sm">Chlore libre ou taux de sel (électrolyseur). Dose insuffisante = algues et bactéries ; dose excessive = irritations.</p>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 p-5">
                <p class="font-display font-semibold text-lg text-ink-950 mb-1">Alcalinité (TAC)</p>
                <p class="text-ink-700 leading-relaxed text-sm">Le tampon du pH. Une TAC stable empêche les variations brutales après une pluie tropicale ou un apport d'eau neuve.</p>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 p-5">
                <p class="font-display font-semibold text-lg text-ink-950 mb-1">Stabilisant</p>
                <p class="text-ink-700 leading-relaxed text-sm">Protège le chlore de la dégradation UV solaire, crucial sous le soleil martiniquais. Trop élevé, il inhibe le chlore.</p>
            </div>
        </div>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">NOTRE MÉTHODE</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Analyse professionnelle et ajustement sur mesure
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Lors de chaque passage, Pierre mesure tous les paramètres avec des kits d'analyse professionnels. Il ajuste les doses de correcteurs (pH+, pH–, anti-algues, floculant) et adapte son intervention aux conditions météo récentes.
        </p>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Si votre piscine est équipée d'un électrolyseur au sel, Pierre vérifie également le taux de sel, le taux de production et l'entretien des cellules.
        </p>
        <!-- [À compléter — Pierre ADAM] : kits d'analyse utilisés, marques produits chimiques recommandées -->

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">CAS D'USAGE FRÉQUENTS</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Quand faire analyser son eau ?
        </h2>
        <ul class="space-y-3 text-ink-700 leading-relaxed max-w-[65ch]">
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">→</span><span>Eau trouble malgré un traitement régulier</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">→</span><span>Odeur de chlore désagréable (souvent un excès de chloramines, pas de chlore)</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">→</span><span>Irritations des yeux ou de la peau après la baignade</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">→</span><span>Après un épisode de brume de sable intense ou de forte pluie tropicale</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">→</span><span>Reprise après une période d'inactivité</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">→</span><span>Avant un événement (réception, fête)</span></li>
        </ul>

        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-12 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                Demander une analyse
            </a>
            <a href="{{ route('services.entretien-recurrent') }}" class="inline-flex items-center gap-2 min-h-[44px] h-12 px-6 rounded-xl ring-1 ring-azure-300 text-azure-700 font-semibold hover:bg-azure-50 transition">
                Voir l'entretien régulier
            </a>
        </div>
    </section>

    {{-- CTA band --}}
    <section class="relative bg-azure-600 text-white overflow-hidden">
        <div class="absolute inset-0 ripple" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
                Une eau saine protège votre santé et vos équipements.
            </h2>
            <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">
                Réservez une analyse professionnelle : intervention rapide dans notre zone d'intervention.
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
