<?php

/**
 * PassageProduitSyncTest — Plan 07-03 Task 2 behavior contract.
 *
 * Vérifie POST /api/passages/produits :
 * - Test 1 : sync avec quantité → pivot créé avec prix_snapshot
 * - Test 2 : sync sans quantité → quantite NULL, prix_snapshot renseigné
 * - Test 3 : idempotence → second POST remplace l'ensemble du pivot (pas de doublon)
 * - Test 4 : produit_id inexistant → 422
 */

use App\Models\Passage;
use App\Models\Produit;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers locaux
// ---------------------------------------------------------------------------

function makeAdminForProduits(): User
{
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new AdminSeeder())->run();

    return User::where('email', 'admin@dloazurtest.local')->first();
}

function makeProduit(array $overrides = []): Produit
{
    return Produit::create(array_merge([
        'libelle'  => 'Chlore choc 5L',
        'prix_ht'  => '12.50',
        'actif'    => true,
    ], $overrides));
}

function makePassage(string $uuid): Passage
{
    return Passage::create([
        'client_uuid' => $uuid,
        'visited_at'  => now(),
        'status'      => 'draft',
    ]);
}

// ---------------------------------------------------------------------------
// Test 1 — POST avec quantité crée le pivot avec prix_snapshot
// ---------------------------------------------------------------------------

it('POST /api/passages/produits crée le pivot avec prix_snapshot et quantité', function () {
    $admin   = makeAdminForProduits();
    $uuid    = (string) Str::uuid();
    $passage = makePassage($uuid);
    $produit = makeProduit(['prix_ht' => '18.00']);

    $this->actingAs($admin)
         ->postJson('/api/passages/produits', [
             'passage_client_uuid' => $uuid,
             'produits' => [
                 ['produit_id' => $produit->id, 'quantite' => 2.5],
             ],
         ])
         ->assertStatus(200)
         ->assertJson(['ok' => true]);

    $this->assertDatabaseHas('passage_produit', [
        'passage_id'    => $passage->id,
        'produit_id'    => $produit->id,
        'quantite'      => '2.50',
        'prix_snapshot' => '18.00',
    ]);
});

// ---------------------------------------------------------------------------
// Test 2 — Quantité optionnelle : quantite NULL, prix_snapshot renseigné
// ---------------------------------------------------------------------------

it('POST /api/passages/produits sans quantité → quantite NULL et prix_snapshot renseigné', function () {
    $admin   = makeAdminForProduits();
    $uuid    = (string) Str::uuid();
    $passage = makePassage($uuid);
    $produit = makeProduit(['prix_ht' => '9.90']);

    $this->actingAs($admin)
         ->postJson('/api/passages/produits', [
             'passage_client_uuid' => $uuid,
             'produits' => [
                 ['produit_id' => $produit->id],
             ],
         ])
         ->assertStatus(200);

    $this->assertDatabaseHas('passage_produit', [
        'passage_id'    => $passage->id,
        'produit_id'    => $produit->id,
        'quantite'      => null,
        'prix_snapshot' => '9.90',
    ]);
});

// ---------------------------------------------------------------------------
// Test 3 — Idempotence : un second POST remplace l'ensemble du pivot
// ---------------------------------------------------------------------------

it('POST /api/passages/produits idempotent : second appel remplace le pivot sans doublon', function () {
    $admin    = makeAdminForProduits();
    $uuid     = (string) Str::uuid();
    $passage  = makePassage($uuid);
    $produit1 = makeProduit(['libelle' => 'Chlore', 'prix_ht' => '10.00']);
    $produit2 = makeProduit(['libelle' => 'pH-', 'prix_ht' => '8.00']);

    // Premier appel : produit1 uniquement
    $this->actingAs($admin)
         ->postJson('/api/passages/produits', [
             'passage_client_uuid' => $uuid,
             'produits' => [
                 ['produit_id' => $produit1->id, 'quantite' => 1.0],
             ],
         ])
         ->assertStatus(200);

    // Second appel : produit2 uniquement (remplace)
    $this->actingAs($admin)
         ->postJson('/api/passages/produits', [
             'passage_client_uuid' => $uuid,
             'produits' => [
                 ['produit_id' => $produit2->id, 'quantite' => 3.0],
             ],
         ])
         ->assertStatus(200);

    // produit1 doit avoir disparu, produit2 doit être présent
    $this->assertDatabaseMissing('passage_produit', [
        'passage_id' => $passage->id,
        'produit_id' => $produit1->id,
    ]);
    $this->assertDatabaseHas('passage_produit', [
        'passage_id'    => $passage->id,
        'produit_id'    => $produit2->id,
        'quantite'      => '3.00',
        'prix_snapshot' => '8.00',
    ]);

    // Vérifier l'absence de doublon
    expect(\DB::table('passage_produit')->where('passage_id', $passage->id)->count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 4 — produit_id inexistant → 422
// ---------------------------------------------------------------------------

it('POST /api/passages/produits avec produit_id inexistant → 422', function () {
    $admin   = makeAdminForProduits();
    $uuid    = (string) Str::uuid();
    makePassage($uuid);

    $this->actingAs($admin)
         ->postJson('/api/passages/produits', [
             'passage_client_uuid' => $uuid,
             'produits' => [
                 ['produit_id' => 99999, 'quantite' => 1.0],
             ],
         ])
         ->assertStatus(422);
});
