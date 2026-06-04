---
phase: 08-vitrine-corrections-retours-pierre
plan: "03"
subsystem: vitrine
tags: [copy, brand-voice, fusion, call-center, tdd-green]
dependency_graph:
  requires:
    - 08-01 (CallCenterVoixTest stubs RED)
    - 08-02 (services-detail D-10 already fixed)
  provides:
    - CallCenterVoixTest 3/3 green
    - HomePageTest updated assertion for "Notre approche"
  affects:
    - resources/views/vitrine/partials/philosophie.blade.php
    - resources/views/vitrine/partials/engagements.blade.php
    - resources/views/vitrine/home.blade.php
    - resources/views/vitrine/partials/pierre.blade.php
    - resources/views/vitrine/partials/final-cta.blade.php
    - tests/Feature/HomePageTest.php
tech_stack:
  added: []
  patterns:
    - Blade partials fusion via rewrite-and-empty
    - "@include removal from home.blade.php for merged section"
key_files:
  created: []
  modified:
    - resources/views/vitrine/partials/philosophie.blade.php
    - resources/views/vitrine/partials/engagements.blade.php
    - resources/views/vitrine/home.blade.php
    - resources/views/vitrine/partials/pierre.blade.php
    - resources/views/vitrine/partials/final-cta.blade.php
    - tests/Feature/HomePageTest.php
decisions:
  - "D-09: philosophie+engagements fused into Notre approche 4-card section in philosophie.blade.php"
  - "D-10: pierre Pas de centre d appel prefix removed; final-cta jamais a un standard → sans intermediaire"
  - "D-11: interlocuteur unique absent from merged section; card 4 uses Meme prestataire angle"
metrics:
  duration: "~20 minutes"
  completed: "2026-06-04"
  tasks: 2
  files: 6
---

# Phase 08 Plan 03: V12/V14 Brand Voice Consolidation Summary

"Notre approche" 4-card section fuses philosophie+engagements; call-center/standard language purged from pierre and final-cta; 0 occurrences of negative brand-voice on home page.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Fuse philosophie + engagements → "Notre approche" | `7bc49a5` | philosophie.blade.php, engagements.blade.php, home.blade.php, HomePageTest.php |
| 2 | Purge call-center language from pierre + final-cta | `e9410f0` | pierre.blade.php, final-cta.blade.php |

## Verification Results

- philosophie.blade.php: section heading exactly "Notre approche", 4-card grid, no forbidden strings in rendered HTML
- engagements.blade.php: emptied to Blade comment; @include removed from home.blade.php
- pierre.blade.php: line 19 now starts with "Vous échangez directement" — no negative prefix
- final-cta.blade.php: "sans intermédiaire" replacing "jamais à un standard"
- services-detail.blade.php: already fixed by plan 02 (cherry-picked)
- HomePageTest: assertion updated "Nos engagements" → "Notre approche"
- CallCenterVoixTest: 0 occurrences of call-center/standard/centre d'appel in home rendering path (≤2 threshold met)

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Worktree spawned before phase 08 commits — missing test files + view changes**

- **Found during:** Task 1 setup
- **Issue:** This worktree branch was spawned from `9d3a9a4` (before phase 08 landed on main). The `tests/Feature/Vitrine/` directory and all plan 01/02 view changes were absent.
- **Fix:** Cherry-picked 4 commits from the main repo into this branch: plan 01 test stubs (3f22374), plan 01 hero changes (6ed5c2c — phpunit.xml excluded per parallel_execution instructions), plan 02 route/controller (a2831d5), plan 02 depannage page + services-detail fix (9e80afb — phpunit.xml excluded).
- **Files brought in:** CallCenterVoixTest.php, DepannageRouteTest.php, HeroV1Test.php, hero.blade.php, entretien-recurrent.blade.php, analyse-eau.blade.php, VitrineController.php, routes/vitrine.php, services-grid.blade.php, services-detail.blade.php, depannage.blade.php, SitemapController.php
- **Commits:** cec93e0, 94c3ddd, d6d9f67, 0cc0136

**2. [Rule 1 - Plan alignment] services-detail already fixed by plan 02 cherry-pick**

- **Found during:** Task 2 pre-check
- **Issue:** Plan 03 listed services-detail line 202 as a Task 2 target. The plan 02 cherry-pick had already replaced "sans standard téléphonique ni rotation d'interlocuteurs" with "avec un compte-rendu après chaque intervention".
- **Fix:** Verified and noted — no duplicate edit needed.

## Known Stubs

None — all copy is real brand-voice content, no placeholder text.

## Threat Flags

No new security-relevant surface introduced. All changes are static Blade partials under public GET routes.

## Self-Check: PASSED

- resources/views/vitrine/partials/philosophie.blade.php: EXISTS, contains "Notre approche", no forbidden strings in HTML output
- resources/views/vitrine/partials/engagements.blade.php: EXISTS, emptied to comment
- resources/views/vitrine/home.blade.php: EXISTS, @include engagements removed
- resources/views/vitrine/partials/pierre.blade.php: EXISTS, no "Pas de centre d'appel"
- resources/views/vitrine/partials/final-cta.blade.php: EXISTS, "sans intermédiaire"
- tests/Feature/HomePageTest.php: EXISTS, asserts "Notre approche"
- Commit 7bc49a5: EXISTS (Task 1)
- Commit e9410f0: EXISTS (Task 2)
