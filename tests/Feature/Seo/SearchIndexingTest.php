<?php

/**
 * SearchIndexingTest — garde d'indexation des environnements de test.
 *
 * Tant que SITE_INDEXABLE n'est pas explicitement true (sites staging + main
 * pré-prod), aucune page ne doit être indexable et robots.txt doit tout bloquer,
 * crawlers IA compris. Au lancement prod (SITE_INDEXABLE=true) la garde tombe.
 */

it('pose X-Robots-Tag noindex sur la home quand le site n\'est pas indexable', function () {
    config(['app.indexable' => false]);

    $this->get('/')
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noai, noimageai');
});

it('ne pose AUCUN X-Robots-Tag quand le site est indexable', function () {
    config(['app.indexable' => true]);

    expect($this->get('/')->headers->has('X-Robots-Tag'))->toBeFalse();
});

it('robots.txt interdit tout et bloque les crawlers IA quand non indexable', function () {
    config(['app.indexable' => false]);

    $body = $this->get('/robots.txt')->assertOk()->getContent();

    expect($body)->toContain("User-agent: *\nDisallow: /");
    expect($body)->toContain('User-agent: GPTBot');
    expect($body)->toContain('User-agent: ClaudeBot');
    expect($body)->toContain('User-agent: Google-Extended');
    // La directive Sitemap reste présente (attendue par CutoverReadinessTest).
    expect($body)->toMatch('/Sitemap:\s*\S+\/sitemap\.xml/');
});

it('robots.txt autorise l\'indexation (hors /admin) quand indexable', function () {
    config(['app.indexable' => true]);

    $body = $this->get('/robots.txt')->assertOk()->getContent();

    expect($body)->toContain("User-agent: *\nAllow: /");
    expect($body)->toContain('Disallow: /admin');
    expect($body)->not->toContain('Disallow: /'."\n");
});
