@extends('layouts.app', ['title' => $title, 'description' => $description])

@push('head')
{!! $articleJsonLd !!}
@endpush

@section('content')
<div class="py-16 px-5">
    <article class="prose prose-lg prose-azure max-w-2xl mx-auto">
        <header class="not-prose mb-8">
            <a href="{{ route('blog.index') }}" class="text-sm text-azure-600 hover:text-azure-700 transition-colors">
                ← Retour au blog
            </a>
            <h1 class="font-display font-bold text-3xl text-ink-950 mt-4 mb-2">{{ $post['title'] }}</h1>
            <p class="text-sm text-ink-500">
                <time datetime="{{ $post['date']->toIso8601String() }}" class="tabular-nums">
                    {{ $post['date']->locale('fr')->isoFormat('LL') }}
                </time>
                · {{ $post['author'] }}
            </p>
        </header>

        <x-markdown>{!! $post['body'] !!}</x-markdown>
    </article>
</div>
@endsection
