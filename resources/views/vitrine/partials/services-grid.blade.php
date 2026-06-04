{{-- Services grid — 1:1 from mockups/v1/vitrine.html --}}
<section id="services" class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-28">
    <div class="max-w-2xl">
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Un entretien complet, pour une eau toujours claire.</h2>
        <p class="mt-4 text-lg leading-relaxed text-ink-700">Nettoyage, contrôle de la filtration, équilibre chimique : Dlo Azur garde votre piscine saine, équilibrée et prête pour la baignade.</p>
    </div>

    {{-- Cartes cliquables vers les pages de détail (feedback Pierre : « les onglets sont-ils cliquables ? ») --}}
    <div class="mt-12 grid lg:grid-cols-3 gap-5">
        {{-- Feature card --}}
        <a href="{{ route('services.entretien-recurrent') }}" class="lg:row-span-2 group relative overflow-hidden rounded-3xl bg-navy-800 text-white min-h-[22rem] flex flex-col justify-end focus:outline-none focus-visible:ring-2 focus-visible:ring-azure-400 focus-visible:ring-offset-2">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/entretien-dos-logo.jpg') }}"
                alt="Entretien hebdomadaire d'une piscine par Pierre ADAM, Dlo Azur"
                width="1620" height="2160"
                class="absolute inset-0 h-full w-full object-cover photo-grade opacity-65 group-hover:opacity-75 group-hover:scale-[1.03] transition duration-700 ease-out-quint">
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/40 to-transparent"></div>
            <div class="relative p-7">
                <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-azure-500 text-white shadow-md mb-4">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12c2-3 4-3 6 0s4 3 6 0 4-3 6 0"/><path d="M3 18c2-3 4-3 6 0s4 3 6 0 4-3 6 0"/></svg>
                </span>
                <h3 class="font-display font-semibold text-2xl text-white">Entretien régulier</h3>
                <p class="mt-2 text-navy-100 leading-relaxed">Passages programmés : nettoyage, contrôle de la filtration, équilibre chimique. Vous ne touchez plus à rien.</p>
                <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-white/90 group-hover:gap-2.5 transition-all">En savoir plus <x-icon.arrow-right :size="15" /></span>
            </div>
        </a>

        {{-- Supporting cards --}}
        <a href="{{ route('services.depannage') }}" class="group rounded-3xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-azure-400">
            <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-azure-50 text-azure-600 mb-4">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-6 6a2 2 0 1 0 3 3l6-6a4 4 0 0 0 5.4-5.4l-2.6 2.6-2-2 2.6-2.6Z"/></svg>
            </span>
            <h3 class="font-display font-semibold text-xl text-ink-950">Dépannage rapide</h3>
            <p class="mt-2 leading-relaxed text-ink-700">Pompe, filtration, eau trouble ou verte : un diagnostic et une remise en route sans attendre.</p>
            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-azure-600 group-hover:gap-2.5 transition-all">En savoir plus <x-icon.arrow-right :size="15" /></span>
        </a>

        <a href="{{ route('services.analyse-eau') }}" class="group rounded-3xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-azure-400">
            <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-lagon-500/12 text-lagon-600 mb-4">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6M10 3v5L5.4 17.5A2 2 0 0 0 7.2 20.5h9.6a2 2 0 0 0 1.8-3L14 8V3"/><path d="M8 14h8"/></svg>
            </span>
            <h3 class="font-display font-semibold text-xl text-ink-950">Analyse &amp; conseils</h3>
            <p class="mt-2 leading-relaxed text-ink-700">pH, chlore, TAC, sel : on mesure, on ajuste, on vous explique. Vous gardez la main si vous le souhaitez.</p>
            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-azure-600 group-hover:gap-2.5 transition-all">En savoir plus <x-icon.arrow-right :size="15" /></span>
        </a>

        <a href="{{ route('services.spa') }}" class="group rounded-3xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-azure-400">
            <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-azure-50 text-azure-600 mb-4">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 20h20M4 20V9l8-5 8 5v11M9 20v-6h6v6"/></svg>
            </span>
            <h3 class="font-display font-semibold text-xl text-ink-950">Spa, jacuzzi &amp; hors-sol</h3>
            <p class="mt-2 leading-relaxed text-ink-700">Installation, mise en route et entretien de spas, jacuzzis et piscines hors-sol, dans les règles de l'art.</p>
            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-azure-600 group-hover:gap-2.5 transition-all">En savoir plus <x-icon.arrow-right :size="15" /></span>
        </a>

        <a href="{{ route('services.eau-verte-urgence') }}" class="group rounded-3xl bg-gradient-to-br from-azure-500 to-azure-700 text-white shadow-md p-7 ripple relative overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-azure-300">
            <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-white/15 text-white mb-4">
                <x-icon.sparkle :size="22" />
            </span>
            <h3 class="font-display font-semibold text-xl text-white">Ma piscine est verte ?</h3>
            <p class="mt-2 leading-relaxed text-white/90">Diagnostic de rattrapage : on rend l'eau limpide et on vous laisse un plan clair pour qu'elle le reste.</p>
            <span class="relative mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-white group-hover:gap-2.5 transition-all">En savoir plus <x-icon.arrow-right :size="15" /></span>
        </a>
    </div>
</section>
