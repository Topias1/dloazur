@extends('layouts.auth')

@section('title', 'Réinitialisation du mot de passe')

@section('form')
<div class="space-y-6">

    {{-- Mobile logo --}}
    <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2.5 mb-8">
        <span class="text-azure-500">
            <x-icon.drop :size="30" />
        </span>
        <span class="font-display font-semibold text-ink-950 text-xl">Dlo Azur</span>
    </a>

    <div>
        <h1 class="font-display font-semibold text-2xl text-ink-950">Mot de passe oublié ?</h1>
        <p class="text-ink-500 mt-1 text-sm">
            Entrez votre adresse e-mail et nous vous enverrons un lien de réinitialisation.
        </p>
    </div>

    {{-- Status message (link sent) --}}
    @if (session('status'))
        <div class="rounded-xl bg-success/10 ring-1 ring-success/30 px-4 py-3">
            <p class="text-sm text-success font-medium">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Error messages --}}
    @if ($errors->any())
        <div class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
            <p class="text-sm text-danger font-medium">{{ $errors->first('email') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4"
        x-data="{ sending: false }" @submit="sending = true">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-ink-900 mb-1.5">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                autofocus
                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                placeholder="admin@dloazurpiscines.com"
            >
        </div>

        <button type="submit"
            :disabled="sending"
            :class="sending ? 'opacity-60 cursor-not-allowed' : ''"
            class="w-full h-13 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors"
            x-text="sending ? 'Envoi...' : 'Recevoir le lien de réinitialisation'">
            Recevoir le lien de réinitialisation
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-azure-600 hover:text-azure-700">
                ← Retour à la connexion
            </a>
        </div>
    </form>

    <p class="mt-4 text-center text-sm text-ink-400">
        Données hébergées en Europe · <a href="{{ route('legal.confidentialite') }}" class="hover:text-ink-600">Confidentialité</a>
    </p>
</div>
@endsection
