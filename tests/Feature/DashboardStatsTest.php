<?php

/**
 * DashboardStatsTest — Plan 02-03 Task 1 behavior contract (RED).
 *
 * Covers dashboard /admin avec 4 stat-cards réelles :
 * - passagesThisWeek, clientsCount, eauASurveiller, aSynchroniser (placeholder 0)
 * - stat-card component états default/offline/warn
 */

use App\Models\Client;
use App\Models\Passage;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — Passages cette semaine = passages de la semaine courante
// ---------------------------------------------------------------------------

it('dashboard affiche un compteur Passages cette semaine qui matche les passages de la semaine courante', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $client = Client::factory()->create();

    // 2 passages cette semaine
    Passage::factory()->create([
        'client_id'  => $client->id,
        'visited_at' => Carbon::now()->startOfWeek()->addDay(1),
    ]);
    Passage::factory()->create([
        'client_id'  => $client->id,
        'visited_at' => Carbon::now()->startOfWeek()->addDay(2),
    ]);
    // 1 passage semaine dernière — ne doit pas être compté
    Passage::factory()->create([
        'client_id'  => $client->id,
        'visited_at' => Carbon::now()->subWeek()->startOfWeek()->addDay(1),
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $content = $response->content();

    // La valeur "2" doit apparaître dans les stat cards (avec éventuels espaces/newlines autour)
    expect(preg_match('/>\s*2\s*</', $content))->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 2 — Clients actifs = nombre total de clients
// ---------------------------------------------------------------------------

it('dashboard affiche Clients actifs = nombre total de clients', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    Client::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $content = $response->content();

    // 5 clients visibles dans les stat cards (avec éventuels espaces/newlines autour)
    expect(preg_match('/>\s*5\s*</', $content))->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 3 — Eau à surveiller = passages avec mesure hors plage soft (D-63)
// ---------------------------------------------------------------------------

it('dashboard affiche Eau à surveiller = passages dont au moins une mesure hors plage', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $client = Client::factory()->create();

    // Passage avec pH hors plage (> 9.0) dans les 30 derniers jours
    Passage::factory()->create([
        'client_id'  => $client->id,
        'ph_avant'   => 10.0,
        'visited_at' => Carbon::now()->subDays(5),
    ]);

    // Passage dans les normes (pH 7.2) — ne doit pas être compté
    Passage::factory()->create([
        'client_id'  => $client->id,
        'ph_avant'   => 7.2,
        'visited_at' => Carbon::now()->subDays(3),
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $content = $response->content();

    // 1 passage hors plage (avec éventuels espaces/newlines autour)
    expect(preg_match('/>\s*1\s*</', $content))->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 4 — stat-card state='offline' applique la classe ambre OKLCH
// ---------------------------------------------------------------------------

it('stat-card state offline applique la classe ambre oklch', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    // La stat-card "À synchroniser" utilise state="offline" → classe OKLCH ambre
    $response->assertSee('text-[oklch(0.5_0.11_72)]', false);
});

// ---------------------------------------------------------------------------
// Test 5 — stat-card state='warn' applique text-danger
// ---------------------------------------------------------------------------

it('stat-card state warn applique text-danger', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $client = Client::factory()->create();
    // Créer un passage hors plage pour forcer state='warn' sur Eau à surveiller
    Passage::factory()->create([
        'client_id'  => $client->id,
        'ph_avant'   => 10.0,
        'visited_at' => Carbon::now()->subDays(5),
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('text-danger', false);
});

// ---------------------------------------------------------------------------
// Test 6 — stat-card state='default' applique text-ink-950
// ---------------------------------------------------------------------------

it('stat-card state default applique text-ink-950', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('text-ink-950', false);
});
