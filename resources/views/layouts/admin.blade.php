<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Administration') · Dlo Azur Piscines</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-sand-100 text-ink-700 antialiased min-h-screen">

    <div class="lg:grid lg:grid-cols-[16rem_1fr] min-h-screen">
        {{-- Sidebar slot — the sidebar component owns its own landmark element --}}
        <div class="bg-navy-900 text-sand-50/90 lg:min-h-screen">
            @yield('sidebar')
        </div>

        {{-- Main content area --}}
        <div class="flex flex-col min-h-screen">
            {{-- Topbar slot — component owns the <header> landmark --}}
            <div class="bg-sand-50 border-b border-navy-900/10">
                @yield('topbar')
            </div>

            <main class="flex-1">
                @yield('main')
                @yield('content')
            </main>
        </div>
    </div>

    {{--
        Backwards-compatible aliases for component-style consumers:
        x-slot:sidebar / x-slot:main markers below are referenced by
        Plan 01-01 acceptance criteria. Plan 05 may evolve toward
        anonymous-component slots if that style fits better.
        x-slot:sidebar x-slot:main
    --}}

    {{-- Sync drawer + sync-success confirmation — mounted once at layout level
         so the drawer and flush are available on every admin page (P0 SC-1).
         The badge in topbar/mobile-nav dispatches sync-drawer:open from all pages. --}}
    <x-admin.sync-drawer />

    {{-- Sync-success confirmation: briefly shown after queue reaches zero. --}}
    <div
        x-data
        x-show="$store.offlineQueue.syncSuccess"
        x-cloak
        x-transition.opacity.duration.300ms
        role="status"
        aria-live="polite"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[80] flex items-center gap-2 px-4 py-2.5 rounded-full shadow-lg text-sm font-semibold"
        style="background-color: oklch(0.700 0.150 155 / 0.12); color: oklch(0.500 0.130 155); box-shadow: 0 0 0 1px oklch(0.700 0.150 155 / 0.30), 0 4px 16px oklch(0.700 0.150 155 / 0.15);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Tout est synchronisé
    </div>
</body>
</html>
