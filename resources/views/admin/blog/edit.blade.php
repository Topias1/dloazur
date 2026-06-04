@extends('layouts.admin')

@section('title'){{ $post->title }} · Modifier · Dlo Azur@endsection

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <div class="px-5 sm:px-8 py-7 max-w-2xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.blog.index') }}"
                class="h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200 flex items-center justify-center text-ink-500 hover:text-ink-900 transition-colors"
                aria-label="Retour à la liste">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <h1 class="font-display font-semibold text-xl text-ink-950">{{ $post->title }}</h1>
        </div>

        {{-- Article form card (PostForm arrives in Plan 04) --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <h2 class="font-display font-semibold text-base text-ink-900 mb-5">Article</h2>
            <livewire:post-form :postId="$post->id" />
        </div>

    </div>
    <x-admin.mobile-bottom-nav />
@endsection
