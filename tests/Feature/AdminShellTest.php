<?php

/**
 * AdminShellTest — Plan 01-05 Task 1 behavior contract (RED).
 *
 * Verifies D-17..D-20: admin shell stub, sidebar nav, greyed items,
 * user pill, topbar, mobile bottom nav.
 */

use App\Models\User;
use Database\Seeders\AdminSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 12 — GET /admin without auth redirects to /login
// ---------------------------------------------------------------------------

it('GET /admin without auth redirects to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Test 13 — GET /admin while authenticated returns 200 with dashboard stub
// ---------------------------------------------------------------------------

it('GET /admin while authenticated returns 200 with the dashboard stub content', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Bonjour Pierre,');
    // Plan 11 (D-10): dashboard restructuré en agenda-led — sous-titre + section "Aujourd'hui".
    $response->assertSee('Voici votre agenda du jour.');
    $response->assertSee("Aujourd'hui", false);
    // Vanity counts rétrogradés en bandeau texte (minuscules).
    $response->assertSee('clients actifs');
    $response->assertSee('passages cette semaine');
    $response->assertSee('À synchroniser');
    // Plan 02-03: "Factures en attente" remplacé par "Eau à surveiller" (UI-SPEC §Dashboard admin Stat cards)
    $response->assertSee('Eau à surveiller');
});

// ---------------------------------------------------------------------------
// Test 14 — admin sidebar has greyed nav items with bientôt badges
// Plan 02-02: Clients is now ACTIVE (no longer greyed). Passages/Factures/Catalogue remain greyed.
// ---------------------------------------------------------------------------

it('admin sidebar has greyed nav items for Factures/Catalogue with aria-disabled', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);

    // Plan 02-03: Passages is now ACTIVE. At least 2 greyed items remain (Factures, Catalogue).
    // Note: mobile bottom-nav also has aria-disabled="true" on Factures, so count ≥ 2.
    $content = $response->content();
    expect(substr_count($content, 'aria-disabled="true"'))->toBeGreaterThanOrEqual(2);

    // bientôt badge appears at least 2 times (Factures + Catalogue in sidebar)
    expect(substr_count($content, 'bientôt'))->toBeGreaterThanOrEqual(2);

    // Nav items still present (Clients + Passages are active, Factures + Catalogue greyed)
    $response->assertSee('Clients');
    $response->assertSee('Passages');
    $response->assertSee('Factures');
    $response->assertSee('Catalogue');
});

// ---------------------------------------------------------------------------
// Test 15 — admin sidebar Tableau de bord is the active item
// ---------------------------------------------------------------------------

it('admin sidebar Tableau de bord is the active item with active state class', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Tableau de bord');

    // The active dashboard link should have the bg-white/10 active state
    $content = $response->content();
    expect($content)->toContain('bg-white/10');
});

// ---------------------------------------------------------------------------
// Test 16 — admin topbar shows greyed 'Nouveau passage' button
// ---------------------------------------------------------------------------

it('admin topbar shows a disabled Nouveau passage button', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Nouveau passage');

    $content = $response->content();
    // Button must be disabled or aria-disabled
    $hasDisabled = str_contains($content, 'disabled')
        || str_contains($content, 'aria-disabled')
        || str_contains($content, 'cursor-not-allowed');

    expect($hasDisabled)->toBeTrue();
});

// ---------------------------------------------------------------------------
// Test 17 — admin shell uses layouts/admin.blade.php (grid layout present)
// ---------------------------------------------------------------------------

it('admin shell renders the lg:grid lg:grid-cols-[16rem_1fr] layout from admin.blade.php', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('lg:grid lg:grid-cols-[16rem_1fr]', false);
});

// ---------------------------------------------------------------------------
// Test 18 — admin shell has user pill with Pierre's name + Pisciniste role
// ---------------------------------------------------------------------------

it('admin shell has user pill with Pierre ADAM and Pisciniste role', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Pierre ADAM');
    $response->assertSee('Pisciniste');
});
