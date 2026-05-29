{{-- Réalisations: bento éditorial, tailles variées pour le rythme. Contenu/photos conservés. --}}
<section id="realisations" class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-28">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-xl">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-600">Nos chantiers</p>
            <h2 class="mt-2 font-display font-bold text-3xl sm:text-4xl text-ink-950">Des piscines suivies, partout en Martinique.</h2>
        </div>
        <a href="{{ route('realisations') }}" class="hidden sm:inline-flex items-center gap-2 text-azure-600 font-semibold hover:text-azure-700 transition-colors">
            Voir plus de chantiers <x-icon.arrow-right :size="16" />
        </a>
    </div>

    <div class="mt-10 grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 auto-rows-[11rem] sm:auto-rows-[13rem]">
        {{-- Pièce maîtresse : avant / après --}}
        <figure class="col-span-2 row-span-2 relative rounded-3xl overflow-hidden group ring-1 ring-navy-950/5">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                alt="Avant / après : une eau verte redevenue limpide en 48 heures"
                width="1440" height="1908"
                class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950/85 via-navy-950/15 to-transparent"></div>
            <figcaption class="absolute bottom-0 inset-x-0 p-5 sm:p-6 text-white">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-2.5 py-1">Avant / après</span>
                <p class="mt-2 font-display font-semibold text-lg sm:text-xl">D'une eau verte à une eau de baignade en 48 h</p>
            </figcaption>
        </figure>

        {{-- Tuiles secondaires : scrim discret au survol pour la profondeur --}}
        <figure class="relative rounded-2xl overflow-hidden group ring-1 ring-navy-950/5">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                alt="Piscine hors-sol à Sainte-Anne, eau limpide et claire, entretenue par Dlo Azur"
                width="3840" height="2160"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            <div class="absolute inset-0 bg-navy-950/0 group-hover:bg-navy-950/15 transition-colors duration-500"></div>
        </figure>
        <figure class="relative rounded-2xl overflow-hidden group ring-1 ring-navy-950/5">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/piscine-hors-sol.jpg') }}"
                alt="Piscine hors-sol entretenue à Sainte-Anne par Dlo Azur"
                width="3840" height="2160"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            <div class="absolute inset-0 bg-navy-950/0 group-hover:bg-navy-950/15 transition-colors duration-500"></div>
        </figure>
        <figure class="col-span-2 lg:col-span-2 relative rounded-2xl overflow-hidden group ring-1 ring-navy-950/5">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/montage-hors-sol.jpg') }}"
                alt="Montage et installation d'une piscine hors-sol en Martinique"
                width="1062" height="720"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            <div class="absolute inset-0 bg-navy-950/0 group-hover:bg-navy-950/15 transition-colors duration-500"></div>
        </figure>
        <figure class="col-span-2 lg:col-span-1 relative rounded-2xl overflow-hidden group ring-1 ring-navy-950/5">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/balai-detail.jpg') }}"
                alt="Nettoyage du fond de piscine au balai épuisette par Dlo Azur"
                width="1200" height="800"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            <div class="absolute inset-0 bg-navy-950/0 group-hover:bg-navy-950/15 transition-colors duration-500"></div>
        </figure>
    </div>
</section>
