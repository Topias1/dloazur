<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hors ligne · Dlo Azur Piscines</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-sand-50 text-ink-700 antialiased">
    <main class="min-h-screen flex flex-col items-center justify-center px-6 text-center">
        <div class="text-azure-500 mb-2">
            <x-icon.drop :size="64" />
        </div>
        <h1 class="font-display font-bold text-2xl text-ink-950">Dlo Azur</h1>
        <h2 class="font-display font-semibold text-xl text-ink-950 mt-8">Vous êtes hors ligne</h2>
        <p class="text-ink-500 mt-3 max-w-sm">La saisie d'un passage reste disponible. Les autres pages se chargeront au retour du réseau.</p>
        <a href="/admin/passages/create"
           class="h-13 px-6 rounded-xl bg-azure-500 text-white font-bold mt-6 inline-flex items-center justify-center">
            Retour à la saisie
        </a>
    </main>
</body>
</html>
