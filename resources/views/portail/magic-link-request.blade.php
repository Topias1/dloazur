<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace client · Dlo Azur Piscines</title>
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
                Accédez à votre espace
            </h1>
            <p class="text-ink-500 mt-2 text-sm leading-relaxed">
                Saisissez votre adresse e-mail. Un lien de connexion vous sera envoyé.
            </p>

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('portail.magic-link.send') }}" class="mt-6"
                x-data="{ sending: false }" @submit="sending = true">
                @csrf

                <label for="email" class="block text-sm font-medium text-ink-700 mb-1.5">
                    Adresse e-mail
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    autocomplete="email"
                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none text-ink-900 placeholder:text-ink-400"
                    placeholder="vous@exemple.com"
                    value="{{ old('email') }}"
                >

                @error('email')
                    <p class="text-sm text-danger mt-2">{{ $message }}</p>
                @enderror

                <button
                    type="submit"
                    :disabled="sending"
                    :class="sending ? 'opacity-60 cursor-not-allowed' : ''"
                    class="w-full h-13 rounded-xl bg-azure-500 text-white font-bold text-base mt-4 hover:bg-azure-600 transition-colors"
                    x-text="sending ? 'Envoi...' : 'Recevoir mon lien'"
                >
                    Recevoir mon lien
                </button>
            </form>

            {{-- Erreur magic-link (lien expiré / invalide / erreur serveur) --}}
            @error('ml')
                <div class="mt-4 text-sm text-danger bg-danger/10 ring-1 ring-danger/30 rounded-xl p-3">
                    {{ $message }}
                </div>
            @enderror

            {{-- Message de statut --}}
            @if (session('status'))
                <div class="mt-4 text-sm text-success bg-success/10 ring-1 ring-success/30 rounded-xl p-3">
                    {{ session('status') }}
                </div>
            @endif

            @error('throttle')
                <p class="text-sm text-danger mt-2">{{ $message }}</p>
            @enderror

            {{-- Connexion démo (DEV-ONLY) — visible uniquement si le flag est actif --}}
            @if (config('app.demo_login'))
                <div class="flex items-center gap-3 mt-6">
                    <span class="h-px flex-1 bg-sand-200"></span>
                    <span class="text-xs uppercase tracking-wide text-ink-400">Serveur de démo</span>
                    <span class="h-px flex-1 bg-sand-200"></span>
                </div>

                <div class="flex flex-col gap-3 mt-4">
                    <form method="POST" action="{{ route('portail.demo.client') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full h-12 rounded-xl bg-sand-50 text-azure-700 font-semibold ring-1 ring-azure-200 hover:bg-azure-50 transition-colors"
                        >
                            Démo Client
                        </button>
                    </form>

                    <form method="POST" action="{{ route('portail.demo.admin') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full h-12 rounded-xl bg-white text-navy-900 font-semibold ring-1 ring-navy-900/15 hover:bg-sand-50 transition-colors"
                        >
                            Démo Admin
                        </button>
                    </form>
                </div>
            @endif

        </div>

        {{-- Fallback WhatsApp --}}
        <div class="text-center mt-8">
            <p class="text-sm text-ink-500">Pas d'email ? Contactez-nous sur WhatsApp</p>
            <a
                href="https://wa.me/596696940054"
                class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-whatsapp text-white font-bold mt-3 shadow-sm hover:opacity-90 transition-opacity"
                target="_blank"
                rel="noopener"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.462 3.49"/>
                </svg>
                Écrire
            </a>
        </div>

        {{-- Footer --}}
        <p class="text-xs text-ink-400 mt-6 text-center">
            Données hébergées en Europe · Confidentialité
        </p>

    </div>
</body>
</html>
