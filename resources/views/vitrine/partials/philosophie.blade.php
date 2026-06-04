{{-- Notre approche — section fusionnée (Phase 8 — V12/V14 brand voice) --}}
{{-- Fusionne l'ancienne section philosophie + les engagements (D-09) --}}
{{-- Règles : pas de "interlocuteur unique", pas de "jamais"/"standard"/"call-center"/"centre d'appel"/"sous-traitance" --}}
<section class="bg-lagon-50/40 border-y border-navy-900/8">
    <div class="mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20">

        <div class="text-center mb-12">
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Notre approche</h2>
            <p class="text-center mt-2 text-lg text-ink-700 max-w-xl mx-auto">Un entretien suivi, un interlocuteur direct, un compte-rendu après chaque passage.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">

            {{-- Card 1 : Eau conforme --}}
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 flex flex-col gap-4">
                <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-azure-50 text-azure-600">
                    <x-icon.shield :size="22" />
                </span>
                <div>
                    <h3 class="font-display font-semibold text-xl text-ink-950">Eau toujours conforme</h3>
                    <p class="mt-2 text-ink-700 leading-relaxed">Vous recevez un compte-rendu après chaque passage : mesures, actions réalisées, état du bassin. Rien de caché, tout tracé.</p>
                </div>
            </div>

            {{-- Card 2 : Joignable sur WhatsApp --}}
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 flex flex-col gap-4">
                <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-[#25D366]/10 text-[#25D366]">
                    <x-icon.whatsapp :size="22" />
                </span>
                <div>
                    <h3 class="font-display font-semibold text-xl text-ink-950">Joignable sur WhatsApp</h3>
                    <p class="mt-2 text-ink-700 leading-relaxed">Vous nous contactez directement, un message suffit. Réponse rapide, sans intermédiaire.</p>
                </div>
            </div>

            {{-- Card 3 : Compte-rendu après chaque passage --}}
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 flex flex-col gap-4">
                <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-lagon-500/12 text-lagon-600">
                    <x-icon.sparkle :size="22" />
                </span>
                <div>
                    <h3 class="font-display font-semibold text-xl text-ink-950">Suivi en ligne à tout moment</h3>
                    <p class="mt-2 text-ink-700 leading-relaxed">Mesures, actions, photos quand c'est utile. Votre historique est accessible en ligne à tout moment depuis votre espace client.</p>
                </div>
            </div>

            {{-- Card 4 : Même prestataire à chaque visite --}}
            <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 flex flex-col gap-4">
                <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-sun-500/10 text-sun-500">
                    <x-icon.sun :size="22" />
                </span>
                <div>
                    <h3 class="font-display font-semibold text-xl text-ink-950">Même prestataire à chaque visite</h3>
                    <p class="mt-2 text-ink-700 leading-relaxed">Nous connaissons votre bassin, vos équipements, et les ajustements déjà faits. Pas besoin de réexpliquer à chaque fois.</p>
                </div>
            </div>

        </div>

    </div>
</section>
