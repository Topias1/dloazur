@extends('layouts.app', ['title' => $title, 'description' => $description])

@section('content')
<div class="max-w-3xl mx-auto px-5 py-20">

    <h1 class="font-display font-bold text-4xl text-ink-950 mb-2">Le blog</h1>
    <p class="text-ink-500 mb-10">Actualités, conseils d'entretien et dépannage de piscines en Martinique.</p>

    @forelse ($posts as $post)
    <article class="mb-10 pb-10 border-b border-sand-200 last:border-0 last:mb-0 last:pb-0">
        <time class="text-sm text-ink-500 tabular-nums" datetime="{{ $post['date']->toIso8601String() }}">
            {{ $post['date']->locale('fr')->isoFormat('LL') }}
        </time>
        <h2 class="font-display font-semibold text-xl text-ink-950 mt-1 mb-2">
            <a href="{{ route('blog.show', ['slug' => $post['slug']]) }}" class="hover:text-azure-600 transition-colors">
                {{ $post['title'] }}
            </a>
        </h2>
        @if ($post['excerpt'])
        <p class="text-ink-600 leading-relaxed mb-3">{{ $post['excerpt'] }}</p>
        @endif
        <a
            href="{{ route('blog.show', ['slug' => $post['slug']]) }}"
            class="text-sm font-semibold text-azure-600 hover:text-azure-700 transition-colors"
        >
            Lire l'article →
        </a>
    </article>
    @empty
    <p class="text-ink-500">Aucun article pour l'instant. Revenez bientôt.</p>
    @endforelse

</div>
@endsection
