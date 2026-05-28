@extends('layouts.app')

@section('content')
    {{-- Section order per UI-SPEC §Page Structure — non-negotiable --}}

    {{-- 1. Hero --}}
    @include('vitrine.partials.hero')

    {{-- 2. Services grid --}}
    @include('vitrine.partials.services-grid')

    {{-- 3. Urgence eau verte — D-34 (inserted between services-grid and how-it-works) --}}
    @include('vitrine.partials.urgence-eau-verte')

    {{-- 4. How it works --}}
    @include('vitrine.partials.how-it-works')

    {{-- 5. Hospitality / B2B --}}
    @include('vitrine.partials.hospitality')

    {{-- 6. Réalisations --}}
    @include('vitrine.partials.realisations-grid')

    {{-- 7. Pierre bio --}}
    @include('vitrine.partials.pierre')

    {{-- 8. Espace client teaser --}}
    @include('vitrine.partials.espace-client-teaser')

    {{-- 9. Testimonials --}}
    @include('vitrine.partials.testimonials')

    {{-- 10. Nos engagements — D-35 (before final CTA) --}}
    @include('vitrine.partials.engagements')

    {{-- 11. Final CTA --}}
    @include('vitrine.partials.final-cta')
@endsection
