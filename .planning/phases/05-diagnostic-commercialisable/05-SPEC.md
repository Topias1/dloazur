# Phase 5: Diagnostic Commercialisable — Specification

**Created:** 2026-05-30
**Ambiguity score:** 0.18 (gate: ≤ 0.20)
**Requirements:** 8 locked

## Goal

A visitor (anonymous or logged-in) runs a **free** pool diagnostic — either a water-chemistry wizard (measurements → server-calculated dosing plan) or a "Dépannage rapide" symptom decision tree — accepts a legal disclaimer before any dosing advice, and converts via a lead-capture form, a WhatsApp hand-off to Pierre, and a downloadable PDF. Stripe monetization (DIAG-04) and the full multi-measure history dashboard (DIAG-05) are explicitly deferred to later phases.

## Background

Phase 5 is unbuilt except for the data layer: `app/Models/Diagnostic.php` and migration `2026_05_28_000009_create_diagnostics_table.php`. The table already supports the intended shape — nullable `client_id`/`piscine_id` (anonymous diagnostics allowed), `volume_m3`, `type_probleme`, `mesures` (JSON), `recommandations` (JSON), `disclaimer_accepted_at`, `created_via`. There is **no** controller, route, Livewire component, view, dose-calculation logic, or decision tree today.

The authoritative UX/feature reference is the mockup `diagnostic-dloazur (2).html` (a Next.js/React export). The project stack is Laravel 13 + Livewire 3 + Alpine + Tailwind 4; the mockup is a feature/UX reference only, not an implementation target. Wizard / decision-tree interactivity is client-side (Alpine), dose calculation is server-side (DIAG-02), PDF uses the in-stack `spatie/laravel-pdf` (DomPDF driver).

ROADMAP success criteria (4) and DIAG-01..05 frame the phase; this spec narrows DIAG-04 and DIAG-05 out of scope per the interview.

## Requirements

1. **Disclaimer gate (DIAG-03)**: A legal disclaimer is shown and must be explicitly accepted before any dosing/chemical advice is displayed.
   - Current: Only a `disclaimer_accepted_at` column exists; no UI, no gate
   - Target: The wizard's first step (and any flow that yields dosing advice) presents the disclaimer ("Conseils indicatifs — En cas de doute, contactez un professionnel"); the user must explicitly accept; `disclaimer_accepted_at` is recorded on the persisted `Diagnostic`
   - Acceptance: Advancing to any screen that shows a dose or chemical product without an explicit accept action is impossible; a persisted diagnostic that contains dosing recommendations always has a non-null `disclaimer_accepted_at`

2. **Water-chemistry wizard — inputs (DIAG-01 part A)**: A two-step wizard collects pool info then water measurements.
   - Current: No wizard exists
   - Target: Step 1 (pool info) — Volume (m³) OR size inputs (surface + average depth) that derive volume, `Type de filtration` (sable/verre, cartouche, poche), `Piscine au sel ?` (électrolyse). Step 2 (measurements) — pH, chlore libre, alcalinité (TAC), stabilisant, taux de sel. Numeric inputs validated (e.g. "ex: 7.4", "les valeurs doivent être numériques")
   - Acceptance: A user can complete both steps with valid numeric entries; non-numeric entries are rejected with a visible error; volume is available to the dose engine either directly or derived from surface+depth

3. **Server-side dose engine (DIAG-02)**: A personalized, quantified action plan is computed server-side from volume + measurements.
   - Current: No dose logic exists; `recommandations` JSON is unused
   - Target: Server computes per-issue recommendation cards (Problème / Étapes / Dosage / Produit) scaled to bassin volume, using the dose formulas extracted verbatim from the mockup as the validated baseline (e.g. "100 g abaisse 0.1 pH pour 10 m³"; chlore choc via hypochlorite de calcium; sel pour viser 4000 ppm; floculant ~1 L/100 m³; TAC+ bicarbonate de sodium; anti-algues; séquestrant de métaux). Calculation MUST run server-side and never be exposed as client JS
   - Acceptance: For a known volume + measurement set, the returned doses match the mockup-baseline formulas; inspecting client-delivered JS reveals no dose formula/coefficients; results render as Problème/Étapes/Dosage/Produit cards

