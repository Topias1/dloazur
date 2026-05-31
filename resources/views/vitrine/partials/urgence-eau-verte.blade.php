{{-- Urgence eau verte section — D-34 --}}
{{-- Inserted between services-grid and how-it-works --}}
<section class="bg-white border-y border-azure-100">
    <div class="mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-azure-50 ring-1 ring-azure-200 px-3 py-1 text-sm font-semibold text-azure-700 mb-4">
                Intervention rapide
            </span>
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Urgence eau verte</h2>
            <p class="mt-4 text-lg leading-relaxed text-ink-700">
                Votre piscine a viré au vert ? Intervention sous 48h en Martinique. Eau claire garantie en 5 à 7 jours avec notre protocole de rattrapage intensif.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('services.eau-verte-urgence') }}" class="inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold shadow-sm hover:bg-azure-600 transition-colors">
                    Sauver ma piscine
                    <x-icon.arrow-right :size="16" />
                </a>
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-[#25D366] text-white font-semibold shadow-sm hover:brightness-95 transition">
                    <x-icon.whatsapp :size="16" />
                    WhatsApp
                </a>
            </div>
        </div>
        <div class="relative">
            <figure class="group relative aspect-square w-full overflow-hidden rounded-2xl ring-1 ring-navy-900/10">
                <img loading="lazy" decoding="async"
                    src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                    alt="Avant / après : une eau verte rattrapée en 48 heures par Dlo Azur"
                    width="1440" height="1908"
                    class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-700 ease-out-quint photo-grade">
                <div class="absolute inset-0 bg-gradient-to-t from-navy-950/85 via-navy-950/30 to-transparent"></div>
                <figcaption class="absolute bottom-0 inset-x-0 p-5 text-white">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-2.5 py-1">Avant / après</span>
                    <p class="mt-2 font-display font-semibold text-lg">D'une eau verte à une eau de baignade</p>
                </figcaption>
            </figure>

            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-3 py-1.5 shadow-sm">
                48h chrono
            </div>
        </div>
    </div>
</section>
