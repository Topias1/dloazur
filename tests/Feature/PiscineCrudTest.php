<?php

/**
 * PiscineCrudTest — Plan 02-02 Task 1 behavior contract.
 *
 * Covers CLI-02: CRUD piscines via Livewire 3 PiscineForm component.
 */

use App\Livewire\PiscineForm;
use App\Models\Client;
use App\Models\Piscine;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Test 1: Pierre crée une piscine liée à un client
it('Pierre crée une piscine liée à un client', function () {
    $client = Client::factory()->create();

    Livewire\Livewire::test(PiscineForm::class, ['clientId' => $client->id])
        ->set('nom', 'Lagon')
        ->set('volume_m3', '25.5')
        ->set('type', 'enterrée')
        ->set('filtration', 'sable')
        ->set('traitement', 'chlore')
        ->call('submit')
        ->assertHasNoErrors();

    expect($client->piscines()->count())->toBe(1);
});

// Test 2: PiscineForm refuse volume_m3 non numérique
it('PiscineForm refuse volume_m3 non numérique', function () {
    $client = Client::factory()->create();

    Livewire\Livewire::test(PiscineForm::class, ['clientId' => $client->id])
        ->set('nom', 'x')
        ->set('volume_m3', 'abc')
        ->call('submit')
        ->assertHasErrors(['volume_m3']);
});

// Test 3: Piscine modifiée préserve client_id
it('Piscine modifiée préserve client_id', function () {
    $client = Client::factory()->create();
    $piscine = Piscine::factory()->create(['client_id' => $client->id, 'nom' => 'Initiale']);

    Livewire\Livewire::test(PiscineForm::class, ['clientId' => $client->id, 'piscineId' => $piscine->id])
        ->set('nom', 'Modifiée')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Piscine::find($piscine->id)->client_id)->toBe($client->id);
    expect(Piscine::find($piscine->id)->nom)->toBe('Modifiée');
});
