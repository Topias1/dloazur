<?php

/**
 * ClientSearchTest — Plan 02-02 Task 1 behavior contract.
 *
 * Covers CLI-03: ILIKE search, pagination, ordering.
 */

use App\Livewire\ClientIndex;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\PierreSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Test 1: Recherche par nom retourne le bon client
it('Recherche par nom retourne le bon client', function () {
    Client::factory()->create(['name' => 'Marie Dupont', 'email' => 'marie@ex.com', 'phone' => '0696112233']);
    Client::factory()->create(['name' => 'Jean Martin', 'email' => 'jean@ex.com', 'phone' => '0696222333']);
    Client::factory()->create(['name' => 'Sophie Leblanc', 'email' => 'sophie@ex.com', 'phone' => '0696333444']);

    Livewire\Livewire::test(ClientIndex::class)
        ->set('search', 'marie')
        ->assertSee('Marie Dupont')
        ->assertDontSee('Jean Martin');
});

// Test 2: Recherche par téléphone fonctionne (ILIKE inclut phone)
it('Recherche par téléphone fonctionne', function () {
    Client::factory()->create(['name' => 'Marie Dupont', 'email' => 'marie@ex.com', 'phone' => '0696112233']);
    Client::factory()->create(['name' => 'Jean Martin', 'email' => 'jean@ex.com', 'phone' => '0696222333']);

    Livewire\Livewire::test(ClientIndex::class)
        ->set('search', '0696112233')
        ->assertSee('Marie Dupont')
        ->assertDontSee('Jean Martin');
});

// Test 3: Recherche sans résultat affiche le message empty avec terme exact
it('Recherche sans résultat affiche le message empty avec terme exact', function () {
    Livewire\Livewire::test(ClientIndex::class)
        ->set('search', 'zzz-inconnu')
        ->assertSee('Aucun résultat pour « zzz-inconnu »');
});

// Test 4: Tri par défaut updated_at DESC
it('Tri par défaut updated_at DESC', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();
    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $clientA = Client::factory()->create(['name' => 'Client Alpha']);
    $clientB = Client::factory()->create(['name' => 'Client Beta']);
    $clientC = Client::factory()->create(['name' => 'Client Gamma']);

    // Touch client B to make it most recent
    sleep(1);
    $clientB->touch();

    $response = $this->actingAs($pierre)->get(route('admin.clients.index'));
    $response->assertStatus(200);

    $content = $response->getContent();
    $posB = strpos($content, 'Client Beta');
    $posA = strpos($content, 'Client Alpha');

    expect($posB)->toBeLessThan($posA, 'Client Beta (most recently updated) doit apparaître avant Client Alpha');
});

// Test 5: Pagination 25/page — 30 clients génèrent 2 pages
it('Pagination 25/page : 30 clients génèrent 2 pages', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();
    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    Client::factory()->count(30)->create();

    $component = Livewire\Livewire::test(ClientIndex::class);
    $component->assertSet('page', 1);

    $response = $this->actingAs($pierre)->get(route('admin.clients.index'));
    $response->assertStatus(200);
    $content = $response->getContent();

    // Either next page link or paginator with ?page=2 should exist
    expect(
        str_contains($content, '?page=2') || str_contains($content, 'page=2')
    )->toBeTrue('30 clients doivent générer un paginator avec page 2');
});

// Test 6: Changement de search réinitialise la page
it('Changement de search réinitialise la page (updatedSearch resetPage)', function () {
    Client::factory()->count(30)->create();

    Livewire\Livewire::test(ClientIndex::class)
        ->set('page', 2)
        ->set('search', 'test')
        ->assertSet('page', 1);
});
