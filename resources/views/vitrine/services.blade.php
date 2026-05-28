@extends('layouts.app')

@section('content')
    {{-- Services page — SITE-02 --}}
    <div class="pt-24">
        @include('vitrine.partials.services-grid')
        @include('vitrine.partials.urgence-eau-verte')
        @include('vitrine.partials.how-it-works')
        @include('vitrine.partials.final-cta')
    </div>
@endsection
