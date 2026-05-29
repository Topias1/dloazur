{{-- Hero section, 1:1 from mockups/v1/vitrine.html --}}
{{-- D-32: tarif indicatif config-driven --}}
{{-- D-33: CTA secondaire Diagnostic gratuit --}}
<section class="relative min-h-[92vh] flex items-end overflow-hidden">
    <img
        fetchpriority="high"
        src="{{ asset('assets/brand/photos/hero-pierre-piscine.jpg') }}"
        alt="Pierre ADAM, pisciniste Dlo Azur, en intervention face à la baie de Martinique"
        width="1215" height="2160"
        class="absolute inset-0 h-full w-full object-cover object-top sm:object-[center_40%] photo-grade"
    >
    <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-900/55 to-navy-900/20"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-navy-950/70 to-transparent"></div>

    <div class="relative mx-auto max-w-content w-full px-5 sm:px-8 pb-28 pt-32">
        <div class="max-w-2xl rise">
            <h1 class="font-display font-bold text-white text-[2.6rem] leading-[1.04] sm:text-6xl sm:leading-[1.02]">
                Votre piscine,<br>claire toute l'année.
            </h1>

            <p class="mt-5 text-lg sm:text-xl text-navy-100 max-w-xl leading-relaxed">
                Entretien régulier, dépannage et analyse de l'eau. Un service à taille humaine, pour que votre piscine reste un plaisir, partout sur l'île.
            </p>

            {{-- Un primaire (devis) + un secondaire (WhatsApp) --}}
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="#contact" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-azure-500 text-white font-bold text-base shadow-md hover:bg-azure-400 transition-colors cursor-pointer">
                    Demander un devis gratuit
                    <x-icon.arrow-right :size="18" />
                </a>
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-white/10 ring-1 ring-white/25 text-white font-bold text-base backdrop-blur hover:bg-white/15 transition-colors cursor-pointer">
                    <x-icon.whatsapp :size="18" />
                    Nous écrire
                </a>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-navy-100/90">
                <span class="inline-flex items-center gap-2">
                    <x-icon.check :size="16" class="text-lagon-400" />
                    Photos à chaque passage
                </span>
                <span class="inline-flex items-center gap-2">
                    <x-icon.check :size="16" class="text-lagon-400" />
                    Réponse rapide
                </span>
                <span class="inline-flex items-center gap-2">
                    <x-icon.check :size="16" class="text-lagon-400" />
                    Suivi en ligne de vos interventions
                </span>
            </div>
        </div>
    </div>

    <svg class="absolute bottom-0 inset-x-0 w-full text-sand-50" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
        <path fill="currentColor" d="M0,60 C220,104 430,18 720,46 C1010,74 1230,104 1440,52 L1440,100 L0,100 Z"></path>
    </svg>
</section>
