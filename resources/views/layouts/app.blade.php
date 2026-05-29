<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo.meta
        :title="$title ?? null"
        :description="$description ?? null"
        :image="$ogImage ?? $image ?? null"
        :canonical="$canonical ?? null"
    />

    {{-- JSON-LD structured data (home page only — LocalBusiness Plumber schema) --}}
    @if(isset($jsonLd) && $jsonLd)
        {!! $jsonLd !!}
    @endif

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-sand-50 text-ink-700 antialiased">

    {{-- a11y: skip to content for keyboard users --}}
    <a
        href="#top"
        class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:rounded-xl focus:bg-azure-500 focus:text-white focus:font-bold"
    >
        Aller au contenu principal
    </a>

    {{-- ===== Top bar (transposed from mockups/v1/vitrine.html topbar) ===== --}}
    <header class="fixed top-0 inset-x-0 z-40" x-data="{ open: false }" @keydown.escape.window="open = false">
        <div class="mx-auto max-w-content px-4 sm:px-6 mt-3">
            <nav class="flex items-center justify-between gap-4 rounded-2xl bg-sand-50/85 backdrop-blur-md ring-1 ring-navy-900/10 shadow-sm px-4 sm:px-5 h-15 py-2.5">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    <span class="text-azure-500">
                        <x-icon.drop :size="30" />
                    </span>
                    <span class="leading-none">
                        <span class="block font-display font-bold text-ink-950 text-lg tracking-tight">Dlo Azur</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-azure-600">Piscines</span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-7 text-sm font-semibold text-ink-700">
                    <a href="{{ route('services') }}" class="hover:text-azure-600 transition-colors">Services</a>
                    <a href="{{ route('home') }}#hospitality" class="hover:text-azure-600 transition-colors">Hospitalité</a>
                    <a href="{{ route('realisations') }}" class="hover:text-azure-600 transition-colors">Réalisations</a>
                    <a href="{{ route('home') }}#pierre" class="hover:text-azure-600 transition-colors">Le pisciniste</a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('contact') }}" class="hidden sm:inline-flex items-center h-11 px-3.5 rounded-xl text-sm font-semibold text-navy-700 hover:bg-navy-900/5 transition-colors">Espace client</a>
                    <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-11 px-4 rounded-xl bg-[#25D366] text-white text-sm font-bold shadow-sm hover:brightness-95 transition cursor-pointer">
                        <x-icon.whatsapp :size="17" />
                        <span class="hidden xs:inline sm:inline">WhatsApp</span>
                    </a>
                    {{-- Mobile hamburger --}}
                    <button
                        type="button"
                        class="md:hidden grid place-items-center h-11 w-11 -mr-1 rounded-xl text-ink-900 hover:bg-navy-900/5 transition-colors"
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-controls="mobile-menu"
                        @click="open = !open"
                    >
                        <span class="sr-only">Menu</span>
                        <svg x-show="!open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg x-show="open" x-cloak width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
            </nav>

            {{-- Mobile menu sheet --}}
            <nav
                id="mobile-menu"
                aria-label="Menu principal"
                class="md:hidden mt-2 origin-top rounded-2xl bg-sand-50/95 backdrop-blur-md ring-1 ring-navy-900/10 shadow-md overflow-hidden"
                x-show="open"
                x-cloak
                x-transition.origin.top
                @click.outside="open = false"
            >
                <ul class="flex flex-col p-2 text-base font-semibold text-ink-800">
                    <li><a href="{{ route('services') }}" @click="open = false" class="flex items-center h-12 px-4 rounded-xl hover:bg-navy-900/5 hover:text-azure-700 transition-colors">Services</a></li>
                    <li><a href="{{ route('home') }}#hospitality" @click="open = false" class="flex items-center h-12 px-4 rounded-xl hover:bg-navy-900/5 hover:text-azure-700 transition-colors">Hospitalité</a></li>
                    <li><a href="{{ route('realisations') }}" @click="open = false" class="flex items-center h-12 px-4 rounded-xl hover:bg-navy-900/5 hover:text-azure-700 transition-colors">Réalisations</a></li>
                    <li><a href="{{ route('home') }}#pierre" @click="open = false" class="flex items-center h-12 px-4 rounded-xl hover:bg-navy-900/5 hover:text-azure-700 transition-colors">Le pisciniste</a></li>
                    <li class="mt-1 pt-1 border-t border-navy-900/10"><a href="{{ route('contact') }}" @click="open = false" class="flex items-center h-12 px-4 rounded-xl bg-azure-500 text-white">Espace client</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="top">
        @yield('content')
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="bg-navy-950 text-navy-100">
        <div class="mx-auto max-w-content px-5 sm:px-8 py-14 grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_auto]">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="text-azure-500">
                        <x-icon.drop :size="30" />
                    </span>
                    <span class="leading-none">
                        <span class="block font-display font-bold text-white text-lg">Dlo Azur</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-azure-400">Piscines</span>
                    </span>
                </div>
                <p class="mt-4 text-sm leading-relaxed max-w-xs text-navy-200">
                    Entretien, dépannage et analyse de l'eau de piscines en Martinique. Un service personnalisé, par Pierre ADAM.
                </p>
                <div class="mt-5 flex items-center gap-2.5">
                    <a href="#" aria-label="Facebook" class="grid h-10 w-10 place-items-center rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3l.5-3.5H14V3.7c0-1 .3-1.7 1.8-1.7H17V-.1A24 24 0 0 0 14.6-.2C12 -.2 10.3 1.4 10.3 4.3v1.2H7.3V9h3v12H14V9Z" transform="translate(0 2)"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram" class="grid h-10 w-10 place-items-center rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <p class="font-display font-semibold text-white mb-3">Services</p>
                <ul class="space-y-2 text-sm text-navy-200">
                    <li><a href="{{ route('services') }}" class="hover:text-white transition-colors">Entretien régulier</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white transition-colors">Dépannage</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white transition-colors">Analyse de l'eau</a></li>
                    <li><a href="{{ route('home') }}#hospitality" class="hover:text-white transition-colors">Offre hospitalité</a></li>
                </ul>
            </div>

            <div>
                <p class="font-display font-semibold text-white mb-3">Contact</p>
                <ul class="space-y-2 text-sm text-navy-200">
                    <li><a href="tel:+596696940054" class="hover:text-white transition-colors">0696 94 00 54</a></li>
                    <li><a href="mailto:contact@dloazurpiscines.com" class="hover:text-white transition-colors">contact@dloazurpiscines.com</a></li>
                    <li>Martinique (972)</li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Espace client</a></li>
                </ul>
            </div>

            <div class="text-center">
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-block rounded-2xl bg-white p-2.5 shadow-md hover:brightness-95 transition" aria-label="Écrire à Dlo Azur sur WhatsApp">
                    <img loading="lazy" src="{{ asset('assets/brand/qr.png') }}" alt="QR code WhatsApp de Dlo Azur Piscines" width="96" height="96" class="h-24 w-24">
                </a>
                <p class="mt-2 text-xs text-navy-300">Scannez pour WhatsApp</p>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto max-w-content px-5 sm:px-8 py-5 flex flex-wrap items-center justify-between gap-3 text-xs text-navy-300">
                <span>© {{ date('Y') }} Dlo Azur Piscines · Pierre ADAM</span>
                <span class="flex gap-4">
                    <a href="{{ route('legal.mentions') }}" class="hover:text-white">Mentions légales</a>
                    <a href="{{ route('legal.cgv') }}" class="hover:text-white">CGV</a>
                    <a href="{{ route('legal.confidentialite') }}" class="hover:text-white">Confidentialité</a>
                </span>
            </div>
        </div>
    </footer>

    {{-- Floating WhatsApp CTA (mobile) — wa.me/596696940054 --}}
    <a
        href="https://wa.me/596696940054"
        aria-label="Nous écrire sur WhatsApp"
        rel="noopener noreferrer"
        target="_blank"
        class="fixed bottom-5 right-5 h-14 w-14 rounded-full bg-[#25D366] shadow-lg md:hidden z-40 grid place-items-center text-white active:scale-95 transition"
    >
        <x-icon.whatsapp :size="28" />
    </a>
</body>
</html>
