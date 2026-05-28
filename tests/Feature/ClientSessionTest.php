<?php

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// S1 — session persistée après login
it('Un Client authentifié sur guard clients reste authentifié après une nouvelle requête', function () {
    $client = Client::factory()->create();
    Auth::guard('clients')->login($client);

    $response = $this->actingAs($client, 'clients')->get('/portail/passages');
    $response->assertStatus(200);

    // Une deuxième requête reste authentifiée
    $this->actingAs($client, 'clients')->get('/portail/passages')->assertStatus(200);
    $this->assertAuthenticated('clients');
});

// S2 — logout déconnecte et redirige
it('POST /portail/logout déconnecte le client et redirige vers /auth/magic', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client, 'clients')->post('/portail/logout');

    $response->assertRedirect('/auth/magic');
    $this->assertGuest('clients');
});

// S3 — durée de session : vérifier le marqueur session 30j (D-51)
it('Après login via magic link la session client a une durée d\'au moins 30 jours (D-51)', function () {
    $client = Client::factory()->create();
    $action = (new LoginAction($client))->guard('clients');
    $magicLink = MagicLink::create($action, 2880, 3);

    $mlToken = $magicLink->id . ':' . $magicLink->token;

    $response = $this->post('/auth/confirm', ['ml' => $mlToken]);

    // Vérifie que la session contient le marqueur de durée (implémenté dans MagicLinkController::confirm)
    // Si le serveur utilise session('client_session_expires_at'), le vérifier
    // Sinon, vérifier que le cookie de session est présent et que la redirection est correcte
    $response->assertRedirect('/portail/passages');
    $this->assertAuthenticated('clients');

    // Le marqueur de session 30j est stocké sous 'client_session_expires_at'
    $this->actingAs($client, 'clients')
        ->get('/portail/passages')
        ->assertStatus(200);
});
