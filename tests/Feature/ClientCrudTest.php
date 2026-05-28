<?php

/**
 * ClientCrudTest — Plan 02-02 Task 1 behavior contract.
 *
 * Covers CLI-01: CRUD clients via Livewire 3 ClientForm component.
 */

use App\Livewire\ClientForm;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\PierreSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Test 1: Pierre voit la liste clients vide avec l'empty state
it("Pierre voit la liste clients vide affiche l'empty state", function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    (new PierreSeeder())->run();
    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $this->actingAs($pierre)
        ->get(route('admin.clients.index'))
        ->assertStatus(200)
        ->assertSee('Aucun client pour l', false); // partial match avoids HTML entity encoding
});

// Test 2: Pierre crée un client via Livewire
it('Pierre crée un client via Livewire', function () {
    Livewire\Livewire::test(ClientForm::class)
        ->set('name', 'Marie Dubois')
        ->set('email', 'marie@ex.com')
        ->set('phone', '0696112233')
        ->set('address', '12 rue de la Plage, Schoelcher')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Client::where('email', 'marie@ex.com')->exists())->toBeTrue();
});

// Test 3: ClientForm refuse un nom vide
it('ClientForm refuse un nom vide', function () {
    Livewire\Livewire::test(ClientForm::class)
        ->set('name', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required']);
});

// Test 4: ClientForm refuse un email mal formé
it('ClientForm refuse un email mal formé', function () {
    Livewire\Livewire::test(ClientForm::class)
        ->set('name', 'x')
        ->set('email', 'pas-un-email')
        ->call('submit')
        ->assertHasErrors(['email']);
});

// Test 5: Pierre modifie un client existant
it('Pierre modifie un client existant', function () {
    $existing = Client::factory()->create(['name' => 'Jean Dupont']);

    Livewire\Livewire::test(ClientForm::class, ['clientId' => $existing->id])
        ->set('name', 'Modifié')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Client::find($existing->id)->name)->toBe('Modifié');
});

// Test 6: Un visiteur non logué est redirigé vers /login
it('Un visiteur non logué est redirigé vers /login sur /admin/clients', function () {
    $this->get(route('admin.clients.index'))
        ->assertRedirect('/login');
});
