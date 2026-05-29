@props(['size' => 30])
{{-- Logo motif — the drop wordmark behind the "Dlo Azur" ligature.
     Extracted from mockups/v1/vitrine.html topbar SVG.
     viewBox height is 38 (not 34) so the drop's rounded base (max y≈37) fits
     inside the viewport instead of being clipped flat. --}}
<svg
    {{ $attributes->merge(['class' => 'inline-block']) }}
    width="{{ $size }}"
    height="{{ (int) round($size * 38 / 28) }}"
    viewBox="0 0 28 38"
    fill="currentColor"
    aria-hidden="true"
    focusable="false"
    xmlns="http://www.w3.org/2000/svg"
>
    <path d="M14 0C14 0 28 14 28 23a14 14 0 1 1-28 0C0 14 14 0 14 0Z"/>
    <path
        d="M14 4C14 4 24 14 24 22a10 10 0 0 1-20 0C4 14 14 4 14 4Z"
        fill="oklch(0.987 0.005 85)"
        opacity="0.16"
    />
</svg>
