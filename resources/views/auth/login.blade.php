@extends('layouts.auth')

@section('title', 'Connexion')

@section('form')
<div x-data="{ tab: 'pro' }">

    {{-- Mobile logo --}}
    <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2.5 mb-8">
        <span class="text-azure-500">
            <x-icon.drop :size="30" />
        </span>
        <span class="font-display font-semibold text-ink-950 text-xl">Dlo Azur</span>
    </a>

    {{-- Segmented toggle: Espace pro / Espace client --}}
    <div class="grid grid-cols-2 gap-1 rounded-2xl bg-sand-200/70 p-1 mb-7">
        <button type="button"
            @click="tab = 'pro'"
            :class="tab === 'pro' ? 'bg-white text-ink-950 shadow-xs' : 'text-ink-500 hover:text-ink-700'"
            class="h-10 rounded-xl text-sm font-bold transition-colors cursor-pointer">
            Espace pro
        </button>
        <button type="button"
            @click="tab = 'client'"
            :class="tab === 'client' ? 'bg-white text-ink-950 shadow-xs' : 'text-ink-500 hover:text-ink-700'"
            class="h-10 rounded-xl text-sm font-bold transition-colors cursor-pointer">
            Espace client
        </button>
    </div>

    {{-- ===== PRO PANE ===== --}}
    <div x-show="tab === 'pro'">
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <h1 class="font-display font-semibold text-2xl text-ink-950">Bon retour, Pierre.</h1>
                <p class="text-ink-500 mt-1 text-sm">Connectez-vous pour gérer vos passages.</p>
            </div>

            {{-- Error messages (wrong credentials / throttle) --}}
            @if ($errors->any())
                <div class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
                    <p class="text-sm text-danger font-medium">{{ $errors->first('email') }}</p>
                </div>
            @endif

            {{-- Email --}}
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
                    placeholder="pierre@dloazurpiscines.com"
                >
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-semibold text-ink-900">Mot de passe</label>
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-azure-600 hover:text-azure-700">Oublié ?</a>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
                    placeholder="••••••••••"
                >
            </div>

            {{-- Remember me --}}
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" name="remember" id="remember" value="1"
                    class="h-5 w-5 rounded-md border-sand-300 text-azure-500 focus:ring-azure-500">
                <span class="text-sm text-ink-700">Rester connecté sur ce téléphone</span>
            </label>

            {{-- Submit --}}
            <button type="submit"
                class="w-full h-12 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors">
                Se connecter
            </button>
        </form>
    </div>

    {{-- ===== CLIENT PANE (Phase 2 stub) ===== --}}
    <div x-show="tab === 'client'" x-cloak>
        <div class="space-y-4">
            <div>
                <h1 class="font-display font-semibold text-2xl text-ink-950">Votre espace piscine.</h1>
                <p class="text-ink-500 mt-1 text-sm">Pas de mot de passe : recevez un lien d'accès sécurisé par e-mail.</p>
            </div>

            <div>
                <label for="cli-email" class="block text-sm font-semibold text-ink-900 mb-1.5">Votre e-mail</label>
                <input id="cli-email" type="email" autocomplete="email" disabled
                    class="w-full h-12 px-4 rounded-xl bg-sand-100 ring-1 ring-sand-200 text-ink-400 placeholder:text-ink-400 outline-none cursor-not-allowed"
                    placeholder="vous@exemple.com">
            </div>

            <button type="button" disabled aria-disabled="true"
                class="w-full h-12 rounded-xl bg-azure-500/40 text-white font-bold cursor-not-allowed opacity-60">
                Bientôt disponible
            </button>

            <p class="text-sm text-ink-500 text-center">
                Disponible dès l'arrivée des fiches clients (Phase 2).
            </p>
        </div>
    </div>

    {{-- Footer note --}}
    <p class="mt-8 text-center text-sm text-ink-400">
        Données hébergées en Europe · <a href="{{ route('legal.confidentialite') }}" class="hover:text-ink-600">Confidentialité</a>
    </p>

</div>
@endsection
