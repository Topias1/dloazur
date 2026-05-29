<?php

/**
 * SeoTest — Plan 01-03 Task 1 (RED).
 *
 * Covers SITE-07: sitemap.xml + LocalBusiness JSON-LD + meta tags on all pages.
 */

it('sitemap returns valid XML', function () {
    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    expect($response->getContent())->toStartWith('<?xml');
});

it('sitemap contains all vitrine routes', function () {
    $content = $this->get('/sitemap.xml')->getContent();
    expect($content)->toContain('<loc>' . url('/') . '</loc>');
    expect($content)->toContain(url('/services'));
    expect($content)->toContain(url('/realisations'));
    expect($content)->toContain(url('/contact'));
    expect($content)->toContain(url('/mentions-legales'));
    expect($content)->toContain(url('/cgv'));
    expect($content)->toContain(url('/confidentialite'));
});

it('home embeds LocalBusiness JSON-LD with multi-type array (D-01, supersedes D-26)', function () {
    $content = $this->get('/')->getContent();
    expect($content)->toContain('<script type="application/ld+json">');

    preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $content, $m);
    expect($m)->not->toBeEmpty('Expected JSON-LD script tag in home page');

    $json = json_decode($m[1], true);
    expect($json)->not->toBeNull('JSON-LD must be valid JSON');
    expect($json['@context'])->toBe('https://schema.org');
    // D-01: multi-type array replaces single "Plumber" string (D-26 superseded)
    expect($json['@type'])->toBeArray('D-01: @type must be an array');
    expect($json['@type'])->toContain('LocalBusiness');
    expect($json['@type'])->toContain('HomeAndConstructionBusiness');
});

it('LocalBusiness JSON-LD has required fields', function () {
    $content = $this->get('/')->getContent();
    preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $content, $m);
    $json = json_decode($m[1], true);

    expect($json['name'])->toBe('Dlo Azur Piscines');
    expect($json['telephone'])->toBe('+596696940054');
    expect($json['address']['addressRegion'])->toBe('Martinique');
    expect((float) $json['geo']['latitude'])->toBe(14.6037);
    expect($json['priceRange'])->toBe('€€');
});

it('LocalBusiness JSON-LD areaServed includes 4 cities + Martinique area', function () {
    $content = $this->get('/')->getContent();
    preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $content, $m);
    $json = json_decode($m[1], true);

    $cities = array_filter($json['areaServed'], fn($a) => $a['@type'] === 'City');
    $areas  = array_filter($json['areaServed'], fn($a) => $a['@type'] === 'AdministrativeArea');

    expect(count($cities))->toBe(4);
    expect(count($areas))->toBe(1);
});

it('LocalBusiness JSON-LD has openingHoursSpecification for Mon-Fri + Sat', function () {
    $content = $this->get('/')->getContent();
    preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $content, $m);
    $json = json_decode($m[1], true);

    expect(count($json['openingHoursSpecification']))->toBeGreaterThanOrEqual(2);

    $allDays = [];
    foreach ($json['openingHoursSpecification'] as $spec) {
        $days = is_array($spec['dayOfWeek']) ? $spec['dayOfWeek'] : [$spec['dayOfWeek']];
        foreach ($days as $d) {
            $allDays[] = $d;
        }
    }

    expect($allDays)->toContain('Monday');
    expect($allDays)->toContain('Saturday');
});

it('every page has canonical + theme-color #0080ff', function () {
    $routes = ['/', '/services', '/realisations', '/contact', '/mentions-legales', '/cgv', '/confidentialite'];

    foreach ($routes as $route) {
        $content = $this->get($route)->getContent();
        expect($content)->toContain('<meta name="theme-color" content="#0080ff"')
            ->and($content)->toContain('<link rel="canonical"');
    }
});
