{{-- Réalisations grid — 1:1 from mockups/v1/vitrine.html --}}
<section id="realisations" class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-28">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-xl">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Des piscines suivies, partout en Martinique.</h2>
        </div>
        <a href="{{ route('realisations') }}" class="hidden sm:inline-flex items-center gap-2 text-azure-600 font-semibold hover:text-azure-700 transition-colors">
            Voir plus de chantiers <x-icon.arrow-right :size="16" />
        </a>
    </div>

    <div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-4 auto-rows-[12rem]">
        <figure class="col-span-2 row-span-2 relative rounded-3xl overflow-hidden group">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                alt="Avant / après : une eau verte redevenue limpide en 48 heures"
                class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
            {{-- TODO: replace with real before/after photo from Pierre --}}
            <figcaption class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-navy-950/85 to-transparent p-5 text-white">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-2.5 py-1">Avant / après</span>
                <p class="mt-2 font-display font-semibold text-lg">D'une eau verte à une eau de baignade en 48 h</p>
            </figcaption>
        </figure>

        <figure class="rounded-2xl overflow-hidden group">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                alt="Piscine entretenue par Dlo Azur, eau limpide et claire"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            {{-- TODO: replace with real photo from Pierre --}}
        </figure>
        <figure class="rounded-2xl overflow-hidden group">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/piscine-hors-sol.jpg') }}"
                alt="Piscine hors-sol entretenue à Sainte-Anne par Dlo Azur"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            {{-- TODO: replace with real photo from Pierre --}}
        </figure>
        <figure class="rounded-2xl overflow-hidden group">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/montage-hors-sol.jpg') }}"
                alt="Montage et installation d'une piscine hors-sol en Martinique"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            {{-- TODO: replace with real photo from Pierre --}}
        </figure>
        <figure class="rounded-2xl overflow-hidden group">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/balai-detail.jpg') }}"
                alt="Nettoyage du fond de piscine au balai aspirateur par Dlo Azur"
                class="h-full w-full object-cover group-hover:scale-[1.05] transition duration-700 ease-out-quint photo-grade">
            {{-- TODO: replace with real photo from Pierre --}}
        </figure>
    </div>
</section>
