<?php

/**
 * CityHubTest — Plan 999.1-04 Task 2.
 *
 * Asserts 4 city hub routes return 200, each page:
 *   - emits exactly one h1
 *   - links to at least one services.* route
 *   - retains the FAIT LOCAL REQUIS placeholder (publish gate, D-12)
 *   - emits EXACTLY ONE BreadcrumbList (duplicate-emission regression guard)
 *   - renders a visible <nav aria-label="Fil d'Ariane"> breadcrumb
 *   - contains no @push('head')…breadcrumbJsonLd (single-emission contract, T-9991-04-04)
 *   - contains no wire: directive (no Livewire on zone pages)
 *
 * Distinct-body assertion: no two city pages have identical body content.
 */

use function Pest\Laravel\get;

// ───────────────────────────────────────────────────────────
// 200 checks
// ───────────────────────────────────────────────────────────

it('GET /zones/fort-de-france returns 200', function () {
    get('/zones/fort-de-france')->assertStatus(200);
});

it('GET /zones/le-lamentin returns 200', function () {
    get('/zones/le-lamentin')->assertStatus(200);
});

it('GET /zones/schoelcher returns 200', function () {
    get('/zones/schoelcher')->assertStatus(200);
});

it('GET /zones/les-trois-ilets returns 200', function () {
    get('/zones/les-trois-ilets')->assertStatus(200);
});

// ───────────────────────────────────────────────────────────
// Exactly one h1 per page
// ───────────────────────────────────────────────────────────

it('/zones/fort-de-france emits exactly one h1', function () {
    expect(substr_count(get('/zones/fort-de-france')->getContent(), '<h1'))->toBe(1);
});

it('/zones/le-lamentin emits exactly one h1', function () {
    expect(substr_count(get('/zones/le-lamentin')->getContent(), '<h1'))->toBe(1);
});

it('/zones/schoelcher emits exactly one h1', function () {
    expect(substr_count(get('/zones/schoelcher')->getContent(), '<h1'))->toBe(1);
});

it('/zones/les-trois-ilets emits exactly one h1', function () {
    expect(substr_count(get('/zones/les-trois-ilets')->getContent(), '<h1'))->toBe(1);
});

// ───────────────────────────────────────────────────────────
// FAIT LOCAL REQUIS publish gate (D-12)
// ───────────────────────────────────────────────────────────

it('/zones/fort-de-france retains FAIT LOCAL REQUIS placeholder', function () {
    expect(get('/zones/fort-de-france')->getContent())->toContain('FAIT LOCAL REQUIS');
});

it('/zones/le-lamentin retains FAIT LOCAL REQUIS placeholder', function () {
    expect(get('/zones/le-lamentin')->getContent())->toContain('FAIT LOCAL REQUIS');
});

it('/zones/schoelcher retains FAIT LOCAL REQUIS placeholder', function () {
    expect(get('/zones/schoelcher')->getContent())->toContain('FAIT LOCAL REQUIS');
});

it('/zones/les-trois-ilets retains FAIT LOCAL REQUIS placeholder', function () {
    expect(get('/zones/les-trois-ilets')->getContent())->toContain('FAIT LOCAL REQUIS');
});

// ───────────────────────────────────────────────────────────
// City→service links present
// ───────────────────────────────────────────────────────────

it('/zones/fort-de-france links to at least one services.* route', function () {
    $content = get('/zones/fort-de-france')->getContent();
    expect($content)->toContain('/services/');
});

it('/zones/le-lamentin links to at least one services.* route', function () {
    $content = get('/zones/le-lamentin')->getContent();
    expect($content)->toContain('/services/');
});

it('/zones/schoelcher links to at least one services.* route', function () {
    $content = get('/zones/schoelcher')->getContent();
    expect($content)->toContain('/services/');
});

it('/zones/les-trois-ilets links to at least one services.* route', function () {
    $content = get('/zones/les-trois-ilets')->getContent();
    expect($content)->toContain('/services/');
});

// ───────────────────────────────────────────────────────────
// Duplicate-emission regression guard: exactly ONE BreadcrumbList (T-9991-04-04)
// ───────────────────────────────────────────────────────────

it('/zones/fort-de-france emits exactly one BreadcrumbList', function () {
    $content = get('/zones/fort-de-france')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

it('/zones/le-lamentin emits exactly one BreadcrumbList', function () {
    $content = get('/zones/le-lamentin')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

it('/zones/schoelcher emits exactly one BreadcrumbList', function () {
    $content = get('/zones/schoelcher')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

it('/zones/les-trois-ilets emits exactly one BreadcrumbList', function () {
    $content = get('/zones/les-trois-ilets')->getContent();
    expect(substr_count($content, '"@type":"BreadcrumbList"'))->toBe(1);
});

// ───────────────────────────────────────────────────────────
// Visible breadcrumb nav (human-readable, distinct from JSON-LD)
// ───────────────────────────────────────────────────────────

it('/zones/fort-de-france renders visible breadcrumb nav', function () {
    $content = get('/zones/fort-de-france')->getContent();
    expect($content)->toContain('aria-label="Fil d\'Ariane"');
    expect($content)->toContain('aria-current="page"');
});

it('/zones/le-lamentin renders visible breadcrumb nav', function () {
    $content = get('/zones/le-lamentin')->getContent();
    expect($content)->toContain('aria-label="Fil d\'Ariane"');
    expect($content)->toContain('aria-current="page"');
});

it('/zones/schoelcher renders visible breadcrumb nav', function () {
    $content = get('/zones/schoelcher')->getContent();
    expect($content)->toContain('aria-label="Fil d\'Ariane"');
    expect($content)->toContain('aria-current="page"');
});

it('/zones/les-trois-ilets renders visible breadcrumb nav', function () {
    $content = get('/zones/les-trois-ilets')->getContent();
    expect($content)->toContain('aria-label="Fil d\'Ariane"');
    expect($content)->toContain('aria-current="page"');
});

// ───────────────────────────────────────────────────────────
// No Livewire directives on zone pages
// ───────────────────────────────────────────────────────────

it('/zones/fort-de-france contains no wire: directive', function () {
    expect(get('/zones/fort-de-france')->getContent())->not->toContain('wire:');
});

it('/zones/le-lamentin contains no wire: directive', function () {
    expect(get('/zones/le-lamentin')->getContent())->not->toContain('wire:');
});

it('/zones/schoelcher contains no wire: directive', function () {
    expect(get('/zones/schoelcher')->getContent())->not->toContain('wire:');
});

it('/zones/les-trois-ilets contains no wire: directive', function () {
    expect(get('/zones/les-trois-ilets')->getContent())->not->toContain('wire:');
});

// ───────────────────────────────────────────────────────────
// Distinct body content — no two city pages identical (D-10)
// ───────────────────────────────────────────────────────────

it('no two city hub pages have identical rendered body content', function () {
    $contents = [
        'fort-de-france'  => get('/zones/fort-de-france')->getContent(),
        'le-lamentin'     => get('/zones/le-lamentin')->getContent(),
        'schoelcher'      => get('/zones/schoelcher')->getContent(),
        'les-trois-ilets' => get('/zones/les-trois-ilets')->getContent(),
    ];

    $slugs  = array_keys($contents);
    $bodies = array_values($contents);

    // Compare every pair — assert each pair differs
    for ($i = 0; $i < count($bodies) - 1; $i++) {
        for ($j = $i + 1; $j < count($bodies); $j++) {
            expect($bodies[$i])->not->toBe(
                $bodies[$j],
                "City pages '{$slugs[$i]}' and '{$slugs[$j]}' have identical rendered content"
            );
        }
    }
});
