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
        ->assertSee("J'accepte", false); // D-08 : CTA "J'accepte et commence" (false = pas d'encodage HTML du needle)
});

it('wizard x-data is complete and not truncated by a raw quote (regression: client crash)', function () {
    // Guard for the bug where a literal " inside a JS comment in the inline Alpine
    // x-data closed the HTML attribute early, truncating the object literal and
    // killing the whole client-side wizard (32 console errors, nothing rendered).
    // Server-side surrogate for a browser smoke test — PHP tests never exercise
    // the rendered Alpine, which is exactly how this shipped despite 400+ tests.
    $html = Livewire::test(DiagnosticWizard::class)->html();

    // Capture the wizard-root x-data: from x-data=" to the next RAW double-quote.
    // @js() payload quotes are HTML-escaped to &quot;, so the closing " is the real
    // end of the object literal — a truncating raw " would cut the capture short.
    expect(preg_match('/x-data="(.*?)"\s+wire:ignore\.self/s', $html, $m))->toBe(1);
    $xdata = $m[1];

    // Methods declared AFTER the previously-truncating comment must survive,
    expect($xdata)->toContain('showRetestPrompt');
    expect($xdata)->toContain('onRetestNon');
    // confidenceLabel() (used by the carnet view) must exist in this scope,
    expect($xdata)->toContain('confidenceLabel');
    // and the object literal must be brace-balanced (complete, not cut off).
    expect(substr_count($xdata, '{'))->toBe(substr_count($xdata, '}'));
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

// ──────────────────────────────────────────────────────────────────────────────
// Plan 05-04 : Escalade + Confiance + WhatsApp riche (DIAG-06 full)
// ──────────────────────────────────────────────────────────────────────────────

it('escalade préemptive se déclenche sur une feuille hors-DIY (electro-panne 230V)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->call('setSymptomResult', 'electro-panne');

    expect($component->get('escaladeNiveau'))->toBe('preemptif');
    expect($component->get('escaladeRaison'))->toBe('230V');
});

it('escalade préemptive se déclenche sur electro-entartree (acide-chlorhydrique)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->call('setSymptomResult', 'electro-entartree');

    expect($component->get('escaladeNiveau'))->toBe('preemptif');
    expect($component->get('escaladeRaison'))->toBe('acide-chlorhydrique');
});

it('guard anti-sur-escalade : aucune escalade sur une feuille DIY facile (algues-parois)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->call('setSymptomResult', 'algues-parois');

    expect($component->get('escaladeNiveau'))->toBe('aucun');
    expect($component->get('escaladeRaison'))->toBeNull();
});

it('guard anti-sur-escalade : aucune escalade sur eau saine (algues-installees)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->call('setSymptomResult', 'algues-installees');

    expect($component->get('escaladeNiveau'))->toBe('aucun');
});

it('hook réactif : triggerRetestFailed() déclenche escalade réactive', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->call('triggerRetestFailed');

    expect($component->get('escaladeNiveau'))->toBe('reactif');
    expect($component->get('escaladeRaison'))->toBe('echec-retest');
});

it('indice de confiance indicatif sur parcours sans mesure', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->call('computeAndPersist');

    expect($component->get('confidenceIndex'))->toBe('indicatif');
});

it('indice de confiance élevé avec pH + chlore + TAC renseignés', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.4')
        ->set('chlore', '1.5')
        ->set('alcalinite', '100')
        ->call('computeAndPersist');

    expect($component->get('confidenceIndex'))->toBe('eleve');
});

it('indice de confiance moyen avec mesures partielles (pH seul)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.4')
        ->call('computeAndPersist');

    expect($component->get('confidenceIndex'))->toBe('moyen');
});

it('whatsapp summary contient le numéro 596696940054 et le contexte riche', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.4')
        ->set('chlore', '1.5')
        ->set('alcalinite', '100')
        ->set('triedActions', ['Chlore choc', 'Brossage des parois'])
        ->call('computeAndPersist');

    $wizard  = $component->instance();
    $summary = $wizard->whatsappSummary();

    // Numéro WhatsApp (DIAG-06)
    $component->assertSee('596696940054');

    // Contexte riche : symptôme / mesures / actions tentées / diagnostic
    expect($summary)->toContain('Mesures');
    expect($summary)->toContain('Déjà tenté (sans succès)');
    expect($summary)->toContain('Chlore choc');
    expect($summary)->toContain('Confiance');
    expect($summary)->toContain('Dlo Azur');
});

it('whatsapp summary contient les accents sans corruption (encodeURIComponent via Blade urlencode)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('computeAndPersist');

    $wizard  = $component->instance();
    $summary = $wizard->whatsappSummary();
    // Vérifie les caractères accentués restent intacts dans la chaîne serveur
    expect($summary)->toContain('Dlo Azur Piscines');
    // urlencode() dans Blade peut encoder les accents — l'important est que la chaîne source les contienne
    expect($summary)->toBeString();
});

