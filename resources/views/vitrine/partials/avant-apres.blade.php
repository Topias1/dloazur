{{-- Avant / après animé — remplace les anciennes sections « Urgence eau verte »
     et « Nos chantiers » retirées de la home (feedback Pierre). La photo composite
     montre la même piscine vert→bleu ; le balayage animé + le léger zoom dirigent
     l'œil sur la transformation. --}}
<section id="avant-apres" class="bg-white border-y border-navy-900/8">
    <div class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-24 grid lg:grid-cols-[1fr_1.05fr] gap-12 items-center">

        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-azure-50 ring-1 ring-azure-200 px-3 py-1 text-sm font-semibold text-azure-700 mb-4">
                Eau verte → eau claire
            </span>
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">D'une eau verte à une eau de baignade.</h2>
            <p class="mt-4 text-lg leading-relaxed text-ink-700">
                La même piscine, avant et après le passage de Dlo Azur. Un protocole de rattrapage, puis un suivi régulier pour qu'elle reste claire.
            </p>
            <a href="{{ route('services.eau-verte-urgence') }}" class="mt-7 inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold shadow-sm hover:bg-azure-600 transition-colors">
                Rattraper une eau verte
                <x-icon.arrow-right :size="16" />
            </a>
        </div>

        <figure class="group relative overflow-hidden rounded-3xl ring-1 ring-navy-900/10 shadow-lg aspect-[3/4] sm:aspect-square">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                alt="Avant / après : une piscine à l'eau verte redevenue limpide après le passage de Dlo Azur"
                width="1440" height="1908"
                class="absolute inset-0 h-full w-full object-cover photo-grade ba-zoom">

            {{-- Balayage lumineux animé qui longe la jointure de la photo --}}
            <span aria-hidden="true"
                  class="pointer-events-none absolute top-0 bottom-0 w-px bg-white/70 shadow-[0_0_18px_4px_oklch(1_0_0/0.55)] ba-sweep"
                  style="left: 50%;">
                <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white/90 ring-1 ring-navy-900/10 shadow-md grid place-items-center text-navy-700">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 6-6 6 6 6"/><path d="m15 6 6 6-6 6"/>
                    </svg>
                </span>
            </span>

            {{-- Étiquettes Avant / Après --}}
            <span class="absolute top-4 left-4 rounded-full bg-navy-950/70 backdrop-blur text-white text-xs font-bold px-2.5 py-1">Avant</span>
            <span class="absolute top-4 right-4 rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-2.5 py-1">Après</span>
        </figure>

    </div>
</section>
