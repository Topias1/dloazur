@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">SPA ET JACUZZI</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Entretien de spa<br class="hidden sm:block"> en Martinique
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                Votre spa ou jacuzzi mérite le même soin que votre piscine. Traitement de l'eau, vérification des buses, entretien du système de filtration : Dlo Azur Piscines s'occupe de tout.
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
                <span class="text-sand-50" aria-current="page">Entretien spa</span>
            </nav>
        </div>
    </section>

    {{-- Content body --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">SPA SOUS LES TROPIQUES</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Les spécificités d'un spa en climat tropical
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Un spa martiniquais est soumis à des contraintes particulières : chaleur constante, ensoleillement intense et humidité élevée accélèrent la dégradation des produits et favorisent la prolifération bactérienne. La température d'utilisation d'un spa (35–40 °C) amplifie ces effets.
        </p>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Un entretien adapté est essentiel non seulement pour le confort de baignade, mais aussi pour protéger la coque, les buses de massage et le système de chauffage contre le calcaire, les biofilms et les dépôts chimiques.
        </p>
        <!-- [À compléter — Pierre ADAM] : types de spas les plus courants en Martinique (gonflable, encastré, hors sol), fréquence recommandée -->

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">CE QUI EST INCLUS</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Notre protocole d'entretien spa
        </h2>

        <div class="space-y-6 mb-8">
            <div>
                <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Traitement et équilibre de l'eau</h3>
                <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Mesure du pH, du désinfectant (chlore ou brome), de l'alcalinité et de la dureté</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Ajustement des produits pour une eau saine et non irritante</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Traitement anti-biofilm et anti-calcaire si nécessaire</span></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Nettoyage et filtration</h3>
                <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Nettoyage de la ligne d'eau et des buses de massage</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Rinçage et nettoyage des cartouches de filtration</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Vérification de l'écumoire et du préfiltre</span></li>
                </ul>
            </div>

            <div>
                <h3 class="font-display font-semibold text-xl text-ink-950 mb-2">Contrôle des équipements</h3>
                <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Vérification du système de chauffage (résistance, régulateur de température)</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Contrôle des jets, de la pompe et du système de bullage</span></li>
                    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Inspection de la couverture isolante (évite la surchauffe et les pertes de chaleur)</span></li>
                </ul>
            </div>
        </div>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">CONSEIL</p>
        <div class="rounded-2xl bg-azure-50 ring-1 ring-azure-200 px-6 py-5 max-w-[65ch]">
            <p class="font-semibold text-azure-800">Fréquence recommandée</p>
            <p class="mt-1 text-ink-700 leading-relaxed">Un spa utilisé régulièrement nécessite un contrôle chimique hebdomadaire et une vidange complète tous les 3 mois environ. En Martinique, la chaleur peut accélérer ce rythme.</p>
            <!-- [À compléter — Pierre ADAM] : recommandation précise selon l'usage et le type de désinfectant -->
        </div>

        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">MONTAGE ET INSTALLATION</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Montage de spa hors sol ou encastré
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Vous souhaitez installer un spa ou un jacuzzi hors sol ? Dlo Azur Piscines vous accompagne de la sélection du modèle jusqu'à la mise en eau : conseil sur le terrain, vérification du nivellement, raccordement de la pompe et des accessoires, premier traitement.
        </p>
        <!-- [À compléter — Pierre ADAM] : marques de spas conseillées, types installés en Martinique -->

        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-12 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                Demander un devis
            </a>
            <a href="{{ route('services.analyse-eau') }}" class="inline-flex items-center gap-2 min-h-[44px] h-12 px-6 rounded-xl ring-1 ring-azure-300 text-azure-700 font-semibold hover:bg-azure-50 transition">
                Analyse de l'eau
            </a>
        </div>
    </section>

    {{-- CTA band --}}
    <section class="relative bg-navy-800 text-white overflow-hidden">
        <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
                Un spa propre et bien traité, c'est la détente garantie.
            </h2>
            <p class="mt-4 text-lg text-navy-200 max-w-xl mx-auto">
                Contactez Dlo Azur Piscines pour un entretien sur mesure de votre spa en Martinique.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition">
                    <x-icon.whatsapp :size="22" />
                    0696 94 00 54
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-azure-500 text-white font-bold text-lg hover:bg-azure-600 transition">
                    Devis gratuit
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
