@extends('layouts.admin')

@section('title', 'Nouvel article · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <div class="px-5 sm:px-8 py-7 max-w-2xl">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-7">
            <a href="{{ route('admin.blog.index') }}"
                class="h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200 flex items-center justify-center text-ink-500 hover:text-ink-900 transition-colors"
                aria-label="Retour à la liste">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <h1 class="font-display font-semibold text-2xl text-ink-950">Nouvel article</h1>
        </div>

        {{-- Form card (PostForm arrives in Plan 04) --}}
        <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
            <livewire:post-form />
        </div>

    </div>
    <x-admin.mobile-bottom-nav />
@endsection
