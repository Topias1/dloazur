<?php

use App\Support\BlogRepository;

it('blog index returns 200 with at least 1 article listed', function () {
    $response = $this->get('/blog');

    $response->assertStatus(200);
    $response->assertSee('Bienvenue chez Dlo Azur Piscines');
    $response->assertSee('Pourquoi nous existons');
});

it('blog index renders dates in French long format', function () {
    $response = $this->get('/blog');

    $response->assertStatus(200);
    $response->assertSee('28 mai 2026');
});

it('blog show with valid slug returns 200 with rendered markdown body', function () {
    $response = $this->get('/blog/bienvenue-dlo-azur');

    $response->assertStatus(200);
    $response->assertSee('Une eau claire');
    $response->assertSee('<article class="prose', false);
});

it('blog show with unknown slug returns 404', function () {
    $response = $this->get('/blog/this-does-not-exist');

    $response->assertStatus(404);
});

it('blog show emits Article JSON-LD', function () {
    $response = $this->get('/blog/bienvenue-dlo-azur');

    $response->assertStatus(200);
    $response->assertSee('<script type="application/ld+json">', false);
    $response->assertSee('"@type":"Article"', false);
    $response->assertSee('Bienvenue chez Dlo Azur Piscines');
    $response->assertSee('Pierre ADAM');
});

it('blog show has own title and meta description from front matter', function () {
    $response = $this->get('/blog/bienvenue-dlo-azur');

    $response->assertStatus(200);
    $response->assertSee('<title>Bienvenue chez Dlo Azur Piscines', false);
    $response->assertSee('Pourquoi nous existons', false);
});

it('spatie/laravel-markdown safe_mode is on — raw HTML in markdown is stripped', function () {
    $fixtureDir = base_path('tests/fixtures/blog');

    // Bind a fixture-aware BlogRepository for this test
    $repo = new BlogRepository($fixtureDir);
    app()->instance(BlogRepository::class, $repo);

    $response = $this->get('/blog/test-post');

    $response->assertStatus(200);
    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertDontSee('<script>', false);

    // Restore normal binding after test
    app()->forgetInstance(BlogRepository::class);
});

it('sitemap.xml includes blog URLs after Plan 04 ships', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    $response->assertSee('<loc>', false);
    $response->assertSee('/blog', false);
    $response->assertSee('/blog/bienvenue-dlo-azur', false);
});
