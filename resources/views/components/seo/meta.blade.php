@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
    'type' => 'website',
])

@php
    $resolvedTitle = $title ?? config('app.name', 'Dlo Azur Piscines');
    $resolvedDescription = $description ?? 'Pisciniste d\'entretien en Martinique — passages réguliers, transparence sur les interventions, portail client.';
    $resolvedCanonical = $canonical ?? url()->current();
    $resolvedImage = $image ?? asset('assets/brand/og-default.jpg');
@endphp

<title>{{ $resolvedTitle }}</title>
<meta name="description" content="{{ $resolvedDescription }}">
<link rel="canonical" href="{{ $resolvedCanonical }}">

{{-- Theme color anchors the browser UI on the azure-500 brand value. --}}
<meta name="theme-color" content="#0080ff">

{{-- Favicon — Plan 03 replaces the placeholder. --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/favicon.svg') }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $resolvedTitle }}">
<meta property="og:description" content="{{ $resolvedDescription }}">
<meta property="og:url" content="{{ $resolvedCanonical }}">
<meta property="og:image" content="{{ $resolvedImage }}">
<meta property="og:locale" content="fr_FR">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDescription }}">
<meta name="twitter:image" content="{{ $resolvedImage }}">
