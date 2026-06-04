@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">

    {{-- Hero band --}}
    <section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
        <div class="mx-auto max-w-content px-5 sm:px-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">DÉPANNAGE PISCINE</p>
            <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
                Dépannage piscine<br class="hidden sm:block"> en Martinique
            </h1>
            <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
                Panne de pompe, filtration hors service, eau trouble incontrôlable : Pierre intervient rapidement dans notre zone d'intervention. Un appel et il évalue la situation avec vous.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-md hover:brightness-95 transition">
                    <x-icon.whatsapp :size="20" />
                    Appeler Pierre sur WhatsApp
                </a>
            </div>
            <nav aria-label="Fil d'Ariane" class="mt-6 flex items-center flex-wrap min-h-[44px] gap-2 text-sm text-sand-100/60">
                <a href="{{ route('home') }}" class="hover:text-sand-50 transition-colors">Accueil</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <a href="{{ route('services') }}" class="hover:text-sand-50 transition-colors">Services</a>
                <span aria-hidden="true" class="text-sand-100/40">›</span>
                <span class="text-sand-50" aria-current="page">Dépannage</span>
            </nav>
        </div>
    </section>

    {{-- Content body --}}
    <section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">

        <h2 class="font-display font-semibold text-2xl text-ink-950 mb-4">Types de pannes fréquentes</h2>
        <p class="text-ink-700 leading-relaxed max-w-[65ch] mb-6">
            Sous le climat martiniquais, pompes, filtres et circuits hydrauliques subissent une usure accélérée. Dlo Azur diagnostique et remet en route rapidement.
        </p>
        <ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Pompe bloquée ou bruyante (surchauffe, roulement usé)</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Filtration hors service (pression anormale, sable ou cartouche colmaté)</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Eau verte incontrôlable malgré un traitement récent</span></li>
            <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>Fuite visible sur le circuit hydraulique ou la margelle</span></li>
        </ul>

    </section>

    {{-- CTA band --}}
    <section class="relative bg-navy-800 text-white overflow-hidden">
        <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
                Une panne&nbsp;? Pierre est joignable directement.
            </h2>
            <p class="mt-4 text-lg text-navy-200 max-w-xl mx-auto">
                Pas d'intermédiaire, pas de ticket — un appel ou un message WhatsApp suffit.
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
