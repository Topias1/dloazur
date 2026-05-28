{{-- Pierre / À propos section — 1:1 from mockups/v1/vitrine.html --}}
<section id="pierre" class="bg-white border-y border-navy-900/8">
    <div class="mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-24 grid lg:grid-cols-[0.85fr_1fr] gap-12 items-center">
        <div class="relative">
            <img loading="lazy" decoding="async"
                src="{{ asset('assets/brand/photos/pierre-portrait.jpg') }}"
                alt="Pierre ADAM, fondateur de Dlo Azur Piscines, pisciniste en Martinique"
                class="rounded-3xl shadow-lg w-full object-cover aspect-[4/5] photo-grade">
            {{-- TODO: replace placeholder above with real portrait from Pierre --}}
            <span class="absolute -top-4 -right-2 sm:right-6 text-azure-500 drop-shadow" aria-hidden="true">
                <svg width="64" height="78" viewBox="0 0 28 34" fill="none"><path d="M14 1.5C14 1.5 3.5 13.5 3.5 22a10.5 10.5 0 0 0 21 0C24.5 13.5 14 1.5 14 1.5Z" fill="currentColor"/><path d="M8.4 21.4c1.6-2.3 3.8-2.3 5.6 0s4 2.3 5.6 0" stroke="oklch(0.987 0.005 85)" stroke-width="1.7" stroke-linecap="round"/></svg>
            </span>
        </div>
        <div>
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Dlo, c'est l'eau. Azur, c'est sa couleur.</h2>
            <p class="mt-5 text-lg leading-relaxed text-ink-700">
                <span class="font-semibold text-ink-900">Pierre ADAM</span> entretient les piscines de Martinique comme s'il s'agissait des siennes. Un seul interlocuteur, qui connaît votre bassin, son volume, sa filtration, et qui passe quand il le faut.
            </p>
            <p class="mt-4 leading-relaxed text-ink-700">Pas de centre d'appel, pas de sous-traitance : vous échangez directement avec celui qui plonge l'épuisette. C'est ça, un service à taille humaine.</p>
            <div class="mt-8 flex items-center gap-6 max-w-lg">
                <p class="font-display font-semibold text-2xl text-ink-950 leading-snug flex-1">Une dizaine de clients à l'année,<br>un seul interlocuteur, toujours.</p>
                <img loading="lazy" decoding="async"
                    src="{{ asset('assets/brand/photos/entretien-dos-logo.jpg') }}"
                    alt="Pierre en intervention, logo Dlo Azur dans le dos"
                    class="w-32 h-32 sm:w-36 sm:h-36 rounded-2xl object-cover shadow-sm shrink-0 photo-grade">
                {{-- TODO: replace with real action photo from Pierre --}}
            </div>
        </div>
    </div>
</section>
