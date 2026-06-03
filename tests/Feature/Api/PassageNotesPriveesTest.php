<?php

/**
 * PassageNotesPriveesTest — Plan 07-01 (admin-2, bug notes_privees)
 *
 * Trois invariants à vérifier :
 *   1. Persistance : POST /api/passages avec notes_privees persiste en base après synchro.
 *   2. Vie privée  : la valeur notes_privees n'apparaît JAMAIS dans la sortie rendue du portail client.
 *   3. Idempotence : un second POST avec le même client_uuid met à jour notes_privees
 *                    sans casser l'ON CONFLICT existant (statut reste draft).
 */

use App\Livewire\Portail\PassageTimeline;
use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers locaux
// ---------------------------------------------------------------------------

function makeAdminNP(): User
{
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new AdminSeeder())->run();

    return User::where('email', 'admin@dloazurtest.local')->first();
}

function passageWithNote(string $uuid, string $note): array
{
    return [
        'client_uuid'  => $uuid,
        'visited_at'   => now()->toIso8601String(),
        'ph_avant'     => 7.2,
        'actions'      => ['Brossage'],
        'notes'        => 'Note publique',
        'notes_privees' => $note,
    ];
}

// ---------------------------------------------------------------------------
// Test 1 — La note interne persiste après synchro POST
// ---------------------------------------------------------------------------

it('notes_privees persiste en base après POST /api/passages', function () {
    $admin = makeAdminNP();
    $uuid  = (string) Str::uuid();
    $note  = 'code portail 1234';

    $this->actingAs($admin)
         ->postJson('/api/passages', passageWithNote($uuid, $note))
         ->assertStatus(200)
         ->assertJson(['ok' => true]);

    $this->assertDatabaseHas('passages', [
        'client_uuid'   => $uuid,
        'notes_privees' => $note,
    ]);
});

// ---------------------------------------------------------------------------
// Test 2 — Invariant vie privée : notes_privees absent de la vue portail
// ---------------------------------------------------------------------------

it('notes_privees est absent de la sortie rendue de PassageTimeline (invariant vie privée)', function () {
    $admin = makeAdminNP();

    // Créer un client avec une piscine
    $client = Client::factory()->create(['name' => 'Client Test NP']);
    $piscine = Piscine::factory()->create(['client_id' => $client->id]);

    $uuid = (string) Str::uuid();
    $note = 'code portail 1234';

    // Insérer le passage avec notes_privees
    $this->actingAs($admin)
         ->postJson('/api/passages', array_merge(passageWithNote($uuid, $note), [
             'client_id'  => $client->id,
             'piscine_id' => $piscine->id,
         ]))
         ->assertStatus(200);

    // Rendre la timeline portail réelle comme le client — la note privée doit être absente
    Livewire::actingAs($client, 'clients')
            ->test(PassageTimeline::class)
            ->assertDontSee('code portail 1234');
});

// ---------------------------------------------------------------------------
// Test 3 — Idempotence : second POST met à jour notes_privees (ON CONFLICT)
// ---------------------------------------------------------------------------

it('second POST avec même client_uuid met à jour notes_privees (idempotence ON CONFLICT)', function () {
    $admin = makeAdminNP();
    $uuid  = (string) Str::uuid();

    // Premier POST
    $this->actingAs($admin)
         ->postJson('/api/passages', passageWithNote($uuid, 'note initiale'))
         ->assertStatus(200);

    // Deuxième POST — note modifiée
    $this->actingAs($admin)
         ->postJson('/api/passages', passageWithNote($uuid, 'note mise à jour'))
         ->assertStatus(200);

    // Pas de doublon
    expect(Passage::where('client_uuid', $uuid)->count())->toBe(1);

    // La note est mise à jour
    expect(Passage::where('client_uuid', $uuid)->value('notes_privees'))
        ->toBe('note mise à jour');

    // Le statut reste draft (ON CONFLICT conditionnel WHERE status='draft')
    expect(Passage::where('client_uuid', $uuid)->value('status'))
        ->toBe('draft');
});
