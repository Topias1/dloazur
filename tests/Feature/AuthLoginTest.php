<?php

/**
 * AuthLoginTest — Plan 01-05 Task 1 behavior contract (RED).
 *
 * Verifies AUTH-01: Fortify login + logout + password-reset-request flow.
 * Covers: D-03 (Fortify headless), D-17 (redirect to /admin),
 *         D-20 (logout via Fortify), RESEARCH §Pattern 2 + Pitfall 8 (rate limit).
 */

use App\Models\User;
use Database\Seeders\PierreSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 5 — GET /login renders the styled view with required UI-SPEC copy
// ---------------------------------------------------------------------------

it('GET /login renders the styled view with required copy', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Bon retour, Pierre.');
    $response->assertSee('Se connecter');
    $response->assertSee('Espace pro');
    $response->assertSee('Espace client');
    $response->assertSee('Rester connecté sur ce téléphone');
    $response->assertSee('Données hébergées en Europe');
    $response->assertSee('Confidentialité');
    $response->assertSee('Chaque passage, gardé en mémoire.');
});

// ---------------------------------------------------------------------------
// Test 6 — login form posts to the Fortify route with CSRF token
// ---------------------------------------------------------------------------

it('login form posts to the Fortify route with CSRF token', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    // The form action must resolve to the login route
    $response->assertSee('action="'.route('login').'"', false);
    // CSRF token field must be present
    $response->assertSee('_token');
});

// ---------------------------------------------------------------------------
// Test 7 — POST /login with valid credentials redirects to /admin
// ---------------------------------------------------------------------------

it('POST /login with valid Pierre credentials redirects to /admin', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    $response = $this->post('/login', [
        'email'    => 'pierre@dloazurtest.local',
        'password' => 'correct-horse-battery-staple',
    ]);

    $response->assertRedirect('/admin');
    $this->assertAuthenticatedAs($pierre);
});

// ---------------------------------------------------------------------------
// Test 8 — POST /login with wrong password returns to /login with error
// ---------------------------------------------------------------------------

it('POST /login with wrong password returns to /login with error message', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new PierreSeeder())->run();

    // Visit GET /login first so that _previous.url is set in the session.
    // Without it, ValidationException redirects back to '/' (no referer).
    $this->get('/login');

    $response = $this->post('/login', [
        'email'    => 'pierre@dloazurtest.local',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');

    // Follow the redirect and verify the French error message renders in the view
    $followedResponse = $this->followRedirects($response);
    $followedResponse->assertSee('E-mail ou mot de passe incorrect.');
});

// ---------------------------------------------------------------------------
// Test 9 — login throttle kicks in after 5 failed attempts
// ---------------------------------------------------------------------------

it('login throttle kicks in after 5 failed attempts', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new PierreSeeder())->run();

    $email = 'pierre@dloazurtest.local';
    $ip    = '127.0.0.1';

    // Clear any pre-existing throttle keys
    $throttleKey = mb_strtolower($email).'|'.$ip;
    RateLimiter::clear($throttleKey);

    // Make 6 failed login attempts with the same IP
    for ($i = 1; $i <= 6; $i++) {
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post('/login', [
                'email'    => $email,
                'password' => 'wrong-password-'.$i,
            ]);
    }

    // The 6th attempt should be throttled (429 or redirect with throttle error)
    $isThrottled = $response->status() === 429
        || ($response->isRedirect('/login') && str_contains(
            (string) session('errors')?->first('email') ?? '',
            'Trop de tentatives'
        ));

    expect($isThrottled)->toBeTrue();
});

// ---------------------------------------------------------------------------
// Test 10 — GET /forgot-password renders the password reset request view
// ---------------------------------------------------------------------------

it('GET /forgot-password renders the password reset request view', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
    // The view should contain a form posting to the password.email route (rendered as URL)
    $response->assertSee('forgot-password');
    $response->assertSee('Recevoir le lien de réinitialisation');
});

// ---------------------------------------------------------------------------
// Test 11 — POST /logout destroys the session and redirects to /
// ---------------------------------------------------------------------------

it('POST /logout destroys the session and redirects to /', function () {
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new PierreSeeder())->run();

    $pierre = User::where('email', 'pierre@dloazurtest.local')->first();

    // Login first
    $this->actingAs($pierre);

    // Logout
    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});
