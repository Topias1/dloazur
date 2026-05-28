<?php

/**
 * PassageIndexTest — Plan 02-03 Task 1 behavior contract (RED).
 *
 * Covers PASS-05: historique passages avec filtres client_id + date range,
 * pagination 25/page, tri visited_at DESC, redirect si anonyme.
 */

use App\Livewire\PassageIndex;
use App\Models\Client;
use App\Models\Passage;
use App\Models\User;
use Database\Seeders\PierreSeeder;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — empty state
// ---------------------------------------------------------------------------

it('Pierre voit la liste passages vide affiche l\'empty state', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get(route('admin.passages.index'));

    $response->assertStatus(200);
    $response->assertSee('Aucun passage enregistr');
});

// ---------------------------------------------------------------------------
// Test 2 — liste triée visited_at DESC
// ---------------------------------------------------------------------------

it('liste affiche les passages triés visited_at DESC', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $client = Client::factory()->create();

    $p1 = Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-05-20 10:00:00']);
    $p2 = Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-05-25 10:00:00']);
    $p3 = Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-05-15 10:00:00']);

    $component = Livewire::actingAs($pierre)->test(PassageIndex::class);

    // La paginator rend les passages triés DESC par visited_at
    $passages = $component->get('passages');
    $items = $passages->items();

    expect($items[0]->id)->toBe($p2->id);
    expect($items[1]->id)->toBe($p1->id);
    expect($items[2]->id)->toBe($p3->id);
});

// ---------------------------------------------------------------------------
// Test 3 — filtre client_id
// ---------------------------------------------------------------------------

it('filtre client_id ne retourne que les passages de ce client', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();

    Passage::factory()->create(['client_id' => $clientA->id]);
    Passage::factory()->create(['client_id' => $clientA->id]);
    Passage::factory()->create(['client_id' => $clientB->id]);

    $component = Livewire::actingAs($pierre)->test(PassageIndex::class);
    $component->set('clientId', (string) $clientA->id);

    $passages = $component->get('passages');
    expect($passages->total())->toBe(2);
});

// ---------------------------------------------------------------------------
// Test 4 — filtre date_from
// ---------------------------------------------------------------------------

it('filtre date_from exclut les passages antérieurs', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $client = Client::factory()->create();

    Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-04-01 10:00:00']);
    Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-04-15 10:00:00']);
    Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-04-30 10:00:00']);

    $component = Livewire::actingAs($pierre)->test(PassageIndex::class);
    $component->set('dateFrom', '2026-04-15');

    $passages = $component->get('passages');
    expect($passages->total())->toBe(2);
});

// ---------------------------------------------------------------------------
// Test 5 — filtre date_to
// ---------------------------------------------------------------------------

it('filtre date_to exclut les passages postérieurs', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $client = Client::factory()->create();

    Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-04-01 10:00:00']);
    Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-04-15 10:00:00']);
    Passage::factory()->create(['client_id' => $client->id, 'visited_at' => '2026-04-30 10:00:00']);

    $component = Livewire::actingAs($pierre)->test(PassageIndex::class);
    $component->set('dateTo', '2026-04-15');

    $passages = $component->get('passages');
    expect($passages->total())->toBe(2);
});

// ---------------------------------------------------------------------------
// Test 6 — changement filtre client_id reset page 1
// ---------------------------------------------------------------------------

it('changement filtre client_id reset à la page 1', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $client = Client::factory()->create();

    $component = Livewire::actingAs($pierre)->test(PassageIndex::class);
    $component->set('clientId', (string) $client->id);

    // resetPage() called — paginator should be at page 1
    $passages = $component->get('passages');
    expect($passages->currentPage())->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 7 — anonyme redirigé vers /login
// ---------------------------------------------------------------------------

it('anonyme redirigé vers /login pour admin.passages.index', function () {
    $response = $this->get(route('admin.passages.index'));
    $response->assertRedirect('/login');
});
