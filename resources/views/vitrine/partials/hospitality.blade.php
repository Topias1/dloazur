{{-- Hospitality / B2B section — 1:1 from mockups/v1/vitrine.html --}}
<section id="hospitality" class="relative bg-navy-900 text-white overflow-hidden">
    <div class="absolute inset-0 ripple opacity-60" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-28 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/15 px-3 py-1 text-sm font-semibold text-lagon-300">Pour les professionnels</span>
            <h2 class="mt-5 font-display font-bold text-3xl sm:text-4xl text-white">Agences, conciergeries &amp; locations saisonnières.</h2>
            <p class="mt-4 text-lg leading-relaxed text-navy-100 max-w-xl">Une piscine impeccable fait partie de l'expérience de vos voyageurs. Dlo Azur prend en charge le suivi de vos villas, avec un compte-rendu photo après chaque passage, pour que vous gardiez l'esprit tranquille.</p>
            <ul class="mt-7 space-y-3.5">
                <li class="flex gap-3">
                    <span class="text-lagon-400 mt-0.5 shrink-0"><x-icon.check :size="20" /></span>
                    <span class="text-navy-50">Interventions planifiées entre deux séjours</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-lagon-400 mt-0.5 shrink-0"><x-icon.check :size="20" /></span>
                    <span class="text-navy-50">Preuve photo horodatée pour vos propriétaires</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-lagon-400 mt-0.5 shrink-0"><x-icon.check :size="20" /></span>
                    <span class="text-navy-50">Facturation centralisée, un seul interlocuteur</span>
                </li>
            </ul>
            <a href="#contact" class="mt-8 inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-white text-navy-900 font-bold shadow-md hover:bg-navy-50 transition-colors cursor-pointer">
                Devenir partenaire
                <x-icon.arrow-right :size="18" />
            </a>
        </div>
        <div class="relative">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/villa-hospitality.jpg') }}"
                alt="Piscine de villa en Martinique entretenue par Dlo Azur : suivi régulier et compte-rendu photo"
                width="2880" height="2160"
                class="rounded-3xl shadow-lg w-full object-cover aspect-[4/3] photo-grade">
            <div class="absolute -bottom-5 -left-3 sm:left-6 rounded-2xl bg-white text-ink-900 shadow-lg px-5 py-4 max-w-[15rem]">
                <p class="font-display font-bold text-2xl text-azure-600">12 villas</p>
                <p class="text-sm text-ink-500 leading-snug">suivies en continu pour des conciergeries du sud de l'île</p>
            </div>
        </div>
    </div>
</section>
