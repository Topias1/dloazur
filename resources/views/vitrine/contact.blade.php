@extends('layouts.app')

@section('content')
    {{-- Contact page — SITE-05 (view shell only — Plan 04 injects the Livewire form) --}}
    <div class="pt-32 pb-20 mx-auto max-w-content px-5 sm:px-8">
        <div class="max-w-lg mx-auto">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Nous contacter</h1>
            <p class="mt-4 text-lg text-ink-700 leading-relaxed">
                Un devis gratuit, une urgence, une question sur votre piscine ? Écrivez-nous directement sur WhatsApp ou via le formulaire ci-dessous.
            </p>

            <div class="mt-8">
                {{-- Plan 04 injects the Livewire ContactForm component. --}}
                {{-- Guard: renders fallback if component not yet registered. --}}
                @if(class_exists(\App\Livewire\ContactForm::class))
                    <livewire:contact-form />
                @else
                    <div class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-8">
                        <p class="text-ink-500 mb-4">Formulaire en cours de chargement…</p>
                        <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-[#25D366] text-white font-semibold shadow-sm hover:brightness-95 transition">
                            <x-icon.whatsapp :size="18" />
                            Nous écrire sur WhatsApp
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
