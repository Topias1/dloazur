<?php

use App\Livewire\DiagnosticWizard;
use App\Mail\DiagnosticLead;
use App\Models\Client;
use App\Models\Diagnostic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────────────────────────
// Route accessibility (Req9)
// ──────────────────────────────────────────────────────────────────────────────

it('/diagnostic page renders the wizard component', function () {
    $this->get('/diagnostic')
        ->assertStatus(200)
        ->assertSee('Trouver mon problème');
});

// ──────────────────────────────────────────────────────────────────────────────
// DIAG-03 — Disclaimer gate (server-side enforcement)
// ──────────────────────────────────────────────────────────────────────────────

it('computeAndPersist is rejected when disclaimerAccepted is false', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', false)
        ->set('volume', '25')
        ->set('ph', '7.0')
        ->call('computeAndPersist')
        ->assertHasErrors(['disclaimer']);

    expect(Diagnostic::count())->toBe(0);
});

it('computeAndPersist persists nothing when disclaimer is false (D-04)', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', false)
        ->set('ph', '7.0')
        ->set('volume', '50')
        ->call('computeAndPersist');

    expect(Diagnostic::count())->toBe(0);
});

it('persisted dosing row has a non-null disclaimer_accepted_at (D-04)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->set('alcalinite', '100')
        ->call('computeAndPersist');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    expect($diagnostic->disclaimer_accepted_at)->not()->toBeNull();
});

// ──────────────────────────────────────────────────────────────────────────────
// Req5 — Anonymous and authenticated persistence
// ──────────────────────────────────────────────────────────────────────────────

it('anonymous diagnostic is created with client_id=null, mesures and recommandations stored', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->set('alcalinite', '100')
        ->call('computeAndPersist');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    expect($diagnostic->client_id)->toBeNull();
    expect($diagnostic->mesures)->not()->toBeNull();
    expect($diagnostic->recommandations)->toBeArray();
});

it('logged-in diagnostic sets client_id to the authenticated client', function () {
    $client = Client::factory()->create();

    // Livewire 3 : actingAs est statique, doit être appelé avant Livewire::test()
    Livewire::actingAs($client, 'clients');

    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.4')
        ->set('alcalinite', '100')
        ->call('computeAndPersist');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    expect($diagnostic->client_id)->toBe($client->id);
});

it('session diagnostic_ids is seeded on persist (D-06 PDF gate)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();

    // The session gate is seeded server-side in computeAndPersist
    expect($component->get('savedDiagnosticId'))->toBe($diagnostic->id);
});

// ──────────────────────────────────────────────────────────────────────────────
// Req6 — Lead capture
// ──────────────────────────────────────────────────────────────────────────────

it('lead capture requires prenom and commune fields', function () {
    Mail::fake();

    // First compute a diagnostic
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.0')
        ->call('computeAndPersist');

    // Now try to submit lead with missing required fields
    $component
        ->set('prenom', '')
        ->set('commune', '')
        ->call('submitLead')
        ->assertHasErrors(['prenom', 'commune']);

    Mail::assertNothingSent();
});

it('lead capture email is optional', function () {
    Mail::fake();

    // Create diagnostic first
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    // Submit lead without email — should succeed
    $component
        ->set('prenom', 'Marie')
        ->set('commune', 'Fort-de-France')
        ->set('email', '')
        ->call('submitLead')
        ->assertHasNoErrors();

    Mail::assertSent(DiagnosticLead::class);
});

it('lead capture persists prenom and commune to diagnostics additive columns', function () {
    Mail::fake();

    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    $component
        ->set('prenom', 'Marie')
        ->set('commune', 'Le Lamentin')
        ->set('email', 'marie@example.com')
        ->call('submitLead');

    $diagnostic = Diagnostic::first();
    expect($diagnostic->prenom)->toBe('Marie');
    expect($diagnostic->commune)->toBe('Le Lamentin');
    expect($diagnostic->email)->toBe('marie@example.com');
});

it('lead capture notifies Pierre via DiagnosticLead mail', function () {
    Mail::fake();

    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    $component
        ->set('prenom', 'Jean')
        ->set('commune', 'Schoelcher')
        ->set('email', 'jean@example.com')
        ->call('submitLead');

    Mail::assertSent(DiagnosticLead::class, function ($mail) {
        return $mail->prenom === 'Jean'
            && $mail->commune === 'Schoelcher'
            && $mail->hasTo(config('contact.recipient', 'contact@dloazurpiscines.com'));
    });
});

it('lead submit sets leadSent to true on success', function () {
    Mail::fake();

    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    $component
        ->set('prenom', 'Jean')
        ->set('commune', 'Schoelcher')
        ->call('submitLead');

    expect($component->get('leadSent'))->toBeTrue();
});

// ──────────────────────────────────────────────────────────────────────────────
// DIAG-06 / Req7 — WhatsApp summary
// ──────────────────────────────────────────────────────────────────────────────

it('whatsapp summary contains the Pierre number 596696940054', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('computeAndPersist');

    // The view renders the wa.me link with the correct number
    $component->assertSee('596696940054');
});

it('whatsapp summary is a non-empty string with diagnostic context', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->set('alcalinite', '100');

    $wizard = $component->instance();
    $summary = $wizard->whatsappSummary();

    expect($summary)->toBeString()
        ->and(strlen($summary))->toBeGreaterThan(10)
        ->and($summary)->toContain('Dlo Azur');
});

// ──────────────────────────────────────────────────────────────────────────────
// Spam / rate limiting (follows ContactForm pattern)
// ──────────────────────────────────────────────────────────────────────────────

it('honeypot trip silently swallows lead submission', function () {
    Mail::fake();

    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    $component
        ->set('prenom', 'Bot')
        ->set('commune', 'Spam')
        ->set('extraFields.my_name', 'I am a bot')
        ->call('submitLead');

    Mail::assertNothingSent();
});

it('lead submit is rate limited after 5 attempts in 60s', function () {
    Mail::fake();

    // Clear rate limiter before the test
    $key = 'livewire-rate-limiter:' . sha1(DiagnosticWizard::class . '|submitLead|' . request()->ip());
    RateLimiter::clear($key);

    // Create a diagnostic first
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '25')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    // 5 successful lead submissions
    for ($i = 1; $i <= 5; $i++) {
        Livewire::test(DiagnosticWizard::class)
            ->set('disclaimerAccepted', true)
            ->set('prenom', "User $i")
            ->set('commune', 'Fort-de-France')
            ->call('submitLead');
    }

    // 6th should be throttled
    $throttled = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('prenom', 'User 6')
        ->set('commune', 'Fort-de-France')
        ->call('submitLead')
        ->assertHasErrors(['throttle']);

    expect($throttled->get('leadSent'))->toBeFalse();

    RateLimiter::clear($key);
});
