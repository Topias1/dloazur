@extends('layouts.app')

@section('content')
    {{-- Section order per UI-SPEC §Page Structure — non-negotiable --}}

    {{-- 1. Hero --}}
    @include('vitrine.partials.hero')

    {{-- 2. Services grid --}}
    @include('vitrine.partials.services-grid')

    {{-- 3. Avant / après animé (remplace « Urgence eau verte » + « Nos chantiers », feedback Pierre) --}}
    @include('vitrine.partials.avant-apres')

    {{-- 4b. Philosophie SEO — Pourquoi choisir / eau parfaite (récupéré Zyro, Plan 01-06) --}}
    @include('vitrine.partials.philosophie')

    {{-- 5. Hospitality / B2B --}}
    @include('vitrine.partials.hospitality')

    {{-- 7. Pierre bio --}}
    @include('vitrine.partials.pierre')

    {{-- 8. Espace client teaser --}}
    @include('vitrine.partials.espace-client-teaser')

    {{-- 9. Testimonials --}}
    @include('vitrine.partials.testimonials')

    {{-- 11. Final CTA --}}
    @include('vitrine.partials.final-cta')
@endsection
