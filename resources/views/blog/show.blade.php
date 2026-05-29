@extends('layouts.app', ['title' => $title, 'description' => $description])

@push('head')
{!! $articleJsonLd !!}
@endpush

@section('content')
<div class="mx-auto max-w-2xl px-5 sm:px-8 pt-28 sm:pt-32 pb-20">

    {{-- ===== Article header ===== --}}
    <header class="mb-10">
        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-azure-600 hover:text-azure-700 transition-colors">
            <x-icon.arrow-right :size="15" class="rotate-180" />
            Retour au blog
        </a>

        <p class="mt-7 text-xs font-bold uppercase tracking-[0.2em] text-lagon-600">Le blog</p>
        <h1 class="mt-3 font-display font-bold text-3xl sm:text-4xl text-ink-950 leading-[1.1]">
            {{ $post['title'] }}
        </h1>

        <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-500">
            @if ($post['show_date'])
                <time datetime="{{ $post['date']->toIso8601String() }}" class="tabular-nums">
                    {{ $post['date']->locale('fr')->isoFormat('LL') }}
                </time>
                <span aria-hidden="true">·</span>
            @endif
            <span>Par {{ $post['author'] }}</span>
            <span aria-hidden="true">·</span>
            <span class="tabular-nums">{{ $post['reading_time'] }} min de lecture</span>
        </div>

        {{-- lagon accent rule — brand "live water", replaces the hero photo --}}
        <div class="mt-6 h-1 w-16 rounded-full bg-lagon-500" aria-hidden="true"></div>
    </header>

    {{-- ===== Article body ===== --}}
    <div class="prose prose-lg max-w-none
        prose-headings:font-display prose-headings:text-ink-950
        prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4
        prose-h3:text-lg prose-h3:font-semibold prose-h3:text-ink-900 prose-h3:mt-8
        prose-p:text-ink-700 prose-p:leading-relaxed
        prose-a:text-azure-600 prose-a:font-semibold prose-a:no-underline hover:prose-a:underline
        prose-strong:text-ink-950 prose-strong:font-semibold
        prose-li:text-ink-700 prose-li:marker:text-azure-500
        prose-img:rounded-2xl">
        <x-markdown>{!! $post['body'] !!}</x-markdown>
    </div>

    {{-- ===== Footer: CTA + à lire aussi ===== --}}
    <footer class="mt-16 space-y-12">
        {{-- contact CTA --}}
        <section class="relative overflow-hidden rounded-3xl bg-navy-900 text-sand-50 ring-1 ring-white/10 p-8 sm:p-10">
            <svg
                class="pointer-events-none absolute -right-14 -bottom-16 h-56 w-56 text-azure-500/25"
                viewBox="0 0 200 200" fill="none" stroke="currentColor" aria-hidden="true"
            >
                <circle cx="100" cy="100" r="38" stroke-width="2"/>
                <circle cx="100" cy="100" r="62" stroke-width="1.5" class="text-lagon-500/30"/>
                <circle cx="100" cy="100" r="86" stroke-width="1"/>
            </svg>
            <div class="relative">
                <h2 class="font-display font-bold text-2xl text-white">Une question sur votre piscine&nbsp;?</h2>
                <p class="mt-3 max-w-md leading-relaxed text-navy-100">
                    Un doute sur l'eau, une panne, un devis d'entretien : écrivez-moi sur WhatsApp, je réponds vite.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a
                        href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 h-13 px-5 rounded-xl bg-[#25D366] text-white font-bold shadow-sm hover:brightness-95 transition"
                    >
                        <x-icon.whatsapp :size="18" />
                        WhatsApp
                    </a>
                    <a
                        href="tel:+596696940054"
                        class="inline-flex items-center justify-center gap-2 h-13 px-5 rounded-xl bg-white/10 text-white font-semibold ring-1 ring-white/15 hover:bg-white/15 transition-colors"
                    >
                        0696 94 00 54
                    </a>
                </div>
            </div>
        </section>

        {{-- à lire aussi --}}
        @if ($morePosts->isNotEmpty())
            <section>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-lagon-600">À lire aussi</p>
                <ul class="mt-4 border-t border-sand-200">
                    @foreach ($morePosts as $other)
                        <li class="border-b border-sand-200">
                            <a
                                href="{{ route('blog.show', ['slug' => $other['slug']]) }}"
                                class="group flex items-center gap-4 py-5"
                            >
                                <span class="min-w-0 flex-1">
                                    <h3 class="font-display font-semibold text-lg text-ink-950 leading-snug group-hover:text-azure-600 transition-colors">
                                        {{ $other['title'] }}
                                    </h3>
                                    <span class="mt-1 block text-sm text-ink-500 tabular-nums">{{ $other['reading_time'] }} min de lecture</span>
                                </span>
                                <x-icon.arrow-right :size="18" class="shrink-0 text-azure-500 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </footer>

</div>
@endsection