4. **"Dépannage rapide" decision tree (DIAG-01 part B)**: A symptom-based troubleshooting flow yields a step-by-step action plan without requiring measurements.
   - Current: No decision tree exists
   - Target: Entry problems — Eau verte, Eau trouble, Eau marron, Eau boueuse après pluie, Problème d'électrolyseur, Manque de débit/flow, Irritation des yeux, Odeur forte de chlore. Branching questions (e.g. "Vois-tu le fond ?", "La filtration fonctionne-t-elle ?", "Apparu après une pluie ?", "Piscine au sel ?"). Electrolyser sub-tree covers défaut de débit (alarme flow), cellule entartrée, électrodes usées / remplacement cellule, manque de sel (<3000 ppm), panne boîtier. Each leaf yields an ordered action plan (with product/dosage guidance where relevant), as structured in the mockup
   - Acceptance: Each of the 8 top-level problems reaches at least one leaf action plan; the electrolyser sub-tree exposes its 5 documented fault leaves; tree content matches the mockup's leaves

5. **Anonymous + save-if-logged-in persistence**: Diagnostics are usable anonymously and linked to a client account when logged in.
   - Current: Nullable `client_id` supports this at the schema level; no save path exists
   - Target: Any visitor can complete a diagnostic without auth (persisted with `client_id = null`); an authenticated client's diagnostic is persisted with their `client_id`; `mesures` and `recommandations` JSON are stored
   - Acceptance: Completing a diagnostic while logged out creates a `Diagnostic` row with null `client_id`; completing while logged in sets `client_id` to the authenticated client; both store `mesures` + `recommandations`

6. **Lead-capture form**: A coordinates form captures a recontact lead.
   - Current: No lead capture exists
   - Target: "Vos coordonnées" form — Prénom (required), Commune (required), Email (optional), Site web (optional) — persisted/associated with the diagnostic so Pierre can recontact
   - Acceptance: Submitting with Prénom + Commune persists the lead tied to the diagnostic; missing required fields are rejected with visible errors; email when provided is format-validated

7. **WhatsApp hand-off**: A one-tap WhatsApp link pre-filled with the diagnostic summary, to Pierre's number.
   - Current: None
   - Target: A "Contacter un expert sur WhatsApp" action opens WhatsApp to `0696 94 00 54` with a pre-filled message summarizing the diagnostic ("Voici mon diagnostic réalisé via votre application : …")
   - Acceptance: The action produces a valid `wa.me`/WhatsApp deep link to the correct number with a non-empty pre-filled message reflecting the diagnostic result

8. **PDF report**: The personalized diagnostic is downloadable as a PDF.
   - Current: None
   - Target: A PDF rendering the diagnostic (pool info, measurements/problem, action plan with doses/products, disclaimer) generated via `spatie/laravel-pdf` (DomPDF driver, already in stack)
   - Acceptance: A completed diagnostic produces a downloadable PDF containing the action plan and the disclaimer text; generation succeeds on the DomPDF driver (no Node/Chrome dependency)

## Boundaries

**In scope:**
- Legal disclaimer acceptance gate before dosing advice (DIAG-03)
- Water-chemistry wizard: pool-info + measurements steps (DIAG-01)
- Server-side dose engine producing quantified action-plan cards (DIAG-02), formulas = mockup baseline
- "Dépannage rapide" symptom decision tree incl. electrolyser sub-tree (DIAG-01)
- Anonymous diagnostics + linking to client account when logged in
- Lead-capture form (Prénom, Commune, Email, Site web)
- WhatsApp hand-off with pre-filled diagnostic summary to 0696 94 00 54
- Downloadable PDF report of the diagnostic
- Persisting `Diagnostic` rows (`mesures`, `recommandations`, `disclaimer_accepted_at`, `created_via`)

**Out of scope:**
- **Stripe monetization (DIAG-04)** — abonnement particulier (piste A) + premium upsell (piste B) — deferred to its own later phase; the mockup is a free diagnostic and Stripe adds significant scope. No paywall, no premium gating, no Stripe scaffold in Phase 5.
- **Full multi-measure history dashboard (DIAG-05)** — "Historique de tes visites", measurement-evolution charts, "Rappels d'entretien à venir" — deferred. Phase 5 persists diagnostics but does not build the history/evolution UI or reminders.
- **Authenticating/validating the dose chemistry** — Pierre signs off on the mockup-baseline formulas + disclaimer wording before launch; Phase 5 implements the baseline, it does not author new chemistry.
- **Native pool/piscine CRUD integration beyond linking** — reusing `piscine_id` is allowed but no new pool-management UI is built here.
- **Offline support** — the diagnostic is online-only (unlike the Phase 2 passage saisie).

