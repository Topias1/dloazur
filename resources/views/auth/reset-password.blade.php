@extends('layouts.auth')

@section('title', 'Nouveau mot de passe')

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
        <h1 class="font-display font-semibold text-2xl text-ink-950">Nouveau mot de passe</h1>
        <p class="text-ink-500 mt-1 text-sm">Choisissez un mot de passe sûr.</p>
    </div>

    {{-- Error messages --}}
    @if ($errors->any())
        <div class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-danger font-medium">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4"
        x-data="{ sending: false }" @submit="sending = true">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-semibold text-ink-900 mb-1.5">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autocomplete="email"
                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
            >
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-ink-900 mb-1.5">Nouveau mot de passe</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
            >
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-ink-900 mb-1.5">Confirmer le mot de passe</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 text-ink-900 placeholder:text-ink-400 focus:ring-2 focus:ring-azure-500 focus:bg-white outline-none transition"
            >
        </div>

        <button type="submit"
            :disabled="sending"
            :class="sending ? 'opacity-60 cursor-not-allowed' : ''"
            class="w-full h-13 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors"
            x-text="sending ? 'Envoi...' : 'Réinitialiser le mot de passe'">
            Réinitialiser le mot de passe
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-ink-400">
        Données hébergées en Europe · <a href="{{ route('legal.confidentialite') }}" class="hover:text-ink-600">Confidentialité</a>
    </p>
</div>
@endsection
