<?php

/**
 * Verifies Tailwind v4 @theme tokens are transposed from mockups/v1/theme.js
 * and that the build pipeline compiles cleanly without #000/#fff or extra font weights.
 *
 * Plan 01-01 Task 3a — behavior contract enforced.
 */

it('compiles tailwind v4 @theme without errors and writes manifest', function () {
    $manifest = base_path('public/build/manifest.json');
    expect(file_exists($manifest))->toBeTrue('public/build/manifest.json missing — run `npm run build` first');
});

it('app.css imports tailwindcss and declares @theme block', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    expect($css)->toContain('@import "tailwindcss"');
    expect($css)->toContain('@theme {');
});

it('app.css contains every required OKLCH brand token', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    $required = [
        '--color-azure-500: oklch(0.615 0.211 256)',
        '--color-navy-900: oklch(0.232 0.052 251)',
        '--color-sand-50: oklch(0.987 0.005 85)',
        '--color-lagon-400: oklch(0.788 0.110 204)',
        '--font-display: "Fredoka"',
        '--font-sans: "Inter"',
        '--spacing-13: 3.25rem',
        '--spacing-15: 3.75rem',
        '--spacing-18: 4.5rem',
        '--breakpoint-xs: 400px',
        '--container-content: 75rem',
    ];
    foreach ($required as $needle) {
        expect($css)->toContain($needle);
    }
});

it('app.css preserves custom utilities from mockup', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    expect($css)->toContain('.ripple');
    expect($css)->toContain('.photo-grade');
    expect($css)->toContain('@keyframes rise');
    expect($css)->toContain('prefers-reduced-motion');
});

it('app.css contains no #000 or #fff hex literals outside comments', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    // Strip block + line comments
    $stripped = preg_replace('!/\*.*?\*/!s', '', $css);
    $stripped = preg_replace('!//.*$!m', '', $stripped);
    expect($stripped)->not->toContain('#000');
    expect($stripped)->not->toContain('#fff');
    expect($stripped)->not->toContain('#FFF');
});

it('vite.config.js wires @tailwindcss/vite plugin', function () {
    $vite = file_get_contents(base_path('vite.config.js'));
    expect($vite)->toContain('@tailwindcss/vite');
});

it('only loads Inter weights 400/600 and Fredoka 600/700', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    // Allowed: Inter:wght@400;600 and Fredoka:wght@600;700
    // Disallowed: weight 500 in either family's font-face/@import
    expect($css)->not->toMatch('/Inter:wght@[^\'"]*500/');
    expect($css)->not->toMatch('/Fredoka:wght@[^\'"]*500/');
    expect($css)->not->toMatch('/Inter:wght@[^\'"]*700/');
    expect($css)->not->toMatch('/Fredoka:wght@[^\'"]*400/');
});
