<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo.meta
        :title="$title ?? null"
        :description="$description ?? null"
        :image="$image ?? null"
        :canonical="$canonical ?? null"
    />

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
    <header class="fixed top-0 inset-x-0 z-40">
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
                    {{-- Plan 03 fills the nav with route-driven sections --}}
                    <span class="text-ink-400">Site en construction</span>
                </div>

                <div class="flex items-center gap-2">
                    <x-cta-whatsapp size="sm" />
                </div>
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
                        <span class="block font-display font-bold text-sand-50 text-lg">Dlo Azur</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-azure-400">Piscines</span>
                    </span>
                </div>
                <p class="mt-4 text-sm leading-relaxed max-w-xs text-navy-200">
                    Entretien, dépannage et analyse de l'eau de piscines en Martinique. Un service personnalisé, par Pierre ADAM.
                </p>
            </div>

            <div>
                <p class="font-display font-semibold text-sand-50 mb-3">Services</p>
                <ul class="space-y-2 text-sm text-navy-200">
                    <li><span>Entretien régulier</span></li>
                    <li><span>Dépannage</span></li>
                    <li><span>Analyse de l'eau</span></li>
                </ul>
            </div>

            <div>
                <p class="font-display font-semibold text-sand-50 mb-3">Contact</p>
                <ul class="space-y-2 text-sm text-navy-200">
                    <li><a href="tel:+596696940054" class="hover:text-sand-50 transition-colors">0696 94 00 54</a></li>
                    <li><a href="mailto:contact@dloazurpiscines.com" class="hover:text-sand-50 transition-colors">contact@dloazurpiscines.com</a></li>
                    <li>Martinique (972)</li>
                </ul>
            </div>

            <div class="text-center">
                <div class="inline-block rounded-2xl bg-sand-50 p-2.5 shadow-md">
                    {{-- Plan 03 wires the real QR; placeholder swatch keeps layout stable. --}}
                    <div class="h-24 w-24 grid place-items-center text-azure-600 font-display font-bold">QR</div>
                </div>
                <p class="mt-2 text-xs text-navy-300">wa.me/596696940054</p>
            </div>
        </div>

        <div class="border-t border-sand-50/10">
            <div class="mx-auto max-w-content px-5 sm:px-8 py-5 flex flex-wrap items-center justify-between gap-3 text-xs text-navy-300">
                <span>© {{ date('Y') }} Dlo Azur Piscines · Pierre ADAM</span>
                <span class="flex gap-4">
                    <span>Mentions légales</span>
                    <span>CGV</span>
                    <span>Confidentialité</span>
                </span>
            </div>
        </div>
    </footer>

    {{-- Floating WhatsApp CTA (mobile) — wa.me/596696940054 --}}
    <a
        href="https://wa.me/596696940054"
        aria-label="Contacter sur WhatsApp"
        rel="noopener noreferrer"
        target="_blank"
        class="md:hidden fixed bottom-5 right-5 z-40 grid h-14 w-14 place-items-center rounded-full bg-[#25D366] text-sand-50 shadow-lg active:scale-95 transition"
    >
        <x-icon.whatsapp :size="24" />
    </a>
</body>
</html>
