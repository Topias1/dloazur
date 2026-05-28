@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'h-10 px-4 text-sm',
        'lg' => 'h-15 px-7 text-base',
        default => 'h-13 px-6 text-base',
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-white/10 ring-1 ring-white/25 text-white backdrop-blur hover:bg-white/15',
        'ghost' => 'bg-transparent text-azure-700 hover:bg-azure-50',
        'whatsapp' => 'bg-[#25D366] text-white shadow-md hover:brightness-110',
        default => 'bg-azure-500 text-white font-semibold shadow-md hover:bg-azure-400',
    };

    $classes = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-all focus-visible:outline-azure-500 ' . $sizeClasses . ' ' . $variantClasses;
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>{{ $slot }}</button>
@endif