## Constraints

- Dose calculation MUST be server-side (DIAG-02 / success criterion #2) — formulas and coefficients must never appear in client-delivered JS.
- Dose formulas + disclaimer copy are the **mockup-baseline, pending Pierre's pre-launch validation** — acceptance is tied to the mockup values; a launch gate requires Pierre's sign-off on the chemistry and legal wording (liability: chemical dosing advice).
- Wizard / decision-tree state is client-side via Alpine (Livewire is acceptable for server round-trips like the dose computation submit, but step navigation should not require a network round-trip per step).
- PDF generation uses `spatie/laravel-pdf` with the **DomPDF driver** only (Laravel Cloud serverless — no Node/Chrome/Browsershot).
- Reuse the existing `Diagnostic` model + `diagnostics` table as-is where possible; any schema change must be an additive migration.
- WhatsApp number is `0696 94 00 54` (Pierre); verify before launch.

## Acceptance Criteria

- [ ] Dosing advice is unreachable until the disclaimer is explicitly accepted; persisted diagnostics with doses have non-null `disclaimer_accepted_at`
- [ ] The water-chemistry wizard collects pool info (volume or surface+depth, filtration type, sel?) and the 5 measurements with numeric validation
- [ ] Server returns volume-scaled doses matching the mockup-baseline formulas; no dose formula is present in client JS
- [ ] Results render as Problème / Étapes / Dosage / Produit cards
- [ ] All 8 "Dépannage rapide" top-level problems reach a leaf action plan; the electrolyser sub-tree exposes its 5 fault leaves
- [ ] A logged-out diagnostic persists with `client_id = null`; a logged-in diagnostic persists with the client's `client_id`; both store `mesures` + `recommandations`
- [ ] Lead-capture form persists a lead (Prénom + Commune required) tied to the diagnostic
- [ ] WhatsApp action opens a valid deep link to `0696 94 00 54` with a non-empty pre-filled diagnostic summary
- [ ] A completed diagnostic downloads as a PDF containing the action plan + disclaimer, generated via DomPDF
- [ ] No Stripe/paywall and no multi-measure history dashboard are present (confirmed out of scope)

## Ambiguity Report

| Dimension          | Score | Min  | Status | Notes                                                        |
|--------------------|-------|------|--------|--------------------------------------------------------------|
| Goal Clarity       | 0.88  | 0.75 | ✓      | Free 2-flow diagnostic w/ lead-gen; Stripe deferred          |
| Boundary Clarity   | 0.82  | 0.70 | ✓      | DIAG-04 + full DIAG-05 explicitly out; conversion set locked |
| Constraint Clarity | 0.72  | 0.65 | ✓      | Server-side doses; mockup formulas pending Pierre sign-off   |
| Acceptance Criteria| 0.80  | 0.70 | ✓      | 10 pass/fail criteria                                        |
| **Ambiguity**      | 0.18  | ≤0.20| ✓      |                                                              |

Status: ✓ = met minimum, ⚠ = below minimum (planner treats as assumption)

## Interview Log

| Round | Perspective       | Question summary                                  | Decision locked                                                        |
|-------|-------------------|---------------------------------------------------|------------------------------------------------------------------------|
| 0     | Researcher (scout)| What exists today vs the mockup?                  | Only Diagnostic model+migration; mockup = full feature reference        |
| 1     | Boundary/Simplifier| Stripe paywall vs free lead-gen (DIAG-04)?       | Free diagnostic + lead-gen + WhatsApp; **Stripe deferred** to own phase |
| 1     | Boundary/Simplifier| Which flows ship?                                | **Both**: water-chemistry wizard (doses) + Dépannage rapide tree        |
| 1     | Boundary/Simplifier| How much DIAG-05 persistence?                    | Anonymous + save-if-logged-in; **full history dashboard deferred**      |
| 2     | Boundary Keeper   | Which conversion outputs in scope?                | Lead-capture form + WhatsApp hand-off + PDF report (all three)          |
| 2     | Failure Analyst   | Dose/disclaimer source of truth (liability)?      | Mockup formulas = validated baseline; **Pierre signs off pre-launch**   |

---

*Phase: 05-diagnostic-commercialisable*
*Spec created: 2026-05-30*
*Next step: /gsd:discuss-phase 5 — implementation decisions (how to build what's specified above)*
