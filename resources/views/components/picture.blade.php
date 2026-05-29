@props(['src', 'alt' => '', 'class' => '', 'loading' => 'lazy'])
@php $base = preg_replace('/\.(jpe?g|png)$/i', '', $src); @endphp
<picture>
    <source srcset="{{ asset($base.'.avif') }}" type="image/avif">
    <source srcset="{{ asset($base.'.webp') }}" type="image/webp">
    <img src="{{ asset($src) }}" alt="{{ $alt }}" class="{{ $class }}" loading="{{ $loading }}" decoding="async">
</picture>
