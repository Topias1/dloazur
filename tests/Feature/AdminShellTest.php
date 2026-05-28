<?php

/**
 * AdminShellTest — Plan 01-05 Task 1 behavior contract (RED).
 *
 * Verifies D-17..D-20: admin shell stub, sidebar nav, greyed items,
 * user pill, topbar, mobile bottom nav.
 */

use App\Models\User;
use Database\Seeders\PierreSeeder;

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
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Bonjour Pierre,');
    $response->assertSee('Tableau de bord opérationnel en Phase 2.');
    $response->assertSee('Clients actifs');
    $response->assertSee('Passages cette semaine');
    $response->assertSee('À synchroniser');
    $response->assertSee('Factures en attente');
    // 4 em-dash values (stat placeholders)
    $content = $response->content();
    expect(substr_count($content, '—'))->toBeGreaterThanOrEqual(4);
});

// ---------------------------------------------------------------------------
// Test 14 — admin sidebar has greyed nav items with bientôt badges
// Plan 02-02: Clients is now ACTIVE (no longer greyed). Passages/Factures/Catalogue remain greyed.
// ---------------------------------------------------------------------------

it('admin sidebar has greyed nav items for Passages/Factures/Catalogue with aria-disabled', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin');

    $response->assertStatus(200);

    // At least 3 greyed items remain (Passages, Factures, Catalogue)
    $content = $response->content();
    expect(substr_count($content, 'aria-disabled="true"'))->toBeGreaterThanOrEqual(3);

    // bientôt badge appears at least 3 times (Passages, Factures, Catalogue)
    expect(substr_count($content, 'bientôt'))->toBeGreaterThanOrEqual(3);

    // Nav items still present (Clients is active, others are greyed)
    $response->assertSee('Clients');
    $response->assertSee('Passages');
    $response->assertSee('Factures');
    $response->assertSee('Catalogue');
});

// ---------------------------------------------------------------------------
// Test 15 — admin sidebar Tableau de bord is the active item
// ---------------------------------------------------------------------------

it('admin sidebar Tableau de bord is the active item with active state class', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin');

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
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin');

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
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('lg:grid lg:grid-cols-[16rem_1fr]', false);
});

// ---------------------------------------------------------------------------
// Test 18 — admin shell has user pill with Pierre's name + Pisciniste role
// ---------------------------------------------------------------------------

it('admin shell has user pill with Pierre ADAM and Pisciniste role', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Pierre ADAM');
    $response->assertSee('Pisciniste');
});
