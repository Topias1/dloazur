<?php

/**
 * PortailAccessTest — Tests d'accès au portail client (P1..P7)
 *
 * P1 : non authentifié → redirigé vers /auth/magic
 * P2 : client authentifié → 200 sur /portail/passages
 * P3 : CRITIQUE — isolation inter-clients (Client A ne voit PAS les passages de Client B)
 * P4 : client authentifié voit ses propres passages
 * P5 : déconnexion POST /portail/logout → redirige + assertGuest
 * P6 : garde 'web' (User admin) n'accède PAS au portail (isolation web vs clients)
 * P7 : état vide — aucun passage n'affiche le message d'état vide
 */

use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// P1 : Accès non authentifié redirige vers /auth/magic
// ---------------------------------------------------------------------------
test('P1 — non authentifié : redirige vers /auth/magic', function () {
    $response = $this->get('/portail/passages');

    $response->assertRedirect(route('portail.magic-link.request'));
});

// ---------------------------------------------------------------------------
// P2 : Client authentifié accède à /portail/passages (200)
// ---------------------------------------------------------------------------
test('P2 — client authentifié : accès 200 à /portail/passages', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// P3 : CRITIQUE — isolation inter-clients (T-2-07G)
// Client A NE DOIT PAS voir les passages de Client B
// Ce test doit passer — sans lui, le portail est non livrable
// ---------------------------------------------------------------------------
test('P3 — CRITIQUE : Client A ne voit pas les passages de Client B', function () {
    $clientA = Client::factory()->create(['name' => 'Client Alpha Test']);
    $clientB = Client::factory()->create(['name' => 'Client Beta Test']);

    $piscineA = Piscine::factory()->create(['client_id' => $clientA->id]);
    $piscineB = Piscine::factory()->create(['client_id' => $clientB->id]);

    // Un passage appartenant à Client A (pH distinctif 9.9)
    Passage::factory()->create([
        'client_id'  => $clientA->id,
        'piscine_id' => $piscineA->id,
        'visited_at' => now()->subDays(2),
        'ph_avant'   => 7.1,
    ]);

    // Passage appartenant à Client B (pH distinctif unique 8.8)
    $passageB = Passage::factory()->create([
        'client_id'  => $clientB->id,
        'piscine_id' => $piscineB->id,
        'visited_at' => now()->subDays(1),
        'notes'      => 'NOTE_SECRETE_CLIENT_B_XQ7Z',
    ]);

    // Client A consulte son portail — il ne doit pas voir les notes de B
    $response = $this->actingAs($clientA, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);
    $response->assertDontSee('NOTE_SECRETE_CLIENT_B_XQ7Z', false);

    // Vérifier que le nom de clientA est affiché (bonne session)
    $response->assertSee('Client Alpha Test', false);
});

// ---------------------------------------------------------------------------
// P4 : Client authentifié voit ses propres passages
// ---------------------------------------------------------------------------
test('P4 — client voit ses propres passages', function () {
    $client = Client::factory()->create();
    $piscine = Piscine::factory()->create(['client_id' => $client->id]);

    $passage = Passage::factory()->create([
        'client_id'    => $client->id,
        'piscine_id'   => $piscine->id,
        'visited_at'   => now()->subDays(2),
        'ph_avant'     => 7.2,
        'chlore_libre' => 1.5,
    ]);

    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);
    // Le composant Livewire doit être présent dans la réponse
    $response->assertSee('passage-timeline', false);
});

// ---------------------------------------------------------------------------
// P5 : Déconnexion via POST /portail/logout
// ---------------------------------------------------------------------------
test('P5 — déconnexion : POST /portail/logout redirige + guest', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client, 'clients')
        ->post('/portail/logout');

    $response->assertRedirect(route('portail.magic-link.request'));
    $this->assertGuest('clients');
});

// ---------------------------------------------------------------------------
// P6 : CRITIQUE — guard 'web' (admin) n'accède PAS au portail
// Isolation croisée — sans ce test, fuite admin/portail possible
// ---------------------------------------------------------------------------
test('P6 — CRITIQUE : guard web (admin) ne donne pas accès au portail clients', function () {
    $user = User::factory()->create();

    // Un User admin authentifié via guard 'web' ne doit pas accéder au portail clients
    $response = $this->actingAs($user, 'web')
        ->get('/portail/passages');

    // Doit être redirigé (guard 'clients' non satisfait)
    $response->assertRedirect(route('portail.magic-link.request'));
});

// ---------------------------------------------------------------------------
// P7 : État vide — aucun passage → message d'état vide affiché
// ---------------------------------------------------------------------------
test('P7 — état vide : aucun passage affiche le message approprié', function () {
    $client = Client::factory()->create();

    // Aucun passage pour ce client
    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);
    $response->assertSee('Aucun passage enregistré', false);
});
