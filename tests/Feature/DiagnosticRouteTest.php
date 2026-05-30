<?php

// ──────────────────────────────────────────────────────────────────────────────
// Req9 — /diagnostic route publique (Plan 05-01 + 05-05)
// Implémentation complète ici (05-05), stubs Plan 05-01 retirés.
// ──────────────────────────────────────────────────────────────────────────────

it('/diagnostic is reachable without authentication (anonymous GET returns 200)', function () {
    $this->get('/diagnostic')
        ->assertStatus(200);
});

it('/diagnostic has no auth middleware in the route definition', function () {
    $routes         = app('router')->getRoutes();
    $diagnosticRoute = $routes->getByName('diagnostic');

    expect($diagnosticRoute)->not()->toBeNull('Route "diagnostic" is not registered');

    $middleware = $diagnosticRoute->gatherMiddleware();
    expect($middleware)->not()->toContain('auth', 'Diagnostic route must not require authentication');
    expect($middleware)->not()->toContain('auth:clients', 'Diagnostic route must not require client auth');
});

it('/diagnostic is not inside the cache.headers:vitrine group', function () {
    $routes = app('router')->getRoutes();
    $route  = $routes->getByName('diagnostic');

    expect($route)->not()->toBeNull('Route "diagnostic" is not registered');

    $middleware = $route->gatherMiddleware();
    foreach ($middleware as $mw) {
        expect($mw)->not()->toContain('cache.headers:vitrine',
            '/diagnostic must not be in the vitrine cache group (Livewire stateful component)'
        );
    }
});

it('/diagnostic returns the canonical URL in SEO meta', function () {
    $this->get('/diagnostic')
        ->assertStatus(200)
        ->assertSee('/diagnostic');
});

it('route diagnostic.pdf exists and has no auth middleware (Req8)', function () {
    $routes = app('router')->getRoutes();
    $route  = $routes->getByName('diagnostic.pdf');

    expect($route)->not()->toBeNull('Route "diagnostic.pdf" is not registered');

    $middleware = $route->gatherMiddleware();
    expect($middleware)->not()->toContain('auth', 'PDF route must not require authentication (session-gated in controller, not middleware)');
    expect($middleware)->not()->toContain('auth:clients');
});

it('route diagnostic.pdf is not inside the cache.headers:vitrine group', function () {
    $routes = app('router')->getRoutes();
    $route  = $routes->getByName('diagnostic.pdf');

    expect($route)->not()->toBeNull('Route "diagnostic.pdf" is not registered');

    $middleware = $route->gatherMiddleware();
    foreach ($middleware as $mw) {
        expect($mw)->not()->toContain('cache.headers:vitrine',
            '/diagnostic/{id}/pdf must not be cached (stateful session-gated route)'
        );
    }
});
