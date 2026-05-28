{{-- Final CTA section — 1:1 from mockups/v1/vitrine.html --}}
{{-- D-33: Diagnostic gratuit CTA also present here --}}
<section id="contact" class="relative bg-azure-600 text-white overflow-hidden">
    <div class="absolute inset-0 ripple" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-content px-5 sm:px-8 py-20 sm:py-24 text-center">
        <h2 class="font-display font-bold text-3xl sm:text-5xl text-white max-w-2xl mx-auto leading-tight">Une piscine verte&nbsp;? Une question&nbsp;? Écrivez-nous.</h2>
        <p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">Devis gratuit et sans engagement. Réponse rapide, en direct, sur WhatsApp.</p>
        <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
            <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition cursor-pointer">
                <x-icon.whatsapp :size="22" />
                0696 94 00 54
            </a>
            <a href="{{ route('contact', ['subject' => 'diagnostic-gratuit']) }}" class="inline-flex items-center gap-2 h-14 px-7 rounded-xl bg-white/15 ring-1 ring-white/30 text-white font-bold text-lg hover:bg-white/25 transition-colors cursor-pointer">
                Diagnostic gratuit
            </a>
            <a href="mailto:contact@dloazurpiscines.com" class="inline-flex items-center gap-2 h-14 px-7 rounded-xl bg-white/15 ring-1 ring-white/30 text-white font-bold text-lg hover:bg-white/25 transition-colors cursor-pointer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                Par e-mail
            </a>
        </div>
    </div>
</section>
