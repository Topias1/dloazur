<?php

use App\Models\Diagnostic;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────────────────────────────────────
// Req8 — PDF generation (Plan 05-05 implements DiagnosticController::pdf())
// ──────────────────────────────────────────────────────────────────────────────

it('a completed diagnostic can be downloaded as a PDF', function () {
    // GET /diagnostic/{id}/pdf returns 200 application/pdf for session-owning visitor
    // PDF body contains the action plan and disclaimer text (SPEC Req 8)
})->markTestIncomplete('Plan 05-05 — PDF route + DomPDF generation implemented there');

it('the generated PDF contains the disclaimer text (DIAG-03 + SPEC Req 8)', function () {
    // PDF must include disclaimer copy regardless of whether it was a symptom or wizard flow
})->markTestIncomplete('Plan 05-05 — PDF disclaimer text validated there');

it('the generated PDF contains the ordered action plan steps', function () {
    // PDF includes Problème / Étapes / Dosage / Produit cards per CONTEXT specifics
})->markTestIncomplete('Plan 05-05 — PDF action plan layout validated there');

// ──────────────────────────────────────────────────────────────────────────────
// DIAG-03 + D-06 — Session-gated anonymous PDF access
// ──────────────────────────────────────────────────────────────────────────────

it('anonymous PDF access returns 403 when the diagnostic ID is not in the session', function () {
    // D-06: session must contain the diagnostic ID; enumeration of other IDs → 403
    $diagnostic = Diagnostic::factory()->create([
        'disclaimer_accepted_at' => now(),
        'created_via'            => 'depannage',
    ]);

    // No session binding → forbidden
    $this->get("/diagnostic/{$diagnostic->id}/pdf")
        ->assertStatus(403);
})->markTestIncomplete('Plan 05-05 — PDF route session gate implemented there (controller + route registered in 05-05)');

it('anonymous PDF access returns 200 when the diagnostic ID is in the session', function () {
    // Store the ID in session via withSession(), verify 200
})->markTestIncomplete('Plan 05-05 — session-gated PDF download verified there');

it('session-gated PDF rejects sequential ID enumeration of unowned diagnostics', function () {
    // Visitor with ID 5 in session cannot download ID 6
})->markTestIncomplete('Plan 05-05 — enumeration attack mitigated via session gate (D-06)');

it('authenticated client can download their own diagnostic PDF', function () {
    // client_id match via auth(clients)->id() bypasses session check
})->markTestIncomplete('Plan 05-05 — auth(clients) client_id match bypasses session gate');

it('authenticated client cannot download another client diagnostic PDF', function () {
    // Different client_id → 403
})->markTestIncomplete('Plan 05-05 — cross-client access blocked by client_id guard');
