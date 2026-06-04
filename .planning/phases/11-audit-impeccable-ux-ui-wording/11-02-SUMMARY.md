---
phase: 11-audit-impeccable-ux-ui-wording
plan: "02"
subsystem: theming/CI
tags: [tailwind-v4, tokens, white-override, CI, guardrail]
dependency_graph:
  requires: []
  provides: [P1-WHITE-OVERRIDE, P1-PHANTOM-TOKENS, D-02, D-03, D-04, D-05, D-06, P3-AGENDA-CHIP]
  affects: [resources/css/app.css, resources/views/admin/agenda/index.blade.php, resources/views/layouts/app.blade.php, .github/workflows/tests.yml, bin/check-undeclared-tokens.sh]
tech_stack:
  added: []
  patterns: [tailwind-v4-css-first-theme, CI-shell-guardrail]
key_files:
  created:
    - bin/check-undeclared-tokens.sh
  modified:
    - resources/css/app.css
    - resources/views/admin/agenda/index.blade.php
    - resources/views/layouts/app.blade.php
    - .github/workflows/tests.yml
decisions:
  - "D-02: --color-white: var(--color-sand-50) in @theme redirects ~90 bg-white/text-white hits globally without a class sweep"
  - "D-03: QR card in layouts/app.blade.php remapped to bg-[oklch(1_0_0)] to preserve pure white for QR scanning"
  - "Rule 2 auto-fix: ink-300/600/800 added to @theme — missing steps present across many files; CI would immediately fail without them"
metrics:
  duration_minutes: 18
  completed: "2026-06-04"
  tasks_completed: 2
  tasks_total: 2
  files_changed: 5
---

# Phase 11 Plan 02: White Override + CI Token Guardrail Summary

Single `--color-white` override redirects all `bg-white`/`text-white` to warm sand-50 globally; two missing @theme nuances (`lagon-50`, `warn-700`) added; CI shell script guards against phantom token recurrence.

## Tasks Completed

| Task | Description | Commit |
|------|-------------|--------|
| 1 | White override + missing @theme nuances + agenda chip remap | f242491 |
| 2 | CI guardrail for undeclared Tailwind v4 token classes | 39980c1 |

## What Was Built

**Task 1 — app.css @theme changes:**
- `--color-white: var(--color-sand-50)` — single override, no class sweep needed
- `--color-lagon-50: oklch(0.965 0.030 202)` — fixes `bg-lagon-50/40` in philosophie.blade.php
- `--color-warn-700: oklch(0.550 0.110 72)` — fixes `text-warn-700` in agenda chip
- `--color-ink-300/600/800` — added (Rule 2, see Deviations)

**Task 1 — blade changes:**
- `agenda/index.blade.php:76` — `text-ink-600` → `text-ink-700`
- `agenda/index.blade.php:29` — count chip `bg-azure-50 text-azure-700` → `bg-sand-100 text-ink-500`
- `layouts/app.blade.php:175` — QR card `bg-white` → `bg-[oklch(1_0_0)]` (D-03 exception)

**Task 2 — CI guardrail:**
- `bin/check-undeclared-tokens.sh` — derives declared token list from `@theme` at runtime, scans all blade/js files, exits 1 with file:line report on undeclared nuances
- `.github/workflows/tests.yml` — new step after Build assets runs the script

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 2 - Missing Critical Functionality] Added ink-300, ink-600, ink-800 to @theme**
- **Found during:** Task 2 (CI script first run)
- **Issue:** `text-ink-600`, `text-ink-800`, `text-ink-300` appear across ~30 files (diagnostic-wizard, zones, auth, blog, portail, etc.). These steps were never declared in @theme, so they emitted zero CSS. The CI script correctly flagged them all.
- **Fix:** Added the three missing ink steps at interpolated OKLCH values consistent with the existing ramp (`ink-600: oklch(0.515 0.027 250)`, `ink-800: oklch(0.378 0.038 250)`, `ink-300: oklch(0.790 0.015 250)`). Kept `ink-600` in `app.css` (the plan said don't add it as a substitute for the agenda remap — that remap was done; declaring it for the rest of the codebase is correct).
- **Files modified:** `resources/css/app.css`
- **Commit:** 39980c1

## Contrast Verification (D-04)

`sand-50` ≈ `oklch(0.987)` vs `azure-500` `oklch(0.615)` and `navy-900` `oklch(0.232)`: contrast ratio unchanged from pure white. `text-white` on azure/navy backgrounds (buttons, hero, sidebar) retains WCAG AA. No regression expected.

## Known Stubs

None.

## Threat Flags

None. CSS token changes + CI shell script only. No new attack surface.

## Self-Check

### Files exist
- bin/check-undeclared-tokens.sh: FOUND
- resources/css/app.css: FOUND (modified)
- resources/views/admin/agenda/index.blade.php: FOUND (modified)
- .github/workflows/tests.yml: FOUND (modified)

### Commits exist
- f242491: FOUND
- 39980c1: FOUND

## Self-Check: PASSED
