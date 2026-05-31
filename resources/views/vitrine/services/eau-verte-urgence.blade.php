@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "Traitement eau verte d'urgence",
    "description": "Intervention sous 48h pour traiter une piscine à eau verte en Martinique. Protocole de rattrapage intensif, eau claire garantie en 5 à 7 jours.",
    "provider": {
        "@@type": "LocalBusiness",
        "name": "Dlo Azur Piscines",
        "telephone": "+596696940054",
        "areaServed": "Martinique"
    },
    "areaServed": {
        "@@type": "AdministrativeArea",
        "name": "Martinique"
    },
    "url": "{{ url('/services/eau-verte-urgence') }}",
    "offers": {
        "@@type": "Offer",
        "availability": "https://schema.org/InStock",
        "priceCurrency": "EUR"
    }
}
</script>
@endpush

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">URGENCE EAU VERTE</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Traitement eau verte<br class="hidden sm:block"> d'urgence en Martinique
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                Votre piscine a viré au vert ? Notre protocole de rattrapage intensif remet l'eau en état en <strong class="text-sand-50">5 à 7 jours</strong>, avec une première intervention possible <strong class="text-sand-50">sous 48 heures</strong>.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-md hover:brightness-95 transition">
                    <x-icon.whatsapp :size="20" />
                    Demander une intervention
                </a>
                <a href="{{ route('contact', ['subject' => 'eau-verte-urgence']) }}"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                    Devis gratuit
                </a>
                {{-- CTA diagnostic haute-intention — Plan 05-01 (DIAG-01, Req9) --}}
                {{-- sun accent autorisé sur la tuile "eau verte" uniquement (UI-SPEC Color) --}}
                <a href="{{ route('diagnostic') }}"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-sun-500 text-navy-950 font-bold shadow-md hover:brightness-95 transition">
                    <x-icon.sparkle :size="18" />
                    Ma piscine est verte ? Diagnostic gratuit
                </a>
            </div>
            <nav aria-label="Fil d'Ariane" class="mt-6 flex items-center flex-wrap min-h-[44px] gap-2 text-sm text-sand-100/60">
                <a href="{{ route('home') }}" class="hover:text-sand-50 transition-colors">Accueil</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <a href="{{ route('services') }}" class="hover:text-sand-50 transition-colors">Services</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <span class="text-sand-50" aria-current="page">Eau verte urgence</span>
            </nav>
        </div>
    </section>

    {{-- Photo gallery --}}
    <section class="mx-auto max-w-content px-5 sm:px-8 pb-16 pt-12">
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-[16rem]">
            <figure class="col-span-2 lg:col-span-2 rounded-3xl overflow-hidden relative group">
                <img loading="eager" decoding="async"
                    src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                    alt="Avant / après traitement eau verte par Dlo Azur Piscines en Martinique"
                    width="1440" height="1908"
                    class="h-full w-full object-cover group-hover:scale-[1.03] transition duration-700 photo-grade">
                <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-navy-950/95 via-navy-950/55 to-transparent p-5 pt-10 text-white">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-2.5 py-1">Avant / après</span>
                    <p class="mt-2 font-display font-semibold">D'une eau verte à une eau de baignade en 48 h</p>
                </figcaption>
            </figure>
            <figure class="rounded-3xl overflow-hidden group">
                <img loading="lazy" decoding="async"
                    src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                    alt="Piscine traitée avec succès : eau cristalline après intervention Dlo Azur"
                    width="3840" height="2160"
                    class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 photo-grade">
            </figure>
        </div>
    </section>

    {{-- Main content --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 pb-16">

        {{-- Causes --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3">COMPRENDRE LE PROBLÈME</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Causes de l'eau verte dans une piscine martiniquaise
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            L'eau verte est causée par une prolifération d'algues microscopiques. En Martinique, le phénomène est amplifié par la combinaison de chaleur constante, de taux d'humidité élevé et d'un ensoleillement intense qui dégrade rapidement le chlore non stabilisé.
        </p>
        <ul class="space-y-3 text-ink-700 leading-relaxed max-w-[65ch] mb-6">
            <li class="flex gap-2"><span class="text-lagon-500 mt-0.5 shrink-0 font-bold">1.</span><span><strong class="text-ink-900">Manque de désinfectant</strong> : taux de chlore trop bas ou électrolyseur sous-dimensionné</span></li>
            <li class="flex gap-2"><span class="text-lagon-500 mt-0.5 shrink-0 font-bold">2.</span><span><strong class="text-ink-900">pH déséquilibré</strong> : un pH au-dessus de 7,8 peut réduire de 80 % l'efficacité du chlore</span></li>
            <li class="flex gap-2"><span class="text-lagon-500 mt-0.5 shrink-0 font-bold">3.</span><span><strong class="text-ink-900">Filtration insuffisante</strong> : filtre colmaté, heures de filtration inadaptées à la chaleur</span></li>
            <li class="flex gap-2"><span class="text-lagon-500 mt-0.5 shrink-0 font-bold">4.</span><span><strong class="text-ink-900">Apport extérieur</strong> : brume de sable saharienne, feuilles, fortes pluies tropicales qui diluent les produits</span></li>
            <li class="flex gap-2"><span class="text-lagon-500 mt-0.5 shrink-0 font-bold">5.</span><span><strong class="text-ink-900">Période d'inactivité</strong> : quelques semaines sans traitement sous notre soleil suffisent</span></li>
        </ul>

        {{-- DIY checklist --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">AVANT D'APPELER LE PRO</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Checklist DIY · ce que vous pouvez vérifier
        </h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            Si l'eau vient juste de commencer à verdir, vous pouvez tenter ces gestes de première urgence avant notre passage :
        </p>
        <ul class="space-y-3 text-ink-700 leading-relaxed max-w-[65ch] mb-4">
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Vérifiez que la pompe tourne bien et que le filtre n'est pas colmaté (manomètre)</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Augmentez la durée de filtration à 12–16 h/jour (en pleine chaleur)</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Effectuez un backwash (rétrolavage) du filtre à sable</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Testez le pH (bandelette ou kit) : il doit être entre 7,2 et 7,6</span></li>
        </ul>
        <p class="text-ink-700 leading-relaxed max-w-[65ch]">
            <strong class="text-ink-900">Quand appeler le pro :</strong> si l'eau est verte ou opaque, si elle dégage une odeur, si des dépôts verdâtres apparaissent sur les parois, ou si vos gestes DIY n'ont pas d'effet après 24 h. Un traitement choc mal dosé peut aggraver la situation.
        </p>

        {{-- Protocol 5 steps --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">NOTRE MÉTHODE</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-8">
            Protocole 5 étapes · de l'eau verte à l'eau cristalline
        </h2>
        <div class="space-y-6">
            @foreach([
                ['step' => '1', 'color' => 'bg-navy-800', 'title' => 'Diagnostic complet', 'body' => "Mesure de tous les paramètres (pH, chlore libre/total, TAC, sel, stabilisant), inspection de la pompe, du filtre et du système de traitement. On identifie la cause exacte du verdissement avant tout traitement."],
                ['step' => '2', 'color' => 'bg-azure-500', 'title' => 'Ajustement du pH', 'body' => "Correction du pH à 7,0–7,2 avant tout choc. Un pH correctement positionné multiplie l'efficacité du chlore par 3 à 4 et est la condition sine qua non d'un traitement réussi."],
                ['step' => '3', 'color' => 'bg-lagon-500', 'title' => 'Choc algicide intensif', 'body' => "Traitement choc au chlore granulé haute dose + algicide curatif. Brossage mécanique complet des parois, du fond et de la ligne d'eau pour décoller les algues fixées."],
                ['step' => '4', 'color' => 'bg-navy-600', 'title' => 'Filtration continue', 'body' => "La filtration tourne en continu (24h/24) pendant le traitement. On vérifie et rétrolave le filtre autant de fois que nécessaire pour évacuer les algues mortes."],
                ['step' => '5', 'color' => 'bg-azure-600', 'title' => 'Suivi et validation', 'body' => "Retour sous 48 h pour vérifier l'évolution. On ajuste le traitement si nécessaire et on valide les paramètres finaux avant de vous remettre une eau de baignade certifiée."],
            ] as $s)
            <div class="flex gap-5">
                <div class="inline-grid h-14 w-14 shrink-0 place-items-center rounded-2xl {{ $s['color'] }} text-white font-display font-bold text-2xl shadow-md">{{ $s['step'] }}</div>
                <div>
                    <h3 class="font-display font-semibold text-xl text-ink-950">{{ $s['title'] }}</h3>
                    <p class="mt-1 text-ink-700 leading-relaxed max-w-[55ch]">{{ $s['body'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Before/After parameters --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">RÉSULTATS TYPES</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            Paramètres avant / après traitement
        </h2>
        <div class="overflow-x-auto rounded-2xl ring-1 ring-navy-900/10 mb-4">
            <table class="w-full text-sm">
                <thead class="bg-navy-900 text-sand-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Paramètre</th>
                        <th class="px-4 py-3 text-left font-semibold text-lagon-400">Avant (eau verte)</th>
                        <th class="px-4 py-3 text-left font-semibold text-azure-400">Après (eau saine)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy-900/8 bg-white">
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-950">pH</td>
                        <td class="px-4 py-3 text-ink-600">8,2 – 8,5 (trop élevé)</td>
                        <td class="px-4 py-3 text-azure-700 font-semibold">7,2 – 7,6</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-950">Chlore libre</td>
                        <td class="px-4 py-3 text-ink-600">0 – 0,2 mg/L (insuffisant)</td>
                        <td class="px-4 py-3 text-azure-700 font-semibold">1 – 3 mg/L</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-950">Alcalinité (TAC)</td>
                        <td class="px-4 py-3 text-ink-600">Variable / non maîtrisé</td>
                        <td class="px-4 py-3 text-azure-700 font-semibold">80 – 120 mg/L</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-950">Aspect visuel</td>
                        <td class="px-4 py-3 text-ink-600">Vert opaque, fond invisible</td>
                        <td class="px-4 py-3 text-azure-700 font-semibold">Limpide, fond visible</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- [À compléter — Pierre ADAM] : chiffres tirés d'un chantier réel (cas confirmé) -->

        {{-- FAQ accordion --}}
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600 mb-3 mt-12">QUESTIONS FRÉQUENTES</p>
        <h2 class="font-display font-bold text-[clamp(1.875rem,3vw,2.5rem)] leading-[1.1] text-ink-950 mb-6">
            FAQ eau verte
        </h2>
        <div class="space-y-3">
            @foreach([
                [
                    'q' => 'Pourquoi mon eau est-elle verte malgré le chlore ?',
                    'a' => "Le chlore perd jusqu'à 80 % de son efficacité si le pH est au-dessus de 7,8. La brume de sable et les pluies tropicales peuvent aussi consommer le chlore en quelques heures. Si votre filtre est colmaté, les algues mortes restent en suspension et favorisent la reprise. Un diagnostic complet est nécessaire pour identifier la cause réelle."
                ],
                [
                    'q' => 'Combien de temps faut-il pour retrouver une eau claire ?',
                    'a' => "Avec notre protocole, le verdissement commence à reculer dès le lendemain du choc. L'eau est généralement translucide au bout de 3 à 5 jours, et baignable entre 5 et 7 jours selon l'intensité du verdissement et la performance de la filtration. Nous repassons pour valider avant de vous laisser plonger."
                ],
                [
                    'q' => "Peut-on se baigner dans une eau légèrement verte ?",
                    'a' => "Non. Une eau verte, même légèrement teintée, indique la présence d'algues et d'un désinfectant insuffisant. Les bactéries prolifèrent dans les mêmes conditions que les algues. Attendez le feu vert de votre pisciniste après vérification des paramètres (chlore libre, pH, absence de bactéries)."
                ],
            ] as $faq)
            <div x-data="{ open: false }" class="rounded-2xl ring-1 ring-sand-200 bg-white">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex w-full items-center justify-between min-h-[44px] px-5 py-3.5 text-left text-ink-900 font-semibold"
                    :aria-expanded="open.toString()"
                >
                    <span>{{ $faq['q'] }}</span>
                    <svg
                        class="h-4 w-4 text-azure-500 transition-transform duration-200 shrink-0 ml-3"
                        :class="open ? 'rotate-180' : ''"
                        style="@media (prefers-reduced-motion: reduce) { transition: none; }"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                    >
                        <path d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-5 pb-4 text-ink-700 leading-relaxed max-w-[65ch]">
                    {{ $faq['a'] }}
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Trust + CTA --}}
    <section class="relative bg-azure-600 text-white overflow-hidden">
        <div class="absolute inset-0 ripple" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
                Votre piscine verte peut redevenir limpide.
            </h2>
            <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">
                Écrivez-nous maintenant sur WhatsApp : on vous répond rapidement et on vient évaluer sous 48 h.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition">
                    <x-icon.whatsapp :size="22" />
                    0696 94 00 54
                </a>
                <a href="{{ route('contact', ['subject' => 'eau-verte-urgence']) }}"
                   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-white/15 ring-1 ring-white/30 text-white font-bold text-lg hover:bg-white/25 transition-colors">
                    Devis gratuit
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
