@extends('layouts.app')

@section('content')
    {{-- Services page — SITE-02 --}}
    {{-- h1 sr-only pour a11y (Plan 01-06) : le titre visuel de la section est un h2 --}}
    <h1 class="sr-only">Services de pisciniste · Dlo Azur Piscines</h1>
    <div class="pt-24">
        @include('vitrine.partials.services-grid')
        @include('vitrine.partials.services-detail')
        @include('vitrine.partials.urgence-eau-verte')
        @include('vitrine.partials.how-it-works')
        @include('vitrine.partials.final-cta')
    </div>
@endsection
