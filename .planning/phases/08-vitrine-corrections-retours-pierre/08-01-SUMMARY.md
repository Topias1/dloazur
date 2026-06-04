---
phase: 08-vitrine-corrections-retours-pierre
plan: "01"
subsystem: vitrine
tags: [copy, tdd, hero, geo, test-stubs]
dependency_graph:
  requires: []
  provides:
    - HeroV1Test (5 assertions green)
    - DepannageRouteTest (6 stubs RED — gates plan 02)
    - CallCenterVoixTest (3 stubs RED — gates plan 03)
  affects:
    - resources/views/vitrine/partials/hero.blade.php
    - resources/views/vitrine/services/entretien-recurrent.blade.php
    - resources/views/vitrine/services/analyse-eau.blade.php
tech_stack:
  added: []
  patterns:
    - Pest 4 Feature tests in tests/Feature/Vitrine/ namespace
    - APP_BASE_PATH server var in phpunit.xml for worktree test isolation
key_files:
  created:
    - tests/Feature/Vitrine/HeroV1Test.php
    - tests/Feature/Vitrine/DepannageRouteTest.php
    - tests/Feature/Vitrine/CallCenterVoixTest.php
  modified:
    - resources/views/vitrine/partials/hero.blade.php
    - resources/views/vitrine/services/entretien-recurrent.blade.php
    - resources/views/vitrine/services/analyse-eau.blade.php
    - phpunit.xml
decisions:
  - D-01: Hero 3rd person — "notre zone d'intervention" replaces "ma tournée"
  - D-03: Invitation à appeler preserved — "Un appel suffit" in hero paragraph
  - D-04: Purge "toute la Martinique" from entretien-recurrent:105 and analyse-eau:117
metrics:
  duration: "~20 minutes"
  completed: "2026-06-04"
  tasks: 2
  files: 8
---

# Phase 08 Plan 01: Wave 0 Test Stubs + V1 Geo Corrections Summary

Hero paragraph rewritten to 3rd person with honest zone language; "toute la Martinique" purged from two service pages; test infrastructure for plans 02 and 03 in place.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Wave 0 — Write failing test stubs for V1/V5/V12/V14 | `3f22374` | HeroV1Test.php, DepannageRouteTest.php, CallCenterVoixTest.php |
| 2 | V1 — Hero 3rd person + purge "toute la Martinique" | `6ed5c2c` | hero.blade.php, entretien-recurrent.blade.php, analyse-eau.blade.php, phpunit.xml |

## Verification Results

- HeroV1Test: 5/5 green
- DepannageRouteTest: 6 stubs RED (route not yet created — gated on plan 02)
- CallCenterVoixTest: 2/3 stubs RED (brand voice not yet edited — gated on plan 03; 1 passes because call-center count is already ≤2 before V12 corrections)
- StaticPagesTest: 17/17 green (no regressions)
- HomePageTest: 17/17 green (no regressions)
- Total phase gate: 34/34 green

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Worktree test isolation: tests ran against main repo views**

- **Found during:** Task 2 GREEN phase
- **Issue:** Laravel's `Application::inferBasePath()` uses `ClassLoader::getRegisteredLoaders()` to infer the app root. When vendor is a symlink to the main repo, `inferBasePath()` returns the main repo path, causing tests to render main repo views (which still had "ma tournée"). The APP_BASE_PATH env var overrides this.
- **Fix:** Added `<server name="APP_BASE_PATH" value="..."/>` to phpunit.xml pointing to the worktree root. Also symlinked `public/build` from the main repo to provide the Vite manifest for test rendering.
- **Files modified:** `phpunit.xml`
- **Commit:** `6ed5c2c`

**2. [Rule 1 - Minor] Hero already partially updated in branch**

- **Found during:** Task 1 RED phase
- **Issue:** The plan assumed hero.blade.php line 22 reads "ma tournée" (per RESEARCH which scanned main branch). In the worktree's branch (`claude/pierre-feedback-website-app-A59QE`), the hero paragraph had already been partially updated to "partout sur l'île" — no "ma tournée". So `assertDontSee('ma tournée')` was already green.
- **Impact:** Task 1 RED count was 10/14 rather than 14/14. All critical stubs (notre zone, toute la Martinique, depannage route, call-center count, Notre approche) remained RED as required.
- **Action:** No change needed — test assertions remain correct for end-state verification.

## Known Stubs

None — all modified view content is real copy, no placeholder text.

## Threat Flags

No new security-relevant surface introduced. All modified routes are static public GET routes under `cache.headers:vitrine` middleware.

## Self-Check: PASSED

- tests/Feature/Vitrine/HeroV1Test.php: EXISTS, 5/5 green
- tests/Feature/Vitrine/DepannageRouteTest.php: EXISTS, 6 stubs RED
- tests/Feature/Vitrine/CallCenterVoixTest.php: EXISTS, 3 stubs (2 RED, 1 pre-green)
- resources/views/vitrine/partials/hero.blade.php: MODIFIED, no "ma tournée", contains "notre zone"
- resources/views/vitrine/services/entretien-recurrent.blade.php: MODIFIED, no "toute la Martinique"
- resources/views/vitrine/services/analyse-eau.blade.php: MODIFIED, no "toute la Martinique"
- Commit `3f22374`: EXISTS (test stubs)
- Commit `6ed5c2c`: EXISTS (implementation)
