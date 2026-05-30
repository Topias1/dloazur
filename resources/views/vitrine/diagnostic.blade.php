@extends('layouts.app')

@section('content')
{{-- /diagnostic — Landing brand S1 + wizard symptôme (Plan 05-01)
     Register : brand pour la landing hero, product pour le wizard (05-UI-SPEC)
     Tokens : @theme exclusivement (resources/css/app.css), jamais #000/#fff
     Motion : .rise (transform+opacity, 700ms ease-out-quint), prefers-reduced-motion honoré --}}

<div class="pt-28 md:pt-32">

    {{-- ═══════════════════════════════════════════════════════════
         Hero S1 — Register : BRAND — Drenched marine/azure ground
         Display title Fredoka (un seul par page), deux tuiles d'entrée ≥ h-15
    ═══════════════════════════════════════════════════════════ --}}
    <section class="bg-navy-950 pb-0 overflow-hidden">
        <div class="mx-auto max-w-content px-5 sm:px-8 pt-14 pb-12 md:pt-20 md:pb-16">

            {{-- Kicker --}}
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-4"
               style="@media (prefers-reduced-motion: no-preference) { animation: rise 700ms cubic-bezier(0.22,1,0.36,1) both; }">
                DIAGNOSTIC PISCINE GRATUIT
            </p>

            {{-- Display title — Fredoka 700, clamp 2.6-4rem, un seul sur la page --}}
            <h1 class="font-display font-bold leading-[1.05] tracking-[-0.005em] text-sand-50 max-w-3xl"
                style="font-size: clamp(2.6rem, 5vw, 4rem);
                       @media (prefers-reduced-motion: no-preference) { animation: rise 700ms 50ms cubic-bezier(0.22,1,0.36,1) both; }">
                Ta piscine te pose problème ?
            </h1>

            {{-- Promise line --}}
            <p class="mt-5 text-lg text-sand-100/75 leading-relaxed max-w-2xl"
               style="@media (prefers-reduced-motion: no-preference) { animation: rise 700ms 100ms cubic-bezier(0.22,1,0.36,1) both; }">
                Réponds à quelques questions et reçois un plan d'action adapté à ton problème — gratuit, sans compte, en quelques clics.
            </p>

            {{-- Deux tuiles d'entrée ≥ h-15 (60px) — CTA verbatim du Copywriting Contract --}}
            <div class="mt-10 flex flex-col sm:flex-row gap-4"
                 style="@media (prefers-reduced-motion: no-preference) { animation: rise 700ms 150ms cubic-bezier(0.22,1,0.36,1) both; }">

                {{-- Tuile 1 : Dépannage rapide / Symptôme --}}
                <a href="#diagnostic-wizard"
                   class="group flex items-center gap-4 min-h-[60px] h-15 px-6 rounded-2xl bg-azure-500 text-white font-bold text-base shadow-lg hover:bg-azure-600 active:bg-azure-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400 focus-visible:ring-offset-2 focus-visible:ring-offset-navy-950"
                   aria-label="Trouver mon problème — parcours symptôme"
                   x-data
                   @click.prevent="
                       const el = document.getElementById('diagnostic-wizard-root');
                       if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                       const modeBtn = el && el.querySelector('[data-mode-symptom]');
                       if (modeBtn) modeBtn.click();
                   ">
                    <span class="shrink-0 h-9 w-9 rounded-xl bg-white/15 grid place-items-center">
                        <x-icon.sparkle :size="20" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold">Trouver mon problème</span>
                        <span class="block text-sm text-azure-100/80 font-normal">Dépannage rapide · symptômes</span>
                    </span>
                    <x-icon.arrow-right :size="16" class="ml-auto opacity-60 group-hover:translate-x-0.5 transition-transform" />
                </a>

                {{-- Tuile 2 : Analyse chimique (sun accent permis pour contraste warm) --}}
                <a href="#diagnostic-wizard"
                   class="group flex items-center gap-4 min-h-[60px] h-15 px-6 rounded-2xl bg-navy-800 ring-1 ring-white/10 text-sand-50 font-bold text-base hover:bg-navy-700 active:bg-navy-800 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-azure-400 focus-visible:ring-offset-2 focus-visible:ring-offset-navy-950"
                   aria-label="Analyser mon eau — wizard chimique"
                   x-data
                   @click.prevent="
                       const el = document.getElementById('diagnostic-wizard-root');
                       if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                       const modeBtn = el && el.querySelector('[data-mode-chemistry]');
                       if (modeBtn) modeBtn.click();
                   ">
                    <span class="shrink-0 h-9 w-9 rounded-xl bg-white/10 grid place-items-center">
                        <x-icon.sun :size="20" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold">Analyser mon eau</span>
                        <span class="block text-sm text-sand-100/60 font-normal">Mesures chimiques · doses calculées</span>
                    </span>
                    <x-icon.arrow-right :size="16" class="ml-auto opacity-60 group-hover:translate-x-0.5 transition-transform" />
                </a>
            </div>

            {{-- Réassurance chips --}}
            <div class="mt-8 flex flex-wrap gap-3"
                 style="@media (prefers-reduced-motion: no-preference) { animation: rise 700ms 200ms cubic-bezier(0.22,1,0.36,1) both; }">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-sand-100/60 uppercase tracking-wide">
                    <x-icon.check :size="13" class="text-success" aria-hidden="true" />
                    Gratuit &amp; sans compte
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-sand-100/60 uppercase tracking-wide">
                    <x-icon.check :size="13" class="text-success" aria-hidden="true" />
                    Plan d'action adapté
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-sand-100/60 uppercase tracking-wide">
                    <x-icon.check :size="13" class="text-success" aria-hidden="true" />
                    Validé par Pierre (pisciniste)
                </span>
            </div>
        </div>

        {{-- Vague de transition navy → sand-50 --}}
        <div class="h-8 bg-gradient-to-b from-navy-950 to-sand-50"></div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         Wizard Livewire — surfaces S2, S4, S5 (register: product)
         Wrapper max-w-2xl, fond sand-50
    ═══════════════════════════════════════════════════════════ --}}
    <section id="diagnostic-wizard-root" class="bg-sand-50 pb-24 pt-4">
        <div class="mx-auto max-w-2xl px-5 sm:px-8">
            @if(class_exists(\App\Livewire\DiagnosticWizard::class))
                <livewire:diagnostic-wizard />
            @else
                {{-- Fallback WhatsApp si le composant n'est pas encore enregistré --}}
                <div class="rounded-2xl bg-white ring-1 ring-sand-200 p-8 text-center">
                    <p class="text-ink-700 mb-2 font-semibold">Le diagnostic est en cours de chargement.</p>
                    <p class="text-ink-500 text-sm mb-6">En attendant, contacte Pierre directement sur WhatsApp — il répond rapidement.</p>
                    <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-sm hover:brightness-95 transition">
                        <x-icon.whatsapp :size="18" />
                        Contacter Pierre sur WhatsApp
                    </a>
                </div>
            @endif
        </div>
    </section>

</div>

{{-- Rise entrance keyframes — respecte prefers-reduced-motion via @media in inline style --}}
@push('head')
<style>
@keyframes rise {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
    @keyframes rise {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
}
</style>
@endpush
@endsection
