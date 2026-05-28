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
        <section class="relative hidden lg:flex flex-col justify-between bg-navy-900 overflow-hidden">
            <div class="absolute inset-0 ripple opacity-80" aria-hidden="true"></div>
            <div class="relative px-12 py-10">
                <span class="text-azure-400">
                    <x-icon.drop :size="44" />
                </span>
                <h1 class="mt-8 font-display text-4xl text-sand-50">Dlo Azur Piscines</h1>
                <p class="mt-4 max-w-md text-navy-100 leading-relaxed">
                    L'artisan du lagon — l'opérateur enregistre chaque passage, le client suit l'historique.
                </p>
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
