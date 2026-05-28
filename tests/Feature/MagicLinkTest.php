<?php

use App\Mail\MagicLinkMail;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// M1 — formulaire demande de lien
it('GET /auth/magic retourne le formulaire avec input email, CTA et lien WhatsApp', function () {
    $response = $this->get('/auth/magic');

    $response->assertStatus(200)
        ->assertSee('Recevoir mon lien')
        ->assertSee('Espace client')
        ->assertSee('name="email"', false)
        ->assertSee('wa.me', false);
});

// M2 — email existant envoie un magic link
it('POST /auth/magic avec email existant envoie un MagicLinkMail et retourne message générique', function () {
    Mail::fake();
    $client = Client::factory()->create(['email' => 'pierre.client@test.com']);

    $response = $this->post('/auth/magic', ['email' => 'pierre.client@test.com']);

    $response->assertSessionHas('status');
    $status = session('status');
    expect($status)->toContain('Si cet email correspond');

    Mail::assertSent(MagicLinkMail::class, fn ($mail) => $mail->hasTo('pierre.client@test.com'));
});

// M3 — email inconnu → même message, aucun mail (anti-énumération)
it('POST /auth/magic avec email inexistant retourne le même message générique et n\'envoie aucun mail', function () {
    Mail::fake();

    $response = $this->post('/auth/magic', ['email' => 'unknown@test.com']);

    $response->assertSessionHas('status');
    $status = session('status');
    expect($status)->toContain('Si cet email correspond');

    Mail::assertNothingSent();
});

// M4 — rate limiter bloque la 6e tentative
it('Le rate limiter magic-link bloque les tentatives au-delà de 5 par heure depuis la même IP', function () {
    Mail::fake();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/auth/magic', ['email' => "test{$i}@test.com"]);
    }

    $response = $this->post('/auth/magic', ['email' => 'test6@test.com']);

    $response->assertStatus(429);
});

// M5 — GET /auth/confirm ne consomme PAS le token (D-50 SafeLinks)
it('GET /auth/confirm avec token valide affiche la page statique sans incrémenter num_visits', function () {
    $client = Client::factory()->create();
    $action = (new LoginAction($client))->guard('clients');
    $magicLink = MagicLink::create($action, 2880, 3);

    $mlToken = $magicLink->id . ':' . $magicLink->token;
    $numVisitsBefore = $magicLink->fresh()->num_visits ?? 0;

    $response = $this->get('/auth/confirm?ml=' . urlencode($mlToken));

    $response->assertStatus(200)
        ->assertSee('Confirmer ma connexion')
        ->assertSee('method="POST"', false);

    $numVisitsAfter = $magicLink->fresh()->num_visits ?? 0;
    expect($numVisitsAfter)->toBe($numVisitsBefore);
});

// M6 — POST /auth/confirm avec token valide connecte le client
it('POST /auth/confirm avec token valide connecte le Client sur guard clients et redirige vers /portail/passages', function () {
    $client = Client::factory()->create();
    $action = (new LoginAction($client))->guard('clients');
    $magicLink = MagicLink::create($action, 2880, 3);

    $mlToken = $magicLink->id . ':' . $magicLink->token;

    $response = $this->post('/auth/confirm', ['ml' => $mlToken]);

    $response->assertRedirect('/portail/passages');
    $this->assertAuthenticated('clients');
    expect(\Illuminate\Support\Facades\Auth::guard('clients')->id())->toBe($client->id);
});

// M7 — token invalide → erreur, redirect vers /auth/magic
it('POST /auth/confirm avec token invalide redirige vers /auth/magic avec erreur', function () {
    $response = $this->post('/auth/confirm', ['ml' => 'invalid-token-bidon']);

    $response->assertRedirect('/auth/magic');
    $this->assertGuest('clients');
});

// M8 — token déjà utilisé 3 fois (numMaxVisits dépassé)
it('POST /auth/confirm avec token épuisé (numMaxVisits atteint) retourne une erreur', function () {
    $client = Client::factory()->create();
    $action = (new LoginAction($client))->guard('clients');
    $magicLink = MagicLink::create($action, 2880, 3);

    $mlToken = $magicLink->id . ':' . $magicLink->token;

    // Consommer 3 fois
    for ($i = 0; $i < 3; $i++) {
        \Illuminate\Support\Facades\Auth::guard('clients')->logout();
        $this->post('/auth/confirm', ['ml' => $mlToken]);
    }

    // 4e tentative — doit échouer
    \Illuminate\Support\Facades\Auth::guard('clients')->logout();
    $response = $this->post('/auth/confirm', ['ml' => $mlToken]);

    $response->assertRedirect('/auth/magic');
    $this->assertGuest('clients');
});
