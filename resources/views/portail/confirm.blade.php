<!doctype html>
{{-- D-50 CRITIQUE : Cette page est PUREMENT statique.
     GET ne consomme pas le token. Seul le POST (/auth/confirm) consomme.
     Protection SafeLinks Microsoft 365 : le proxy M365 fait un GET pre-scan,
     la page HTML statique est inoffensive. L'utilisateur clique "Confirmer",
     le POST est alors envoyé et le token est consommé.
     INTERDIT : pas de JS auto-submit, pas de fetch, pas d'image trackée.
--}}
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion à votre espace · Dlo Azur Piscines</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-sand-50 min-h-screen flex items-center justify-center px-6 antialiased">
    <div class="max-w-sm w-full">

        {{-- Card --}}
        <div class="rounded-3xl bg-white ring-1 ring-navy-900/8 shadow-lg p-8">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <x-icon.drop :size="34" class="text-azure-500" />
                <span class="font-display font-bold text-xl text-ink-950">Dlo Azur</span>
            </div>

            {{-- Titre --}}
            <h1 class="font-display font-semibold text-xl text-ink-950 mt-6">
                Connexion à votre espace
            </h1>
            <p class="text-ink-500 mt-2 text-sm leading-relaxed">
                Vous êtes sur le point d'accéder à votre espace Dlo Azur Piscines.
            </p>

            {{-- Formulaire POST — le seul endroit où le token est consommé --}}
            <form method="POST" action="{{ route('portail.magic-link.confirm') }}" class="mt-6"
                x-data="{ sending: false }" @submit="sending = true">
                @csrf
                <input type="hidden" name="ml" value="{{ $token }}">

                <button
                    type="submit"
                    :disabled="sending"
                    :class="sending ? 'opacity-60 cursor-not-allowed' : ''"
                    class="w-full h-13 rounded-xl bg-azure-500 text-white font-bold text-base hover:bg-azure-600 transition-colors"
                    x-text="sending ? 'Envoi...' : 'Confirmer ma connexion'"
                >
                    Confirmer ma connexion
                </button>
            </form>

            {{-- Note sécurité --}}
            <p class="text-xs text-ink-400 mt-4 text-center">
                Ce lien expire dans 48 h · Utilisable jusqu'à 3 fois
            </p>

        </div>

        {{-- Footer --}}
        <p class="text-xs text-ink-400 mt-6 text-center">
            Données hébergées en Europe · Confidentialité
        </p>

    </div>
</body>
</html>
