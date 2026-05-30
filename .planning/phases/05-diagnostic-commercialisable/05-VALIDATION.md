---
phase: 05
slug: diagnostic-commercialisable
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-05-30
---

# Phase 05 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.
> Source: `05-RESEARCH.md` § Validation Architecture (Pest PHP).

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest PHP 4.7 (already in stack) |
| **Config file** | `phpunit.xml` (root) |
| **Quick run command** | `./vendor/bin/pest --filter DoseEngine` |
| **Full suite command** | `./vendor/bin/pest` |
| **Estimated runtime** | ~30–60 seconds (full suite, current project) |

---

## Sampling Rate

- **After every task commit:** Run `./vendor/bin/pest --filter DoseEngine` (or the plan's nearest unit filter)
- **After every plan wave:** Run `./vendor/bin/pest`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** ~60 seconds

---

## Per-Task Verification Map

> Task IDs are assigned by the planner; rows below are keyed by requirement + target test file so the planner maps each task's `<automated>` verify to a row. Dose formulas (DIAG-02) carry the highest liability — assert against the mockup-baseline values, expert-audited corrections per `05-DIAGNOSTIC-EXPERT-AUDIT.md`.

| Requirement | Behavior | Test Type | Automated Command | Target File | File Exists | Status |
|-------------|----------|-----------|-------------------|-------------|-------------|--------|
| DIAG-02 | pH low dose: volume=50, pH=7.0 → 300 g pH+ | unit | `./vendor/bin/pest --filter DoseEngine` | `tests/Unit/DoseEngineTest.php` | ❌ W0 | ⬜ pending |
| DIAG-02 | pH high dose: volume=50, pH=7.8 → expected g pH- | unit | `./vendor/bin/pest --filter DoseEngine` | `tests/Unit/DoseEngineTest.php` | ❌ W0 | ⬜ pending |
| DIAG-02 | TAC low: volume=50, TAC=60 → expected g bicarbonate | unit | `./vendor/bin/pest --filter DoseEngine` | `tests/Unit/DoseEngineTest.php` | ❌ W0 | ⬜ pending |
| DIAG-02 | Stabilisant >75 → drain calculation | unit | `./vendor/bin/pest --filter DoseEngine` | `tests/Unit/DoseEngineTest.php` | ❌ W0 | ⬜ pending |
| DIAG-02 | Sel <3000 → kg sel calculation | unit | `./vendor/bin/pest --filter DoseEngine` | `tests/Unit/DoseEngineTest.php` | ❌ W0 | ⬜ pending |
| DIAG-02 | Chlore bas <1 → rattrapage dose (~3-4 g/m³, NOT choc — expert P0) | unit | `./vendor/bin/pest --filter DoseEngine` | `tests/Unit/DoseEngineTest.php` | ❌ W0 | ⬜ pending |
| DIAG-02 | Formulas absent from client JS bundle | smoke | `grep -rE "[0-9]+ ?\* ?(volume|m3)" resources/js/` exits non-zero | — | ❌ W0 | ⬜ pending |
| DIAG-01 | All 8 tree top-level problems reach ≥1 leaf | unit | `./vendor/bin/pest --filter DecisionTree` | `tests/Unit/DecisionTreeTest.php` | ❌ W0 | ⬜ pending |
| DIAG-01 | Electrolyser sub-tree exposes 5 fault leaves | unit | `./vendor/bin/pest --filter DecisionTree` | `tests/Unit/DecisionTreeTest.php` | ❌ W0 | ⬜ pending |
| DIAG-01 | Floculant branch: cartouche path never emits word "floculant" | unit | `./vendor/bin/pest --filter DecisionTree` | `tests/Unit/DecisionTreeTest.php` | ❌ W0 | ⬜ pending |
| DIAG-03 | computeAndPersist without disclaimer accept → rejected | feature | `./vendor/bin/pest --filter DiagnosticWizard` | `tests/Feature/DiagnosticWizardTest.php` | ❌ W0 | ⬜ pending |
| DIAG-03 | Persisted diagnostic with doses has non-null `disclaimer_accepted_at` | feature | `./vendor/bin/pest --filter DiagnosticWizard` | `tests/Feature/DiagnosticWizardTest.php` | ❌ W0 | ⬜ pending |
| DIAG-01/Req5 | Anon diagnostic: `client_id=null`, `mesures`+`recommandations` stored | feature | `./vendor/bin/pest --filter DiagnosticWizard` | `tests/Feature/DiagnosticWizardTest.php` | ❌ W0 | ⬜ pending |
| Req5 | Logged-in diagnostic sets `client_id` to auth client | feature | `./vendor/bin/pest --filter DiagnosticWizard` | `tests/Feature/DiagnosticWizardTest.php` | ❌ W0 | ⬜ pending |
| Req6 | Lead-capture persists (Prénom+Commune required) tied to diagnostic | feature | `./vendor/bin/pest --filter DiagnosticWizard` | `tests/Feature/DiagnosticWizardTest.php` | ❌ W0 | ⬜ pending |
| DIAG-06/Req7 | WhatsApp deep link: correct number + non-empty rich context | feature | `./vendor/bin/pest --filter DiagnosticWizard` | `tests/Feature/DiagnosticWizardTest.php` | ❌ W0 | ⬜ pending |
| Req8 | PDF contains action plan + disclaimer, via DomPDF (no Node/Chrome) | feature | `./vendor/bin/pest --filter DiagnosticPdf` | `tests/Feature/DiagnosticPdfTest.php` | ❌ W0 | ⬜ pending |
| DIAG-03/Req8 | Anon PDF access session-gated; enumeration of other IDs rejected | feature | `./vendor/bin/pest --filter DiagnosticPdf` | `tests/Feature/DiagnosticPdfTest.php` | ❌ W0 | ⬜ pending |
| Req9 | `/diagnostic` reachable without auth | feature | `./vendor/bin/pest --filter DiagnosticRoute` | `tests/Feature/DiagnosticRouteTest.php` | ❌ W0 | ⬜ pending |
| DIAG-07 | Carnet local-only: history reads from device store (front) | browser | Pest v4 browser test (or manual) | `tests/Browser/CarnetLocalTest.php` | ❌ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Unit/DoseEngineTest.php` — stubs for DIAG-02 dose formula assertions (mockup baseline + expert P0 corrections)
- [ ] `tests/Unit/DecisionTreeTest.php` — stubs for DIAG-01 tree completeness + floculant branch
- [ ] `tests/Feature/DiagnosticWizardTest.php` — stubs for DIAG-03, Req5, Req6, DIAG-06/Req7
- [ ] `tests/Feature/DiagnosticPdfTest.php` — stubs for Req8 + session-gated access (D-06)
- [ ] `tests/Feature/DiagnosticRouteTest.php` — stub for Req9 public route
- [ ] Pest is already installed — no framework install needed

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| WhatsApp opens to Pierre with pre-filled message on a real device | DIAG-06 / Req7 | `wa.me` deep-link behavior depends on the OS/WhatsApp client; the link *string* is unit-testable but the open is not | On iOS + Android, complete a diagnostic, tap "Contacter un expert", confirm WhatsApp opens to `0696 94 00 54` with the rich-context summary pre-filled |
| Carnet local-only persists across sessions on device | DIAG-07 | IndexedDB/localStorage persistence + offline read is device/browser-specific | Run a diagnostic offline-capable, reload, confirm "mes diagnostics passés" list survives; confirm 0 network calls for history read |
| Chemistry + legal wording sign-off | DIAG-02 / DIAG-03 | Liability gate — Pierre (domain expert) must validate dose values + disclaimer copy before launch | Pierre reviews `DoseEngine` values vs `05-DIAGNOSTIC-EXPERT-AUDIT.md` and the disclaimer text; records sign-off |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 60s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
