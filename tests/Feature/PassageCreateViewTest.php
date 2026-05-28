<?php

/**
 * PassageCreateViewTest — Plan 02-05 Task 1 (Feature fallback — sans Playwright).
 *
 * Vérifie PASS-01 minimal : la vue /admin/passages/create est rendue et expose
 * les sections requises + l'Alpine binding x-data="passageForm".
 *
 * Les tests Browser (offline flow IDB) sont reportés en Plan QA mobile
 * (Playwright requis en CI — non disponible à cette étape).
 */

use App\Models\User;
use Database\Seeders\PierreSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test A — GET /admin/passages/create rendu pour Pierre (200)
// ---------------------------------------------------------------------------

it('GET /admin/passages/create returns 200 for authenticated operator', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin/passages/create');

    $response->assertStatus(200);
    // assertSeeText strips HTML + decodes entities (Blade escapes apostrophe → &#39;)
    $response->assertSeeText("Mesures de l'eau");
    $response->assertSeeText('Actions menées');
    $response->assertSeeText('Enregistrer le passage');
    $response->assertSeeText('Brouillon sauvegardé automatiquement');
});

// ---------------------------------------------------------------------------
// Test B — Anonyme redirigé vers /login
// ---------------------------------------------------------------------------

it('GET /admin/passages/create redirects anonymous to /login', function () {
    $response = $this->get('/admin/passages/create');

    $response->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Test C — La vue expose x-data="passageForm" et le meta csrf-token
// ---------------------------------------------------------------------------

it('GET /admin/passages/create exposes Alpine passageForm binding and csrf-token meta', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->actingAs($pierre)->get('/admin/passages/create');

    $response->assertStatus(200);
    // Alpine binding présent (false = non-escaped search)
    $response->assertSee('x-data="passageForm', false);
    // Meta CSRF token présente (Pitfall 5)
    $response->assertSee('csrf-token', false);
    // Input photo caméra arrière
    $response->assertSee('capture="environment"', false);
    // Bandeau hors-ligne Alpine
    $response->assertSee('x-show="!online"', false);
    // Section Mot pour le client
    $response->assertSee('Mot pour le client');
});

// ---------------------------------------------------------------------------
// Test D — ?client_id= query string pré-remplit le client dans la vue
// ---------------------------------------------------------------------------

it('GET /admin/passages/create with ?client_id pre-fills the client context', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $client = \App\Models\Client::factory()->create(['name' => 'Famille Durand']);

    $response = $this->actingAs($pierre)->get('/admin/passages/create?client_id=' . $client->id);

    $response->assertStatus(200);
    $response->assertSee('Famille Durand');
    // clientId passé à Alpine via l'attribut x-data
    $response->assertSee((string) $client->id, false);
});
