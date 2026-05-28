@extends('layouts.app')

@section('content')
    <section class="relative min-h-[80vh] flex items-center bg-sand-50 px-5 sm:px-8">
        <div class="mx-auto max-w-content w-full pt-32 pb-20 text-center rise">
            <span class="inline-flex items-center gap-2 rounded-full bg-lagon-300/30 ring-1 ring-lagon-400/30 px-3 py-1 text-sm font-semibold text-lagon-700">
                <span class="h-1.5 w-1.5 rounded-full bg-lagon-500"></span>
                Walking skeleton — la vitrine arrive en Plan 03
            </span>

            <h1 class="mt-8 font-display font-bold text-ink-950 text-5xl sm:text-7xl tracking-tight">
                Dlo Azur Piscines
            </h1>

            <p class="mt-5 text-lg sm:text-xl text-ink-700 max-w-2xl mx-auto leading-relaxed">
                Pisciniste d'entretien en Martinique — passages réguliers, transparence sur les interventions, portail client.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <x-cta-whatsapp size="lg" label="Demander un devis sur WhatsApp" />
            </div>

            <p class="mt-12 text-xs text-ink-500">
                Site en construction — la vitrine complète arrive en Plan 03.
            </p>
        </div>
    </section>
@endsection
