---
phase: 10-diagnostic-retours-pierre
plan: "01"
subsystem: diagnostic-wizard
tags: [livewire, attribution, tdd, test-decoupling]
dependency_graph:
  requires: []
  provides: [DiagnosticWizard::setMode, attribution-tests]
  affects: [app/Livewire/DiagnosticWizard.php, tests/Feature/DiagnosticWizardTest.php, tests/Browser/CarnetLocalTest.php]
tech_stack:
  added: []
  patterns: [whitelist-validation, tdd-red-green]
key_files:
  created: []
  modified:
    - app/Livewire/DiagnosticWizard.php
    - tests/Feature/DiagnosticWizardTest.php
    - tests/Browser/CarnetLocalTest.php
decisions:
  - "setMode() silently ignores out-of-whitelist values (no exception) to prevent Tampering (T-10-01)"
  - "assertSee('Avant de commencer') is the stable intermediate assertion (stable across Plan 01 and 02 states)"
metrics:
  duration: "8m"
  completed_date: "2026-06-04"
  tasks: 2
  files_changed: 3
---

# Phase 10 Plan 01: setMode() server-side + test decoupling Summary

**One-liner:** Added `setMode()` Livewire method with `['symptom','chemistry']` whitelist, wiring Alpine mode clicks to server `$mode` → `created_via`/`type_probleme`, and decoupled Feature/Browser tests from the S0 screen removed in Plan 02.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Ajouter setMode() + tests attribution created_via | c75d3d6 | app/Livewire/DiagnosticWizard.php, tests/Feature/DiagnosticWizardTest.php |
| 2 | Découpler les tests de l'écran S0 | bf2fbac | tests/Feature/DiagnosticWizardTest.php, tests/Browser/CarnetLocalTest.php |

## What Was Built

**Task 1 (TDD):**

RED: Three failing attribution tests added to `DiagnosticWizardTest.php`:
- Test A: `setMode('chemistry')` → `created_via='wizard'`, `type_probleme='chemistry'`
- Test B: `setMode('symptom')` → `created_via='depannage'`, `type_probleme='symptom'`
- Test C: `setMode('foo')` → silent ignore, `$mode` stays null, `created_via='wizard'`

GREEN: `setMode(string $mode): void` added to `DiagnosticWizard.php` immediately after `acceptDisclaimer()`. Uses `in_array($mode, ['symptom','chemistry'], strict: true)` guard — any other value is silently ignored (T-10-01 Tampering mitigation). The `keepDiagnostic()` lines at 633/637 are untouched.

**Task 2:**
- `tests/Feature/DiagnosticWizardTest.php` line 21: `assertSee('Trouver mon problème')` → `assertSee('Avant de commencer')` with D-08 comment
- `tests/Browser/CarnetLocalTest.php` line 101: same replacement with D-09 comment
- No `data-mode-*` selectors found in tests (grep confirmed zero matches)
- `DiagnosticPdfTest.php` and `DiagnosticRouteTest.php` untouched

## Verification

```
vendor/bin/pest tests/Feature/DiagnosticWizardTest.php tests/Feature/DiagnosticRouteTest.php
# → 43 passed (0 failures)

grep -c "function setMode" app/Livewire/DiagnosticWizard.php
# → 1

grep -rn "Trouver mon problème|data-mode-" tests/
# → (no output — clean)
```

## Deviations from Plan

None — plan executed exactly as written.

## Known Stubs

None.

## Threat Flags

None — `setMode()` is the T-10-01 mitigation from the plan's threat register, correctly implemented with in_array whitelist.

## Self-Check: PASSED

- [x] `app/Livewire/DiagnosticWizard.php` modified — `function setMode` exists
- [x] `tests/Feature/DiagnosticWizardTest.php` modified — attribution tests present
- [x] `tests/Browser/CarnetLocalTest.php` modified — assertSee('Avant de commencer') present
- [x] Commit c75d3d6 exists (Task 1)
- [x] Commit bf2fbac exists (Task 2)
- [x] keepDiagnostic() lines 633/637 untouched
- [x] Full test suite: 43/43 green
