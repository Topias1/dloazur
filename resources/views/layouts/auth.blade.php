<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Connexion') · Dlo Azur Piscines</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0080ff">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-navy-900 text-sand-50 antialiased min-h-screen">

    <div class="min-h-screen lg:grid lg:grid-cols-2">
        {{-- Brand panel (left on lg+) — ripple overlay, navy ground --}}
        {{-- UI-SPEC §Auth page + Copywriting Contract: H2 "Chaque passage, gardé en mémoire." --}}
        <section class="relative hidden lg:flex flex-col justify-between bg-navy-900 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-navy-900/80 to-navy-800/70 ripple opacity-80" aria-hidden="true"></div>
            <div class="relative h-full flex flex-col justify-between p-12">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="text-azure-400">
                        <x-icon.drop :size="34" />
                    </span>
                    <span class="leading-none">
                        <span class="block font-display font-semibold text-white text-2xl">Dlo Azur</span>
                        <span class="block text-xs font-semibold uppercase tracking-[0.24em] text-azure-400">Piscines</span>
                    </span>
                </a>
                <div>
                    <h2 class="font-display font-semibold text-4xl text-white leading-tight max-w-md">Chaque passage, gardé en mémoire.</h2>
                    <p class="mt-4 text-lg text-navy-100 max-w-md leading-relaxed">
                        L'espace de suivi de Dlo Azur : mesures de l'eau, actions et photos, accessibles en quelques secondes.
                    </p>
                </div>
                <p class="text-sm text-navy-300">Dlo Azur Piscines · Martinique · 0696 94 00 54</p>
            </div>
        </section>

        {{-- Form panel (right) --}}
        <section class="flex items-center justify-center bg-sand-50 text-ink-700 px-6 py-12">
            <div class="w-full max-w-md">
                @yield('form')
                @yield('content')
            </div>
        </section>
    </div>
</body>
</html>
