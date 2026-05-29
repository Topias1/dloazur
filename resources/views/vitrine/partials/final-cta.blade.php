{{-- Final CTA: WhatsApp instantané (gauche) + formulaire de contact inline (droite). --}}
{{-- Le bouton mailto a été retiré au profit d'un vrai formulaire (composant Livewire ContactForm). --}}
<section id="contact" class="relative bg-azure-600 text-white overflow-hidden">
    <div class="absolute inset-0 ripple" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-24">
        <div class="grid gap-10 lg:grid-cols-[1.05fr_1fr] lg:gap-16 lg:items-center">

            {{-- Colonne gauche : accroche + canal instantané --}}
            <div class="text-center lg:text-left">
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-white leading-tight">Une piscine verte&nbsp;? Une question&nbsp;? Écrivez-nous.</h2>
                <p class="mt-4 text-lg text-azure-50 max-w-md mx-auto lg:mx-0">Devis gratuit et sans engagement. Vous parlez directement à Pierre, jamais à un standard.</p>
                <div class="mt-7 flex flex-col sm:flex-row items-center lg:items-start justify-center lg:justify-start gap-x-4 gap-y-2">
                    <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold text-base shadow-lg hover:brightness-95 transition cursor-pointer">
                        <x-icon.whatsapp :size="20" />
                        0696 94 00 54
                    </a>
                    <span class="text-sm text-azure-100/90">Réponse rapide, 7j/7</span>
                </div>
            </div>

            {{-- Colonne droite : formulaire inline sur carte claire (le composant attend un fond clair) --}}
            <div class="rounded-3xl bg-sand-50 text-ink-900 p-6 sm:p-8 shadow-xl ring-1 ring-navy-950/5">
                <h3 class="font-display font-semibold text-xl text-ink-950">Ou laissez-nous un message</h3>
                <p class="mt-1 mb-5 text-sm text-ink-500">Pierre vous recontacte au plus vite.</p>
                <livewire:contact-form />
            </div>

        </div>
    </div>
</section>
