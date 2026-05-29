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
            <div x-data="{
                    pos: 50,
                    set(clientX) {
                        const r = this.$refs.frame.getBoundingClientRect();
                        this.pos = Math.min(100, Math.max(0, ((clientX - r.left) / r.width) * 100));
                    },
                    drag(e) {
                        e.preventDefault();
                        this.set(e.clientX);
                        const move = (ev) => this.set(ev.clientX);
                        const up = () => {
                            window.removeEventListener('pointermove', move);
                            window.removeEventListener('pointerup', up);
                        };
                        window.addEventListener('pointermove', move);
                        window.addEventListener('pointerup', up);
                    },
                    key(e) {
                        const step = e.shiftKey ? 10 : 2;
                        if (e.key === 'ArrowLeft')  { this.pos = Math.max(0, this.pos - step); e.preventDefault(); }
                        if (e.key === 'ArrowRight') { this.pos = Math.min(100, this.pos + step); e.preventDefault(); }
                        if (e.key === 'Home') { this.pos = 0; e.preventDefault(); }
                        if (e.key === 'End')  { this.pos = 100; e.preventDefault(); }
                    }
                 }"
                 x-ref="frame"
                 @pointerdown="drag($event)"
                 role="group"
                 aria-label="Comparateur avant / après : une eau verte rattrapée en 48 heures"
                 class="group relative aspect-square w-full cursor-ew-resize touch-none select-none overflow-hidden rounded-2xl ring-1 ring-navy-900/10">

                {{-- Après : couche de base, visible à droite de la poignée --}}
                <img loading="lazy" decoding="async" draggable="false"
                    src="{{ asset('assets/brand/photos/piscine-propre.jpg') }}"
                    alt="Piscine eau claire et limpide après traitement d'urgence Dlo Azur"
                    width="3840" height="2160"
                    class="pointer-events-none absolute inset-0 h-full w-full object-cover photo-grade">

                {{-- Avant : couche révélée de la gauche jusqu'à la poignée --}}
                <img loading="lazy" decoding="async" draggable="false"
                    src="{{ asset('assets/brand/photos/avant-apres.jpg') }}"
                    alt="Piscine avec eau verte avant traitement par Dlo Azur"
                    width="1440" height="1908"
                    :style="`clip-path: inset(0 ${100 - pos}% 0 0)`"
                    class="pointer-events-none absolute inset-0 h-full w-full object-cover photo-grade">

                <span class="pointer-events-none absolute top-3 left-3 rounded-full bg-navy-950/80 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-sm">Avant</span>
                <span class="pointer-events-none absolute bottom-3 right-3 rounded-full bg-azure-600/90 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-sm">Après</span>

                {{-- Ligne de séparation + poignée --}}
                <div class="pointer-events-none absolute inset-y-0 w-px bg-white/90"
                     :style="`left: ${pos}%; transform: translateX(-50%)`">
                    <button type="button"
                            @pointerdown.stop="drag($event)"
                            @keydown="key($event)"
                            role="slider"
                            aria-label="Glisser pour comparer l'avant et l'après"
                            aria-valuemin="0" aria-valuemax="100"
                            :aria-valuenow="Math.round(pos)"
                            :aria-valuetext="`${Math.round(pos)} % avant`"
                            class="pointer-events-auto absolute top-1/2 left-1/2 grid size-11 -translate-x-1/2 -translate-y-1/2 cursor-ew-resize place-items-center rounded-full bg-white text-navy-900 shadow-md ring-1 ring-navy-900/10 transition-shadow group-hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-azure-500">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m9 7-5 5 5 5" /><path d="m15 7 5 5-5 5" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-sun-500 text-navy-950 text-xs font-bold px-3 py-1.5 shadow-sm">
                48h chrono
            </div>
        </div>
    </div>
</section>
