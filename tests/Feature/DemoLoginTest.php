<?php

use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns 404 when the flag is off for client and admin', function () {
    config(['app.demo_login' => false]);

    $this->post(route('portail.demo.client'))->assertNotFound();
    $this->post(route('portail.demo.admin'))->assertNotFound();
});

it('logs in the demo client and provisions data when flag on', function () {
    config(['app.demo_login' => true]);

    $response = $this->post(route('portail.demo.client'));

    $response->assertRedirect(route('portail.passages'));
    $this->assertAuthenticated('clients');

    $client = Client::where('email', 'demo-client@dloazur.test')->first();
    expect($client)->not->toBeNull();
    expect($client->piscines()->count())->toBeGreaterThanOrEqual(1);
    expect($client->passages()->count())->toBeGreaterThanOrEqual(1);
});

it('logs in the demo admin when flag on', function () {
    config(['app.demo_login' => true]);

    $response = $this->post(route('portail.demo.admin'));

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticated('web');

    $admin = User::where('email', 'demo-admin@dloazur.test')->first();
    expect($admin)->not->toBeNull();
    expect($admin->email_verified_at)->not->toBeNull();
});

it('is idempotent for the demo client', function () {
    config(['app.demo_login' => true]);

    $this->post(route('portail.demo.client'));
    $this->post(route('portail.demo.client'));

    expect(Client::where('email', 'demo-client@dloazur.test')->count())->toBe(1);

    $client = Client::where('email', 'demo-client@dloazur.test')->first();
    expect($client->piscines()->count())->toBe(1);
    expect($client->passages()->count())->toBe(4);
});
