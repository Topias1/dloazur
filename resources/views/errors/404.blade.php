@php($meta = [
    'title' => 'Page introuvable · Dlo Azur Piscines',
    'description' => "Cette page n'existe pas ou a été déplacée.",
    'meta' => [],
])
@extends('layouts.app')

@section('content')
    <section class="min-h-[70vh] flex items-center">
        <div class="mx-auto max-w-content w-full px-5 sm:px-8 py-20 text-center">
            <span class="font-display font-bold text-azure-500 text-7xl sm:text-8xl tabular-nums">404</span>
            <h1 class="mt-4 font-display font-bold text-ink-950 text-3xl sm:text-4xl">
                Cette page a filé comme l'eau.
            </h1>
            <p class="mt-4 text-lg text-ink-700 leading-relaxed max-w-md mx-auto">
                La page que vous cherchez n'existe pas ou a été déplacée. Revenons à l'essentiel : votre piscine.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-azure-500 text-white font-bold text-base shadow-md hover:bg-azure-400 transition-colors">
                    Retour à l'accueil
                    <x-icon.arrow-right :size="18" />
                </a>
                <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-13 px-6 rounded-xl bg-whatsapp text-white font-bold text-base shadow-sm hover:brightness-95 transition">
                    <x-icon.whatsapp :size="18" />
                    Nous écrire
                </a>
            </div>
        </div>
    </section>
@endsection
