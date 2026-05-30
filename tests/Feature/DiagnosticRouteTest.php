<?php

// ──────────────────────────────────────────────────────────────────────────────
// Req9 — /diagnostic public route (Plan 05-01 Task 1 + Task 2)
// ──────────────────────────────────────────────────────────────────────────────

it('/diagnostic is reachable without authentication (anonymous GET returns 200)', function () {
    $this->get('/diagnostic')
        ->assertStatus(200);
})->markTestIncomplete('Plan 05-01 Task 1 — once route + controller + view exist, remove this incomplete marker');

it('/diagnostic renders the S1 brand landing with the Display title', function () {
    // Checks that the hero H1 with font-display + the two entry tiles are present
    $this->get('/diagnostic')
        ->assertStatus(200)
        ->assertSee('Trouver mon problème')
        ->assertSee('Analyser mon eau');
})->markTestIncomplete('Plan 05-01 Task 1 — brand landing rendered once view exists');

it('/diagnostic has no auth middleware in the route definition', function () {
    // Verify the route is not wrapped in the auth or admin group
    $routes = app('router')->getRoutes();
    $diagnosticRoute = $routes->getByName('diagnostic');

    expect($diagnosticRoute)->not()->toBeNull('Route "diagnostic" is not registered');

    $middleware = $diagnosticRoute->gatherMiddleware();
    expect($middleware)->not()->toContain('auth', 'Diagnostic route must not require authentication');
    expect($middleware)->not()->toContain('auth:clients', 'Diagnostic route must not require client auth');
})->markTestIncomplete('Plan 05-01 Task 1 — route registered after controller creation');

it('/diagnostic is not inside the cache.headers:vitrine group', function () {
    // Livewire stateful route must not carry cache headers
    $routes = app('router')->getRoutes();
    $route  = $routes->getByName('diagnostic');

    if ($route === null) {
        $this->markTestIncomplete('Plan 05-01 Task 1 — route not yet registered');
    }

    $middleware = $route->gatherMiddleware();
    foreach ($middleware as $mw) {
        expect($mw)->not()->toContain('cache.headers:vitrine',
            '/diagnostic must not be in the vitrine cache group (it hosts a Livewire stateful component)'
        );
    }
})->markTestIncomplete('Plan 05-01 Task 1 — verify cache middleware exclusion after route registration');

it('the vitrine nav contains a link to route(diagnostic)', function () {
    $this->get('/')->assertStatus(200)->assertSee('Diagnostic piscine gratuit');
})->markTestIncomplete('Plan 05-01 Task 1 — nav link added in layouts/app.blade.php');

it('the eau-verte-urgence page links to route(diagnostic)', function () {
    $this->get('/services/eau-verte-urgence')
        ->assertStatus(200)
        ->assertSee('Diagnostic gratuit');
})->markTestIncomplete('Plan 05-01 Task 1 — eau-verte CTA added to that view');

it('/diagnostic returns the canonical URL in SEO meta', function () {
    // DiagnosticController::show() passes canonical = url("/diagnostic")
    $this->get('/diagnostic')
        ->assertStatus(200)
        ->assertSee('/diagnostic');
})->markTestIncomplete('Plan 05-01 Task 1 — SEO meta canonical verified after controller creation');
