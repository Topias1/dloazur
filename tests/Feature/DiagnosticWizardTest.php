<?php

use App\Livewire\DiagnosticWizard;
use App\Models\Diagnostic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────────────────────────
// Route accessibility (Req9 — implemented in Plan 05-05 / DiagnosticRouteTest)
// ──────────────────────────────────────────────────────────────────────────────

it('/diagnostic page renders the wizard component', function () {
    $this->get('/diagnostic')
        ->assertStatus(200)
        ->assertSee('Trouver mon problème');
})->markTestIncomplete('Plan 05-01 Task 1 — route render verified in DiagnosticRouteTest once route+controller exist');

// ──────────────────────────────────────────────────────────────────────────────
// DIAG-03 — Disclaimer gate
// ──────────────────────────────────────────────────────────────────────────────

it('computeAndPersist is rejected when disclaimerAccepted is false', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', false)
        ->call('computeAndPersist')
        ->assertHasErrors(['disclaimer']);
})->markTestIncomplete('Plan 05-03 — computeAndPersist implemented with dose compute; disclaimer gate shape created in 05-01');

it('persisted diagnostic has a non-null disclaimer_accepted_at', function () {
    // Once computeAndPersist is implemented in Plan 05-03, this checks D-04
    $diagnostic = Diagnostic::factory()->create([
        'disclaimer_accepted_at' => now(),
    ]);

    expect($diagnostic->disclaimer_accepted_at)->not()->toBeNull();
})->markTestIncomplete('Plan 05-03 — full computeAndPersist + persistence implemented there');

// ──────────────────────────────────────────────────────────────────────────────
// Req5 — Anonymous diagnostics (client_id=null)
// ──────────────────────────────────────────────────────────────────────────────

it('anonymous diagnostic is created with client_id=null, mesures and recommandations stored', function () {
    // Plan 05-03 implements computeAndPersist; this stub names the expectation
})->markTestIncomplete('Plan 05-03 — anonymous diagnostic persistence with client_id=null');

it('logged-in diagnostic sets client_id to the authenticated client', function () {
    // Plan 05-03 implements auth guard + client link
})->markTestIncomplete('Plan 05-03 — auth(clients) client_id linked on persist');

// ──────────────────────────────────────────────────────────────────────────────
// Req6 — Lead capture
// ──────────────────────────────────────────────────────────────────────────────

it('lead capture requires prenom and commune fields', function () {
    // Plan 05-03 implements submitLead()
})->markTestIncomplete('Plan 05-03 — lead capture submit validation (prenom + commune required)');

it('lead capture email is optional', function () {
    // email is nullable|email per CONTEXT D-03
})->markTestIncomplete('Plan 05-03 — email optional on lead submit');

it('lead capture persists to the diagnostics additive columns', function () {
    // D-03: additive columns prenom/commune/email/site_web on diagnostics table
})->markTestIncomplete('Plan 05-03 — lead persisted to diagnostics.prenom/commune/email/site_web');

it('lead capture notifies Pierre via mail', function () {
    // Follows ContactForm pattern: Mail::fake() + Mail::assertSent(DiagnosticLead::class)
})->markTestIncomplete('Plan 05-03 — DiagnosticLead mailer dispatched on lead submit');

// ──────────────────────────────────────────────────────────────────────────────
// DIAG-06 / Req7 — WhatsApp rich escalation
// ──────────────────────────────────────────────────────────────────────────────

it('whatsapp deep link contains the correct Pierre number and a non-empty pre-filled message', function () {
    // Alpine builds via encodeURIComponent; message contains symptôme + measures + tried-actions
})->markTestIncomplete('Plan 05-04 — rich WhatsApp escalation context (DIAG-06) implemented there');

// ──────────────────────────────────────────────────────────────────────────────
// Spam / rate limiting (follows ContactForm pattern)
// ──────────────────────────────────────────────────────────────────────────────

it('honeypot trip silently swallows lead submission', function () {
    // Mirrors ContactFormTest honeypot pattern
})->markTestIncomplete('Plan 05-03 — honeypot wired to submitLead');

it('lead submit is rate limited after 5 attempts in 60s', function () {
    // Mirrors ContactFormTest rate-limit pattern with throttle key
})->markTestIncomplete('Plan 05-03 — rate limit on submitLead');
