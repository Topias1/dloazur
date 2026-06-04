---
phase: 08-vitrine-corrections-retours-pierre
plan: "04"
subsystem: vitrine
tags: [cleanup, blade, partial-deletion]
dependency_graph:
  requires: [08-01]
  provides: [V6-complete]
  affects: [resources/views/vitrine/services.blade.php, resources/views/vitrine/home.blade.php]
tech_stack:
  added: []
  patterns: [blade-partial-removal]
key_files:
  created: []
  modified:
    - resources/views/vitrine/services.blade.php
    - resources/views/vitrine/home.blade.php
  deleted:
    - resources/views/vitrine/partials/urgence-eau-verte.blade.php
decisions:
  - "D-12: Removed orphan urgence-eau-verte partial — file deleted, both @include references purged (services.blade.php + home.blade.php)"
metrics:
  duration: "~5 min"
  completed: "2026-06-04T03:53:40Z"
  tasks_completed: 1
  files_changed: 3
---

# Phase 08 Plan 04: V6 Ménage — Suppression du partial urgence-eau-verte — Summary

**One-liner:** Orphan Blade partial `urgence-eau-verte.blade.php` deleted after removing its two `@include` references from `services.blade.php` and `home.blade.php`, eliminating the risk of a 500 on both GET /services and GET /.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Remove @include from services.blade.php then delete partial file | 57f92ff | services.blade.php (modified), home.blade.php (modified), urgence-eau-verte.blade.php (deleted) |

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Second @include in home.blade.php not flagged by RESEARCH.md**
- **Found during:** Task 1 — post-edit grep check `grep -r "urgence-eau-verte" resources/views/`
- **Issue:** `home.blade.php` line 13 also contained `@include('vitrine.partials.urgence-eau-verte')`. If the file had been deleted with only the `services.blade.php` include removed, GET / would have thrown a Blade `InvalidArgumentException` (500).
- **Fix:** Removed the include block (including the D-34 comment) from `home.blade.php` before committing.
- **Files modified:** `resources/views/vitrine/home.blade.php`
- **Commit:** 57f92ff

## Verification

Tests could not run in worktree (vendor symlink not available — known parallel execution constraint). Verification done manually:

- `grep -r "urgence-eau-verte" resources/views/` returned **ALL CLEAN** (zero references remaining)
- `test ! -f resources/views/vitrine/partials/urgence-eau-verte.blade.php` returned **DELETED OK**
- `services.blade.php` confirmed clean (4 remaining @includes: services-grid, services-detail, how-it-works, final-cta)
- Orchestrator will run `./vendor/bin/pest --filter='StaticPages' -x` post-merge to confirm GET /services and GET / return 200

## Known Stubs

None.

## Threat Flags

None — this plan only removes a Blade partial and its @include references. No new network surface introduced.

## Self-Check: PASSED

- [x] `resources/views/vitrine/partials/urgence-eau-verte.blade.php` does not exist on disk
- [x] `resources/views/vitrine/services.blade.php` does not reference `urgence-eau-verte`
- [x] `resources/views/vitrine/home.blade.php` does not reference `urgence-eau-verte`
- [x] `grep -r "urgence-eau-verte" resources/views/` returns empty
- [x] Commit 57f92ff exists in git log
