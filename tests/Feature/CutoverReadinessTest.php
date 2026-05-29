<?php

/**
 * CutoverReadinessTest — Plan 01-06 Task 1
 *
 * Suite de pré-vol qui valide toutes les routes Phase 1 et les en-têtes
 * HTTP Cache-Control avant chaque déploiement en staging/production.
 * D-22 — validation pre-cutover automatisée.
 */

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Test 3 nécessite la BDD (seeder Pierre) — RefreshDatabase appliqué
// à l'échelle du fichier via uses() au niveau top-level.
uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — chaque route publique retourne HTTP 200
// ---------------------------------------------------------------------------

$publicRoutes = [
    '/',
    '/services',
    '/realisations',
    '/blog',
    '/blog/bienvenue-dlo-azur',
    '/contact',
    '/mentions-legales',
    '/cgv',
    '/confidentialite',
    '/sitemap.xml',
    '/robots.txt',
    '/up',
    '/login',
    '/forgot-password',
];

it('every public route returns 200', function (string $url) {
    $this->get($url)->assertOk();
})->with($publicRoutes);

// ---------------------------------------------------------------------------
// Test 2 — GET /admin sans auth → redirection vers /login
// ---------------------------------------------------------------------------

it('admin route redirects anonymous to login', function () {
    $this->get('/admin')->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Test 3 — GET /admin authentifié → 200
// ---------------------------------------------------------------------------

it('admin route returns 200 when authenticated', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

// ---------------------------------------------------------------------------
// Test 4 — robots.txt contient une directive Sitemap:
// ---------------------------------------------------------------------------

it('sitemap is referenced from robots.txt', function () {
    $body = $this->get('/robots.txt')->getContent();
    expect($body)->toContain('Sitemap:');
    // La valeur pointée doit se terminer par /sitemap.xml
    preg_match('/Sitemap:\s*(\S+)/i', $body, $m);
    expect($m[1] ?? '')->toEndWith('/sitemap.xml');
});

// ---------------------------------------------------------------------------
// Test 5 — routes vitrine statiques ont Cache-Control: public + max-age=300
// ---------------------------------------------------------------------------

$cachedVitrineRoutes = [
    '/',
    '/services',
    '/realisations',
    '/mentions-legales',
    '/cgv',
    '/confidentialite',
    '/blog',
];

it('every static vitrine GET has Cache-Control: public, max-age=300', function (string $url) {
    $cacheControl = $this->get($url)->headers->get('Cache-Control');
    expect($cacheControl)->toContain('public');
    expect($cacheControl)->toContain('max-age=300');
})->with($cachedVitrineRoutes);

// ---------------------------------------------------------------------------
// Test 6 — /up ne doit PAS avoir de directive public dans Cache-Control
// ---------------------------------------------------------------------------

it('/up does NOT set a public cache directive', function () {
    $cacheControl = $this->get('/up')->headers->get('Cache-Control') ?? '';
    // Doit contenir no-cache ou no-store, ou ne PAS contenir "public"
    $hasNoCacheDirective = str_contains($cacheControl, 'no-cache')
        || str_contains($cacheControl, 'no-store');
    $hasPublic = str_contains($cacheControl, 'public');

    expect($hasNoCacheDirective || ! $hasPublic)->toBeTrue(
        "/up Cache-Control should not be public; got: {$cacheControl}"
    );
});

// ---------------------------------------------------------------------------
// Test 7 — /sitemap.xml a un cache 1 h (max-age=3600)
// ---------------------------------------------------------------------------

it('/sitemap.xml has a 1-hour cache window', function () {
    $cacheControl = $this->get('/sitemap.xml')->headers->get('Cache-Control') ?? '';
    expect($cacheControl)->toContain('max-age=3600');
});

// ---------------------------------------------------------------------------
// Test 8 — STAGING_URL (CI env) correspond au pattern laravel.cloud ou dloazurpiscines.com
// ---------------------------------------------------------------------------

it('live-staging URL is captured in CI env', function () {
    $stagingUrl = env('STAGING_URL', '');
    expect($stagingUrl)->toMatch('/^https?:\/\/.+\.(laravel\.cloud|dloazurpiscines\.com)/');
})->skip(fn () => empty(env('STAGING_URL')), 'STAGING_URL absent — test ignoré en dev local (actif en CI uniquement)');
