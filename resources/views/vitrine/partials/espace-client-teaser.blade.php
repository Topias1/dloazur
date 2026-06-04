{{-- Espace client teaser — 1:1 from mockups/v1/vitrine.html --}}
<section class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-24">
    <div class="rounded-3xl bg-navy-50 ring-1 ring-azure-200/60 p-8 sm:p-12 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-azure-100 px-3 py-1 text-sm font-semibold text-azure-700">Espace client</span>
            <h2 class="mt-4 font-display font-bold text-3xl text-ink-950">Chaque passage, gardé en mémoire.</h2>
            <p class="mt-4 text-lg leading-relaxed text-ink-700">Après chaque intervention, retrouvez les mesures de l'eau, les actions menées et les photos, horodatées. Accessible d'un simple lien, sans mot de passe à retenir.</p>
            <a href="{{ route('contact') }}" class="mt-7 inline-flex items-center gap-2 h-12 px-5 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors cursor-pointer">
                Demander un accès
                <x-icon.arrow-right :size="18" />
            </a>
        </div>
        {{-- Mini portal preview card --}}
        <div class="rounded-2xl bg-white shadow-md ring-1 ring-navy-900/8 p-5">
            <div class="flex items-center justify-between">
                <p class="font-display font-semibold text-ink-950">Dernier passage</p>
                <span class="text-sm text-ink-400">il y a 4 jours</span>
            </div>
            <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2.5">
                    <p class="text-xs text-ink-400">pH</p>
                    <p class="font-display font-bold text-lg text-ink-950 tabular-nums">7,2</p>
                </div>
                <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2.5">
                    <p class="text-xs text-ink-400">Chlore</p>
                    <p class="font-display font-bold text-lg text-ink-950 tabular-nums">1,5</p>
                </div>
                <div class="rounded-xl bg-sand-50 ring-1 ring-sand-200 py-2.5">
                    <p class="text-xs text-ink-400">TAC</p>
                    <p class="font-display font-bold text-lg text-ink-950 tabular-nums">95</p>
                </div>
                <div class="rounded-xl bg-success/10 ring-1 ring-success/30 py-2.5">
                    <p class="text-xs text-success">Eau</p>
                    <p class="font-display font-bold text-sm text-success pt-1">OK</p>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2">
                <img loading="lazy" decoding="async"
                    src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                    alt=""
                    class="rounded-lg aspect-square object-cover">
                <img loading="lazy" decoding="async"
                    src="{{ asset('assets/brand/photos/balai-detail.jpg') }}"
                    alt=""
                    class="rounded-lg aspect-square object-cover">
                <div class="rounded-lg bg-azure-50 grid place-items-center text-azure-600 font-semibold text-sm">+3</div>
            </div>
        </div>
    </div>
</section>
