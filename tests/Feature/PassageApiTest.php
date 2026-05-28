<?php

/**
 * PassageApiTest — Plan 02-06 Task 1 behavior contract (RED).
 *
 * Vérifie PASS-03: POST /api/passages UPSERT idempotent sur client_uuid (D-38).
 * Covers: D-38 (upsert conditionnel WHERE status='draft'),
 *         D-40 (409 already_closed),
 *         Pitfall 5 (CSRF exempt api/*),
 *         T-6-01 (passage clos immuable via UPSERT).
 */

use App\Models\Passage;
use App\Models\User;
use Database\Seeders\PierreSeeder;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers locaux
// ---------------------------------------------------------------------------

function makePierre(): User
{
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new PierreSeeder())->run();

    return User::where('email', 'pierre@dloazurtest.local')->first();
}

function passagePayload(string $uuid, array $overrides = []): array
{
    return array_merge([
        'client_uuid' => $uuid,
        'visited_at'  => now()->toIso8601String(),
        'ph_avant'    => 7.2,
        'actions'     => ['Brossage'],
        'notes'       => 'Test passage',
    ], $overrides);
}

// ---------------------------------------------------------------------------
// Test 1 — POST avec un client_uuid frais crée le passage et retourne 200
// ---------------------------------------------------------------------------

it('POST /api/passages avec un client_uuid frais crée le passage et retourne 200 ok=true', function () {
    $pierre = makePierre();
    $uuid   = (string) Str::uuid();

    $this->actingAs($pierre)
         ->postJson('/api/passages', passagePayload($uuid))
         ->assertStatus(200)
         ->assertJson(['ok' => true]);

    expect(Passage::where('client_uuid', $uuid)->count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 2 — POST 2x avec le même client_uuid retourne 200 (UPSERT) sans doublon
// ---------------------------------------------------------------------------

it('POST /api/passages 2x avec le même client_uuid retourne 200 (UPSERT) et ne crée pas de doublon', function () {
    $pierre = makePierre();
    $uuid   = (string) Str::uuid();

    // Premier POST
    $this->actingAs($pierre)
         ->postJson('/api/passages', passagePayload($uuid, ['ph_avant' => 7.2]))
         ->assertStatus(200);

    // Deuxième POST — ph_avant mis à jour
    $this->actingAs($pierre)
         ->postJson('/api/passages', passagePayload($uuid, ['ph_avant' => 7.5]))
         ->assertStatus(200);

    expect(Passage::where('client_uuid', $uuid)->count())->toBe(1);
    expect((float) Passage::where('client_uuid', $uuid)->value('ph_avant'))->toBe(7.5);
});

// ---------------------------------------------------------------------------
// Test 3 — POST sur un passage status != 'draft' retourne 409 already_closed
// ---------------------------------------------------------------------------

it("POST /api/passages sur un passage avec status != 'draft' retourne 409 avec error=already_closed", function () {
    $pierre = makePierre();
    $uuid   = (string) Str::uuid();

    // Insérer directement un passage clos
    Passage::factory()->create([
        'client_uuid' => $uuid,
        'status'      => 'closed',
    ]);

    $this->actingAs($pierre)
         ->postJson('/api/passages', passagePayload($uuid))
         ->assertStatus(409)
         ->assertJson(['error' => 'already_closed'])
         ->assertJsonStructure(['server_state' => ['id', 'client_uuid', 'status']]);
});

// ---------------------------------------------------------------------------
// Test 4 — POST sans client_uuid retourne 422
// ---------------------------------------------------------------------------

it('POST /api/passages sans client_uuid retourne 422', function () {
    $pierre = makePierre();

    $this->actingAs($pierre)
         ->postJson('/api/passages', ['ph_avant' => 7.2])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['client_uuid']);
});

// ---------------------------------------------------------------------------
// Test 5 — POST avec client_uuid non-UUID retourne 422
// ---------------------------------------------------------------------------

it('POST /api/passages avec client_uuid non-UUID retourne 422', function () {
    $pierre = makePierre();

    $this->actingAs($pierre)
         ->postJson('/api/passages', ['client_uuid' => 'not-a-uuid'])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['client_uuid']);
});

// ---------------------------------------------------------------------------
// Test 6 — POST sans auth retourne redirect /login (web guard)
// ---------------------------------------------------------------------------

it('POST /api/passages sans auth retourne redirect /login (web guard)', function () {
    $uuid = (string) Str::uuid();

    $this->postJson('/api/passages', passagePayload($uuid))
         ->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Test 7 — POST valide que actions est bien un array si présent
// ---------------------------------------------------------------------------

it('POST /api/passages valide actions comme array si présent', function () {
    $pierre = makePierre();

    $this->actingAs($pierre)
         ->postJson('/api/passages', [
             'client_uuid' => (string) Str::uuid(),
             'actions'     => 'not-array',
         ])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['actions']);
});

// ---------------------------------------------------------------------------
// Test 8 — L'UPSERT met à jour synced_at à NOW()
// ---------------------------------------------------------------------------

it("Le passage UPSERT met à jour synced_at à NOW()", function () {
    $pierre = makePierre();
    $uuid   = (string) Str::uuid();

    $this->actingAs($pierre)
         ->postJson('/api/passages', passagePayload($uuid))
         ->assertStatus(200);

    expect(Passage::where('client_uuid', $uuid)->first()->synced_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Test 9 — CSRF token absent ne bloque pas (api/* exempt — Pitfall 5)
// ---------------------------------------------------------------------------

it('CSRF token absent ne bloque pas /api/passages (api/* exempté)', function () {
    $pierre = makePierre();
    $uuid   = (string) Str::uuid();

    // Utiliser post() classique (pas postJson) sans csrf pour tester l'exemption
    $this->actingAs($pierre)
         ->post('/api/passages', passagePayload($uuid), [
             'Accept'       => 'application/json',
             'Content-Type' => 'application/json',
         ])
         ->assertStatus(200);
});
