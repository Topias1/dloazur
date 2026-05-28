@props([
    'size' => 'md',
    'label' => 'WhatsApp',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'h-10 px-4 text-sm gap-2',
        'lg' => 'h-15 px-7 text-base gap-3',
        default => 'h-13 px-6 text-base gap-2.5',
    };
@endphp

<a
    href="https://wa.me/596696940054"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center rounded-xl bg-[#25D366] text-white font-semibold shadow-md hover:brightness-110 focus-visible:outline-azure-500 transition-all ' . $sizeClasses,
        'rel' => 'noopener noreferrer',
        'target' => '_blank',
    ]) }}
>
    <x-icon.whatsapp :size="$size === 'sm' ? 16 : 20" />
    <span>{{ $label }}</span>
</a>
