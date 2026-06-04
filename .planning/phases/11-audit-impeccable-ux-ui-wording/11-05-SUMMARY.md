---
phase: 11-audit-impeccable-ux-ui-wording
plan: "05"
subsystem: portail-client
tags: [portail, eau-saine, sc-7, vouvoiement, ux, remediation]
dependency_graph:
  requires: []
  provides: [eau-saine-gated-badge, guarded-photo-render, one-passage-state, portail-p3-polish]
  affects: [resources/views/livewire/portail/passage-timeline.blade.php, app/Livewire/Portail/PassageTimeline.php]
tech_stack:
  added: []
  patterns:
    - Gate badge on computed booleans from component (not blade @php inline)
    - @if ($firstPhotoUrl) guard pattern for optional signed-URL renders
    - Operator initial derived from config with fallback
key_files:
  created: []
  modified:
    - resources/views/livewire/portail/passage-timeline.blade.php
    - app/Livewire/Portail/PassageTimeline.php
decisions:
  - Moved $phOk/$clOk/$tacOk computation to the Livewire component (correct place for logic) so the badge and tile verdicts share canonical booleans without recomputing in the view
  - $firstPhotoUrl set to null (not '') on Storage exception so @if guard works correctly
  - $operatorInitial derived from config('app.operator_name', 'Pierre') for future-proof avatar; defaults to 'P'
  - Cl libre / TAC / pH tiles: unit (mg/L or pH) always visible; verdict "idéal" moved to a separate <span class="text-success"> so unit never disappears when in range (3 tiles now consistent)
metrics:
  duration: 15m
  completed_date: "2026-06-04"
  tasks_completed: 1
  files_changed: 2
---

# Phase 11 Plan 05: Portail Remediation — Eau saine gate + photo guard + P3 polish

One-liner: Eau saine badge gated on $phOk&&$clOk&&$tacOk (SC-7), broken-img guard replaced with @if/$firstPhotoUrl, 1-passage history state, and portail P3 polish (hero alt, Cl libre unit, operator avatar).

## Tasks Completed

| Task | Commit | Files |
|------|--------|-------|
| 1 — Gate Eau saine + guard temporaryUrl + 1-passage state + P3 polish | 9b2f53a | passage-timeline.blade.php, PassageTimeline.php |

## What Was Built

**PassageTimeline.php (component):**
- Computes `$phOk`, `$clOk`, `$tacOk` as canonical gate booleans (range-checked against the same thresholds as the blade tiles) and passes them to the view.
- Computes `$operatorInitial` from `config('app.operator_name', 'Pierre')`.
- Both variables are now available before the piscine card renders, enabling the Eau saine badge to gate on them.

**passage-timeline.blade.php (view):**
- **SC-7 / P1 Eau saine**: Badge guard changed from `@if ($lastPassage)` to `@if ($lastPassage && $phOk && $clOk && $tacOk)`. Out-of-range readings no longer show a green "Eau saine" claim.
- **P2 temporaryUrl guard**: Catch block now sets `$firstPhotoUrl = null` (was `''`). The `<img>` is wrapped in `@if ($firstPhotoUrl) ... @else <p>Photo non disponible pour ce passage.</p> @endif`. No broken `<img src="">` path remains.
- **P2 1-passage state**: `@if count()===1` branch added before the `@elseif count()>1` timeline list. Message: "Votre premier passage apparaît ci-dessus. L'historique se remplira à chaque entretien."
- **P3 hero alt**: Now dated when heroPhotoUrl is a real photo ("Votre piscine — passage du d/m/Y"), generic fallback otherwise.
- **P3 Cl libre / TAC / pH tiles**: Each tile shows its unit always (mg/L or pH label), with "idéal" in a separate `<span class="text-success font-semibold">` appended when in range. Tiles are now consistent and the unit never disappears.
- **P3 avatar**: Hardcoded "P" replaced with `{{ $operatorInitial }}`. Label changed from "Mot du pisciniste" to "Le mot de Pierre".
- **D-08**: No `tu` anywhere in the file; vouvoiement strict throughout.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] $firstPhotoUrl set to '' prevented @if guard from working**
- **Found during:** Task 1 — the existing catch block set `$firstPhotoUrl = ''`. An empty string is truthy-ish but evaluates falsy in Blade, however the spec guard is `@if ($firstPhotoUrl)` which correctly treats empty string as falsy. Changed to `null` for semantic correctness and to avoid any ambiguity.
- **Fix:** `$firstPhotoUrl = null` in catch block.
- **Files modified:** passage-timeline.blade.php

**2. [Rule 2 - Missing logic] $phOk/$clOk/$tacOk not in component**
- **Found during:** Task 1 — the plan says "use the already-computed $phOk/$clOk/$tacOk; do NOT recompute" referencing CONTEXT.md, but the component did not compute them. They were `@php` inline in the blade, after the badge location.
- **Fix:** Moved canonical computation to the Livewire component (`PassageTimeline.php`), removed the three `@php` inline blocks from the blade tiles (now use the view-injected variables). No formula duplication.
- **Files modified:** PassageTimeline.php, passage-timeline.blade.php

None of the deviations are architectural — both are corrections within the same single-file/component scope of the plan.

## Threat Surface Scan

No new network endpoints, auth paths, file access patterns, or schema changes. The `@if ($firstPhotoUrl)` guard narrows the existing R2 signed-URL render surface (T-11-05-01) — it does not widen it. No new threat flags.

## Known Stubs

None. All six changes are wired to real data.

## Self-Check: PASSED

- [x] `passage-timeline.blade.php` exists and modified
- [x] `PassageTimeline.php` exists and modified
- [x] Commit 9b2f53a present in git log
- [x] grep phOk → line 63 badge guard confirmed
- [x] grep firstPhotoUrl → @if guard at line 187 confirmed
- [x] grep "premier passage" → line 209-210 confirmed
- [x] No `<img src="">` path remains
- [x] No `tu` in the blade file
