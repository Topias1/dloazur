<?php

/**
 * PictureComponentTest — Plan 999.1-05 Task 2.
 *
 * Asserts <x-picture> emits the correct AVIF → WebP → JPG source set,
 * and that the generated .webp / .avif siblings exist on disk.
 */

use Illuminate\Support\Facades\Blade;

it('renders avif source before webp source before img fallback', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" />');

    $avifPos = strpos($html, 'type="image/avif"');
    $webpPos = strpos($html, 'type="image/webp"');
    $imgPos  = strpos($html, '<img');

    expect($avifPos)->not->toBeFalse()
        ->and($webpPos)->not->toBeFalse()
        ->and($imgPos)->not->toBeFalse()
        ->and($avifPos)->toBeLessThan($webpPos)
        ->and($webpPos)->toBeLessThan($imgPos);
});

it('includes image/avif source type', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" />');
    expect($html)->toContain('type="image/avif"');
});

it('includes image/webp source type', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" />');
    expect($html)->toContain('type="image/webp"');
});

it('emits img with decoding async', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" />');
    expect($html)->toContain('decoding="async"');
});

it('emits jpg fallback src in img tag', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" />');
    expect($html)->toContain('hero-pierre-piscine.jpg');
});

it('renders the alt attribute', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="Piscine Pierre" />');
    expect($html)->toContain('alt="Piscine Pierre"');
});

it('defaults loading to lazy', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" />');
    expect($html)->toContain('loading="lazy"');
});

it('accepts eager loading for hero images', function () {
    $html = Blade::render('<x-picture src="assets/brand/photos/hero-pierre-piscine.jpg" alt="test" loading="eager" />');
    expect($html)->toContain('loading="eager"');
});

it('generated webp sibling exists on disk for hero image', function () {
    $path = public_path('assets/brand/photos/hero-pierre-piscine.webp');
    expect(file_exists($path))->toBeTrue();
});

it('generated avif sibling exists on disk for hero image', function () {
    $path = public_path('assets/brand/photos/hero-pierre-piscine.avif');
    expect(file_exists($path))->toBeTrue();
});
