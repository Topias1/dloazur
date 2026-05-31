<?php

use App\Models\Client;
use App\Models\Diagnostic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\LaravelPdf\Facades\Pdf;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────────────────────

/**
 * Crée un diagnostic complet avec recommandations + disclaimer accepté.
 */
function makeCompletedDiagnostic(array $overrides = []): Diagnostic
{
    return Diagnostic::factory()->create(array_merge([
        'disclaimer_accepted_at' => now(),
        'created_via'            => 'wizard',
        'type_probleme'          => 'eau trouble',
        'mesures'                => ['ph' => '7.2', 'chlore' => '1.5', 'alcalinite' => '100'],
        'recommandations'        => [
            ['param' => 'pH', 'current' => '7.2', 'target' => '7.4', 'product' => 'pH+', 'dose' => '150 g', 'note' => 'Ajouter devant les buses de refoulement'],
            ['param' => 'Chlore libre', 'current' => '1.5', 'target' => '2.0', 'product' => 'Chlore choc', 'dose' => '200 g', 'note' => 'Attendre 30 min après pH'],
        ],
    ], $overrides));
}

// ──────────────────────────────────────────────────────────────────────────────
// D-06 — Session gate : accès 403 sans session
// ──────────────────────────────────────────────────────────────────────────────

it('anonymous PDF access returns 403 when the diagnostic ID is not in the session', function () {
    $diagnostic = makeCompletedDiagnostic();

    // Aucune session → 403 (D-06 : prévient l'énumération séquentielle)
    $this->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(403);
});

it('session-gated PDF rejects sequential ID enumeration of unowned diagnostics', function () {
    $diagnosticA = makeCompletedDiagnostic();
    $diagnosticB = makeCompletedDiagnostic();

    // Visiteur avec l'id A en session ne peut PAS télécharger le PDF de B
    $this->withSession(['diagnostic_ids' => [$diagnosticA->id]])
        ->get("/diagnostic/{$diagnosticB->id}/pdf")
        ->assertStatus(403);
});

// ──────────────────────────────────────────────────────────────────────────────
// D-06 — Session gate : accès 200 avec session valide
// ──────────────────────────────────────────────────────────────────────────────

it('anonymous PDF access returns 200 when the diagnostic ID is in the session', function () {
    $fake = Pdf::fake();

    $diagnostic = makeCompletedDiagnostic();

    $this->withSession(['diagnostic_ids' => [$diagnostic->id]])
        ->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(200);

    $fake->assertRespondedWithPdf(fn ($pdf) => $pdf->viewName === 'pdf.diagnostic-report');
});

// ──────────────────────────────────────────────────────────────────────────────
// Req8 — Contenu du PDF : action plan + disclaimer
// ──────────────────────────────────────────────────────────────────────────────

it('a completed diagnostic can be downloaded as a PDF (Req8)', function () {
    $fake = Pdf::fake();

    $diagnostic = makeCompletedDiagnostic();

    $this->withSession(['diagnostic_ids' => [$diagnostic->id]])
        ->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(200);

    // Le PDF utilise la bonne vue et transmet le bon diagnostic
    $fake->assertRespondedWithPdf(fn ($pdf) => $pdf->viewName === 'pdf.diagnostic-report'
        && $pdf->viewData['diagnostic']->id === $diagnostic->id);
});

it('the generated PDF contains the disclaimer text (DIAG-03 + SPEC Req 8)', function () {
    $diagnostic = makeCompletedDiagnostic();

    // Render la vue directement pour vérifier le contenu HTML (DomPDF rend le Blade → HTML)
    $html = view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])->render();

    // Le texte exact du disclaimer doit être présent (DIAG-03 / SPEC Req 8)
    // La vue PDF est un document HTML brut — texte statique non escapé
    expect($html)
        ->toContain('titre indicatif')
        ->toContain('jugement')
        ->toContain("Conditions d'utilisation")
        ->toContain('responsabilité');
});

it('the generated PDF contains the ordered action plan steps', function () {
    $diagnostic = makeCompletedDiagnostic();

    $html = view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])->render();

    // Le plan d'action est présent avec les données de recommandations
    expect($html)
        ->toContain("Plan d'action ordonn")
        ->toContain('pH')
        ->toContain('pH+')
        ->toContain('Chlore choc');
});

it('the generated PDF contains pool info and measurements', function () {
    $diagnostic = makeCompletedDiagnostic(['type_probleme' => 'eau verte']);

    $html = view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])->render();

    expect($html)
        ->toContain('eau verte')
        ->toContain('7.2')  // pH mesure
        ->toContain('Votre diagnostic piscine');
});

it('the generated PDF contains the safety block (ambre)', function () {
    $diagnostic = makeCompletedDiagnostic();

    $html = view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])->render();

    expect($html)
        ->toContain('Précautions de sécurité')
        ->toContain('Ne jamais mélanger les produits');
});

it('the generated PDF contains the footer with WhatsApp number and professional notice', function () {
    $diagnostic = makeCompletedDiagnostic();

    $html = view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])->render();

    expect($html)
        ->toContain('Pierre ADAM')
        ->toContain('0696 94 00 54')
        ->toContain("Ce document ne remplace pas l'avis d'un professionnel");
});

// ──────────────────────────────────────────────────────────────────────────────
// D-06 — Auth client : accès PDF par client_id (bypass session)
// ──────────────────────────────────────────────────────────────────────────────

it('authenticated client can download their own diagnostic PDF', function () {
    $fake = Pdf::fake();

    $client     = Client::factory()->create();
    $diagnostic = makeCompletedDiagnostic(['client_id' => $client->id]);

    // Authentifié via guard clients, pas de session diagnostic_ids
    $this->actingAs($client, 'clients')
        ->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(200);

    $fake->assertRespondedWithPdf(fn ($pdf) => $pdf->viewName === 'pdf.diagnostic-report');
});

it('authenticated client cannot download another client diagnostic PDF', function () {
    $client1    = Client::factory()->create();
    $client2    = Client::factory()->create();
    $diagnostic = makeCompletedDiagnostic(['client_id' => $client1->id]);

    // Client2 tente d'accéder au diagnostic de Client1 → 403
    $this->actingAs($client2, 'clients')
        ->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(403);
});

it('authenticated client cannot download anonymous (null client_id) diagnostic without session', function () {
    $client     = Client::factory()->create();
    $diagnostic = makeCompletedDiagnostic(['client_id' => null]);

    // Le diagnostic est anonyme (pas de client_id), client authentifié sans session → 403
    $this->actingAs($client, 'clients')
        ->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(403);
});
