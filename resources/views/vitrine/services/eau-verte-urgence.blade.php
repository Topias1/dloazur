@extends('layouts.app')

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Service",
    "name": "Traitement eau verte d'urgence",
    "description": "Intervention sous 48h pour traiter une piscine à eau verte en Martinique. Protocole de rattrapage intensif, eau claire garantie en 5 à 7 jours.",
    "provider": {
        "@@type": "Plumber",
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
    {{-- Eau verte urgence dedicated page — D-34 --}}
    <div class="pt-32 pb-0">

        {{-- Hero section --}}
        <section class="mx-auto max-w-content px-5 sm:px-8 pb-16">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-azure-50 ring-1 ring-azure-200 px-3 py-1 text-sm font-semibold text-azure-700 mb-4">
                    Urgence piscine — Intervention rapide
                </span>
                <h1 class="font-display font-bold text-3xl sm:text-5xl text-ink-950 leading-tight">
                    Traitement eau verte d'urgence
                </h1>
                <p class="mt-5 text-lg sm:text-xl leading-relaxed text-ink-700">
                    Votre piscine a viré au vert à cause de la chaleur martiniquaise ? Ne paniquez pas. Notre protocole de rattrapage intensif remet l'eau en état en <strong class="text-ink-900">5 à 7 jours</strong>, avec une première intervention possible <strong class="text-ink-900">sous 48 heures</strong>.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-md hover:brightness-95 transition">
                        <x-icon.whatsapp :size="20" />
                        Demander une intervention
                    </a>
                    <a href="{{ route('contact', ['subject' => 'eau-verte-urgence']) }}" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-azure-500 text-white font-bold shadow-md hover:bg-azure-600 transition">
                        Devis gratuit
                    </a>
                </div>
            </div>
        </section>

        {{-- Photo gallery placeholder --}}
        <section class="mx-auto max-w-content px-5 sm:px-8 pb-16">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 auto-rows-[16rem]">
                <figure class="col-span-2 lg:col-span-2 rounded-3xl overflow-hidden relative group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                        alt="Avant / après traitement eau verte par Dlo Azur Piscines en Martinique"
                        class="h-full w-full object-cover group-hover:scale-[1.03] transition duration-700 photo-grade">
                    {{-- TODO: replace with real before/after photo from Pierre --}}
                    <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-navy-950/85 to-transparent p-5 text-white">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-2.5 py-1">Avant / après</span>
                        <p class="mt-2 font-display font-semibold">D'une eau verte à une eau de baignade en 48 h</p>
                    </figcaption>
                </figure>
                <figure class="rounded-3xl overflow-hidden group">
                    <img loading="lazy" decoding="async"
                        src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                        alt="Piscine traitée avec succès — eau cristalline après intervention Dlo Azur"
                        class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 photo-grade">
                    {{-- TODO: replace with real after-treatment photo from Pierre --}}
                </figure>
            </div>
        </section>

        {{-- Process section --}}
        <section class="bg-white border-y border-navy-900/8">
            <div class="mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20">
                <h2 class="font-display font-bold text-3xl text-ink-950 mb-10 max-w-xl">Comment on traite une eau verte en Martinique</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="flex flex-col gap-4">
                        <div class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-navy-800 text-white font-display font-bold text-2xl shadow-md">1</div>
                        <h3 class="font-display font-semibold text-xl text-ink-950">Diagnostic complet</h3>
                        <p class="text-ink-700 leading-relaxed">Mesure des paramètres (pH, chlore, TAC, sel), inspection de la filtration et du système de traitement. On identifie la cause du verdissement.</p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-azure-500 text-white font-display font-bold text-2xl shadow-md">2</div>
                        <h3 class="font-display font-semibold text-xl text-ink-950">Choc algicide intensif</h3>
                        <p class="text-ink-700 leading-relaxed">Traitement choc au chlore et algicide, nettoyage mécanique complet (aspirateur, brossage des parois), ajustement chimique.</p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-lagon-500 text-white font-display font-bold text-2xl shadow-md">3</div>
                        <h3 class="font-display font-semibold text-xl text-ink-950">Suivi jusqu'à l'eau claire</h3>
                        <p class="text-ink-700 leading-relaxed">Retour en 48 h pour vérifier l'évolution. On repart quand l'eau est claire. Vous recevez des photos à chaque étape.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Trust + CTA --}}
        <section class="relative bg-azure-600 text-white overflow-hidden">
            <div class="absolute inset-0 ripple" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">Votre piscine verte peut redevenir limpide.</h2>
                <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">Écrivez-nous maintenant sur WhatsApp — on vous répond rapidement et on vient évaluer sous 48 h.</p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition">
                        <x-icon.whatsapp :size="22" />
                        0696 94 00 54
                    </a>
                    <a href="{{ route('contact', ['subject' => 'eau-verte-urgence']) }}" class="inline-flex items-center gap-2 h-14 px-7 rounded-xl bg-white/15 ring-1 ring-white/30 text-white font-bold text-lg hover:bg-white/25 transition-colors">
                        Devis gratuit
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
