@extends('layouts.app', ['title' => $title, 'description' => $description])

@section('content')
@php($lead = $posts->first())
@php($rest = $posts->slice(1)->values())

<div class="mx-auto max-w-3xl px-5 sm:px-8 pt-28 sm:pt-32 pb-20 sm:pb-28">

    {{-- ===== Page header ===== --}}
    <header class="mb-12 sm:mb-16">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-lagon-600">Le blog</p>
        <h1 class="mt-3 font-display font-bold text-4xl sm:text-5xl text-ink-950 leading-[1.05]">
            Conseils &amp; actualités
        </h1>
        <p class="mt-4 max-w-xl text-lg leading-relaxed text-ink-600">
            Entretien, dépannage, analyse de l'eau : ce que j'apprends sur le terrain,
            partagé pour garder votre piscine claire toute l'année en Martinique.
        </p>
    </header>

    @if ($lead)
        {{-- ===== Lead article — marine feature card ===== --}}
        <a
            href="{{ route('blog.show', ['slug' => $lead['slug']]) }}"
            class="group relative block overflow-hidden rounded-3xl bg-navy-900 text-sand-50 ring-1 ring-white/10 shadow-sm hover:shadow-md transition-shadow duration-300 p-8 sm:p-10 mb-12"
        >
            {{-- water-ripple motif (brand "live water", no stock photo) --}}
            <svg
                class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 text-azure-500/25"
                viewBox="0 0 200 200" fill="none" stroke="currentColor" aria-hidden="true"
            >
                <circle cx="100" cy="100" r="38" stroke-width="2"/>
                <circle cx="100" cy="100" r="62" stroke-width="1.5" class="text-lagon-500/30"/>
                <circle cx="100" cy="100" r="86" stroke-width="1"/>
            </svg>

            <div class="relative">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-lagon-300">À la une</p>
                <h2 class="mt-3 font-display font-bold text-2xl sm:text-3xl text-white leading-tight max-w-xl group-hover:text-azure-100 transition-colors">
                    {{ $lead['title'] }}
                </h2>
                @if ($lead['excerpt'])
                    <p class="mt-3 max-w-2xl leading-relaxed text-navy-100">{{ $lead['excerpt'] }}</p>
                @endif
                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-navy-200">
                    @if ($lead['show_date'])
                        <time datetime="{{ $lead['date']->toIso8601String() }}" class="tabular-nums">
                            {{ $lead['date']->locale('fr')->isoFormat('LL') }}
                        </time>
                        <span aria-hidden="true" class="text-navy-400">·</span>
                    @endif
                    <span class="tabular-nums">{{ $lead['reading_time'] }} min de lecture</span>
                    <span class="ml-auto inline-flex items-center gap-1.5 font-semibold text-azure-200 group-hover:text-white transition-colors">
                        Lire l'article
                        <x-icon.arrow-right :size="16" class="transition-transform duration-300 group-hover:translate-x-0.5" />
                    </span>
                </div>
            </div>
        </a>
    @endif

    @if ($rest->isNotEmpty())
        {{-- ===== Remaining articles — numbered editorial list ===== --}}
        <ol class="border-t border-sand-200">
            @foreach ($rest as $post)
                <li class="border-b border-sand-200">
                    <a
                        href="{{ route('blog.show', ['slug' => $post['slug']]) }}"
                        class="group flex items-start gap-5 sm:gap-7 py-7"
                    >
                        <span
                            aria-hidden="true"
                            class="shrink-0 grid place-items-center h-14 w-14 sm:h-16 sm:w-16 rounded-xl bg-azure-50 font-display font-bold text-xl text-azure-700 tabular-nums ring-1 ring-azure-100 transition-colors duration-200 group-hover:bg-azure-100"
                        >
                            {{ str_pad((string) ($loop->iteration + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <h3 class="font-display font-semibold text-xl text-ink-950 leading-snug group-hover:text-azure-600 transition-colors">
                                {{ $post['title'] }}
                            </h3>
                            @if ($post['excerpt'])
                                <p class="mt-1.5 leading-relaxed text-ink-600 line-clamp-2">{{ $post['excerpt'] }}</p>
                            @endif
                            <span class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-500">
                                @if ($post['show_date'])
                                    <time datetime="{{ $post['date']->toIso8601String() }}" class="tabular-nums">
                                        {{ $post['date']->locale('fr')->isoFormat('LL') }}
                                    </time>
                                    <span aria-hidden="true">·</span>
                                @endif
                                <span class="tabular-nums">{{ $post['reading_time'] }} min de lecture</span>
                                <span class="inline-flex items-center gap-1.5 font-semibold text-azure-600 group-hover:text-azure-700 transition-colors">
                                    Lire
                                    <x-icon.arrow-right :size="15" class="transition-transform duration-300 group-hover:translate-x-0.5" />
                                </span>
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>
    @elseif (! $lead)
        {{-- ===== Empty state ===== --}}
        <div class="rounded-2xl bg-sand-100 ring-1 ring-sand-200 px-6 py-12 text-center">
            <p class="font-display font-semibold text-xl text-ink-900">Bientôt les premiers articles</p>
            <p class="mt-2 text-ink-600">Conseils d'entretien et actualités arrivent très vite. Revenez nous voir.</p>
        </div>
    @endif

</div>
@endsection
