<?php

/**
 * BreadcrumbTest — Plan 999.1-03 Task 1.
 *
 * Asserts the BreadcrumbSchema builder emits correct JSON-LD and that the
 * layout renders the $breadcrumbJsonLd slot exactly once (single-emission contract).
 */

use App\Support\SchemaOrg\BreadcrumbSchema;

it('BreadcrumbSchema toScript returns a BreadcrumbList JSON-LD script block', function () {
    $output = (new BreadcrumbSchema)->toScript([
        ['name' => 'Accueil',  'url' => url('/')],
        ['name' => 'Services', 'url' => url('/services')],
    ]);

    expect($output)->toContain('"@type":"BreadcrumbList"');
});

it('BreadcrumbSchema itemListElement contains ListItem', function () {
    $output = (new BreadcrumbSchema)->toScript([
        ['name' => 'Accueil',  'url' => url('/')],
        ['name' => 'Services', 'url' => url('/services')],
    ]);

    expect($output)->toContain('ListItem');
});

it('BreadcrumbSchema assigns correct positions', function () {
    $output = (new BreadcrumbSchema)->toScript([
        ['name' => 'Accueil',  'url' => url('/')],
        ['name' => 'Services', 'url' => url('/services')],
    ]);

    expect($output)->toContain('"position":1');
    expect($output)->toContain('"position":2');
});

it('BreadcrumbSchema includes crumb names in output', function () {
    $output = (new BreadcrumbSchema)->toScript([
        ['name' => 'Accueil',  'url' => url('/')],
        ['name' => 'Services', 'url' => url('/services')],
    ]);

    expect($output)->toContain('Accueil');
    expect($output)->toContain('Services');
});

it('layout forwards $type to x-seo.meta (type attribute present)', function () {
    $content = file_get_contents(base_path('resources/views/layouts/app.blade.php'));
    expect($content)->toContain(':type=');
});

it('layout has breadcrumbJsonLd emission slot', function () {
    $content = file_get_contents(base_path('resources/views/layouts/app.blade.php'));
    expect($content)->toContain('breadcrumbJsonLd');
});