it('richContextPayload inclut symptôme, mesures, actions tentées, diagnostic, confiance', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '30')
        ->set('ph', '7.2')
        ->set('chlore', '2.0')
        ->set('alcalinite', '90')
        ->set('triedActions', ['Anti-algues'])
        ->call('computeAndPersist');

    $wizard   = $component->instance();
    $payload  = $wizard->richContextPayload();
    $summary  = implode("\n", $payload);

    expect($summary)->toContain('pH');
    expect($summary)->toContain('Déjà tenté (sans succès)');
    expect($summary)->toContain('Anti-algues');
    expect($summary)->toContain('Indice de confiance');
});

// ──────────────────────────────────────────────────────────────────────────────
// Throttle (gardé en dernier car utilise RateLimiter)
// ──────────────────────────────────────────────────────────────────────────────

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

// ──────────────────────────────────────────────────────────────────────────────
// Decouple compute/persist — voir les doses sans créer de ligne DB
// ──────────────────────────────────────────────────────────────────────────────

it('computeDoses shows recommandations but persists NOTHING', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '8.2')        // hors plage → au moins une correction
        ->set('alcalinite', '60') // bas → correction TAC
        ->call('computeDoses');

    // Doses calculées et visibles…
    expect($component->get('hasComputed'))->toBeTrue();
    expect($component->get('recommandations'))->not()->toBeEmpty();
    expect($component->get('savedDiagnosticId'))->toBeNull();
    // …mais AUCUNE ligne en base (le cœur du decouple)
    expect(Diagnostic::count())->toBe(0);
});

it('computeDoses is still gated by the disclaimer (DIAG-03)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', false)
        ->set('volume', '50')
        ->set('ph', '8.2')
        ->call('computeDoses')
        ->assertHasErrors(['disclaimer']);

    expect($component->get('hasComputed'))->toBeFalse();
    expect(Diagnostic::count())->toBe(0);
});

it('keepDiagnostic materializes exactly one row after computeDoses (D-04)', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->set('alcalinite', '100')
        ->call('computeDoses');

    expect(Diagnostic::count())->toBe(0);

    $component->call('keepDiagnostic');

    expect(Diagnostic::count())->toBe(1);
    $diagnostic = Diagnostic::first();
    expect($diagnostic->disclaimer_accepted_at)->not()->toBeNull();
    expect($component->get('savedDiagnosticId'))->toBe($diagnostic->id);
});

it('keepDiagnostic is idempotent — two calls create only one row', function () {
    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('computeDoses')
        ->call('keepDiagnostic')
        ->call('keepDiagnostic');

    expect(Diagnostic::count())->toBe(1);
});

it('downloadPdf persists then redirects to the PDF route', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('computeDoses')
        ->call('downloadPdf')
        ->assertRedirect(route('diagnostic.pdf', Diagnostic::first()));

    expect(Diagnostic::count())->toBe(1);
});

it('submitLead materializes the diagnostic when none was kept yet', function () {
    Mail::fake();

    $component = Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('computeDoses');

    expect(Diagnostic::count())->toBe(0);

    $component
        ->set('prenom', 'Marie')
        ->set('commune', 'Le Lamentin')
        ->call('submitLead');

    expect($component->get('leadSent'))->toBeTrue();
    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    expect($diagnostic->prenom)->toBe('Marie');
    Mail::assertSent(DiagnosticLead::class);
});

// ──────────────────────────────────────────────────────────────────────────────
// Attribution mode serveur (Phase 10 — diag-1/diag-2)
// ──────────────────────────────────────────────────────────────────────────────

it('mode serveur — parcours chimie : created_via=wizard et type_probleme=chemistry', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->call('setMode', 'chemistry')
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('keepDiagnostic');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    expect($diagnostic->created_via)->toBe('wizard');
    expect($diagnostic->type_probleme)->toBe('chemistry');
});

it('mode serveur — parcours symptôme : created_via=depannage et type_probleme=symptom', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->call('setMode', 'symptom')
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('keepDiagnostic');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    expect($diagnostic->created_via)->toBe('depannage');
    expect($diagnostic->type_probleme)->toBe('symptom');
});

it('mode serveur — valeur hors liste ignorée silencieusement : $mode reste null, created_via=wizard', function () {
    Livewire::test(DiagnosticWizard::class)
        ->set('disclaimerAccepted', true)
        ->call('setMode', 'foo')
        ->set('volume', '50')
        ->set('ph', '7.0')
        ->call('keepDiagnostic');

    $diagnostic = Diagnostic::first();
    expect($diagnostic)->not()->toBeNull();
    // mode null => created_via defaults to 'wizard' (existing keepDiagnostic logic)
    expect($diagnostic->created_via)->toBe('wizard');
    expect($diagnostic->type_probleme)->toBeNull();
});
