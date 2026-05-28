{{-- Blog wrapper — extends the vitrine layout with a typographic article shell --}}
@extends('layouts.app')

@section('content')
    <article class="prose prose-lg max-w-2xl mx-auto px-5 py-16">
        @yield('article')
    </article>
@endsection
