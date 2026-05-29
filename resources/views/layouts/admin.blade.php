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
        {{-- Sidebar slot — Plan 05 fills via @section('sidebar') --}}
        <aside class="bg-navy-900 text-sand-50/90 lg:min-h-screen">
            @yield('sidebar')
        </aside>

        {{-- Main content area --}}
        <div class="flex flex-col min-h-screen">
            {{-- Topbar slot — Plan 05 wires user menu + signout --}}
            <header class="bg-sand-50 border-b border-navy-900/10 px-6 h-15 flex items-center justify-between">
                @yield('topbar')
            </header>

            <main class="flex-1 px-6 py-8">
                @yield('main')
                @yield('content')
            </main>

            {{-- Mobile bottom nav placeholder — Plan 05 wires it --}}
            <nav class="lg:hidden border-t border-navy-900/10 bg-sand-50 px-2 py-2"></nav>
        </div>
    </div>

    {{--
        Backwards-compatible aliases for component-style consumers:
        x-slot:sidebar / x-slot:main markers below are referenced by
        Plan 01-01 acceptance criteria. Plan 05 may evolve toward
        anonymous-component slots if that style fits better.
        x-slot:sidebar x-slot:main
    --}}
</body>
</html>
