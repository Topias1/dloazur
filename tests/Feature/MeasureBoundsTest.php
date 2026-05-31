<?php
use App\Models\User;
use App\Models\Client;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function boundsAdmin(): User {
    putenv('OPERATOR_EMAIL=admin@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=secret');
    (new AdminSeeder())->run();
    return User::where('email','admin@dloazurtest.local')->first();
}

it('rejects an out-of-range pH with 422 instead of crashing the insert (Postgres overflow)', function () {
    $admin = boundsAdmin();
    $client = Client::factory()->create();
    $r = $this->actingAs($admin)->postJson('/api/passages', [
        'client_uuid' => (string) Str::uuid(),
        'client_id'   => $client->id,
        'ph_avant'    => 74, // typo: 7.4 -> 74, overflows decimal(4,2) on PG
        'actions'     => [],
    ]);
    $r->assertStatus(422)->assertJsonValidationErrors(['ph_avant']);
});

it('still accepts realistic field readings (including soft-warning values)', function () {
    $admin = boundsAdmin();
    $client = Client::factory()->create();
    $r = $this->actingAs($admin)->postJson('/api/passages', [
        'client_uuid' => (string) Str::uuid(),
        'client_id'   => $client->id,
        'ph_avant'    => 7.4,
        'chlore_libre'=> 12.5,   // above soft range (0-10) but a real reading -> must pass
        'tac'         => 250,
        'sel_g_l'     => 4.2,
        'actions'     => ['Brossage parois'],
    ]);
    $r->assertStatus(200)->assertJson(['ok'=>true]);
});

it('rejects negative measurements', function () {
    $admin = boundsAdmin();
    $client = Client::factory()->create();
    $this->actingAs($admin)->postJson('/api/passages', [
        'client_uuid' => (string) Str::uuid(),
        'client_id'   => $client->id,
        'ph_avant'    => -1,
        'actions'     => [],
    ])->assertStatus(422)->assertJsonValidationErrors(['ph_avant']);
});
