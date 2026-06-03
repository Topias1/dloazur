<?php

/**
 * AgendaTest — Plan 07-02 Task 3 (admin-1).
 *
 * Couvre la dérivation frequence_jour → piscines du jour et les flags « à revoir »
 * issus des notes internes. Le temps est figé via Carbon::setTestNow() pour rendre
 * la dérivation jour-courant déterministe.
 *
 * Test 1 : dérivation du jour — piscine 'lundi' apparaît un lundi, piscine 'mardi' non.
 * Test 2 : flag « à revoir » — passage récent avec notes_privees → assertSee le client.
 * Test 3 : états vides — aucune piscine ni note → messages d'état vide affichés.
 */

use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — Dérivation du jour : piscine 'lundi' apparaît un lundi
// ---------------------------------------------------------------------------

it('agenda affiche la piscine du lundi et masque celle du mardi quand on est lundi', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    // Client A — piscine programmée le lundi
    $clientA = Client::factory()->create(['name' => 'Famille Lundi']);
    Piscine::factory()->create([
        'client_id'     => $clientA->id,
        'nom'           => 'Piscine Lundi',
        'frequence_jour' => 'lundi',
    ]);

    // Client B — piscine programmée le mardi (ne doit pas apparaître)
    $clientB = Client::factory()->create(['name' => 'Famille Mardi']);
    Piscine::factory()->create([
        'client_id'     => $clientB->id,
        'nom'           => 'Piscine Mardi',
        'frequence_jour' => 'mardi',
    ]);

    // Figer la date au lundi 8 juin 2026
    Carbon::setTestNow(Carbon::parse('2026-06-08'));

    try {
        $response = $this->actingAs($admin)->get('/admin/agenda');

        $response->assertOk();
        $response->assertSee('Famille Lundi');
        $response->assertDontSee('Famille Mardi');
    } finally {
        Carbon::setTestNow(null);
    }
});

// ---------------------------------------------------------------------------
// Test 2 — Flag « à revoir » : passage récent porteur d'une note interne
// ---------------------------------------------------------------------------

it('agenda remonte un passage récent avec notes_privees dans le bloc à revoir', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    $client = Client::factory()->create(['name' => 'Client Note Test']);

    // Passage récent (3 jours) avec note interne
    Passage::factory()->create([
        'client_id'    => $client->id,
        'visited_at'   => Carbon::now()->subDays(3),
        'notes_privees' => 'Vérifier pompe filtrante la semaine prochaine.',
    ]);

    $response = $this->actingAs($admin)->get('/admin/agenda');

    $response->assertOk();
    $response->assertSee('Client Note Test');
});

// ---------------------------------------------------------------------------
// Test 3 — États vides : aucune piscine du jour ni note → messages vides
// ---------------------------------------------------------------------------

it('agenda affiche les messages d état vide quand aucune piscine ni note', function () {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();

    $admin = User::where('email', 'admin@dloazurtest.local')->first();

    // Figer la date au lundi — pas de piscines 'lundi' ni de notes en base
    Carbon::setTestNow(Carbon::parse('2026-06-08'));

    try {
        $response = $this->actingAs($admin)->get('/admin/agenda');

        $response->assertOk();
        $response->assertSee('Aucune piscine prévue aujourd\'hui.', false);
        $response->assertSee('Rien à revoir.', false);
    } finally {
        Carbon::setTestNow(null);
    }
});
