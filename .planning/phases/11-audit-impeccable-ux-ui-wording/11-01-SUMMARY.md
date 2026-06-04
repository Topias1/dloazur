---
phase: 11-audit-impeccable-ux-ui-wording
plan: "01"
subsystem: offline-pwa
tags: [offline, alpine, pwa, a11y, wording]
dependency_graph:
  requires: []
  provides: [shared-offline-flush, zombie-recovery, sync-success-feedback]
  affects: [admin-layout, passage-create, offline-fallback]
tech_stack:
  added: [resources/js/upload-pipeline.js]
  patterns: [Alpine.store shared flush, module-level upload pipeline]
key_files:
  created:
    - resources/js/upload-pipeline.js
  modified:
    - resources/js/app.js
    - resources/js/passage-form.js
    - resources/js/sync-drawer.js
    - resources/views/layouts/admin.blade.php
    - resources/views/admin/passages/create.blade.php
    - resources/views/offline.blade.php
decisions:
  - "Upload pipeline extracted to upload-pipeline.js — single backoff source [2000,8000,30000]; passage-form.js and store both import from it"
  - "Photo upload backoff stays in passage-form.js._uploadPhoto — different endpoint, legitimately separate from passage upload"
  - "Sync-success pill rendered in admin layout (not a toast) — calme, role=status aria-live=polite, 3.5s auto-hide via Alpine syncSuccess store flag"
  - "offline.blade.php is resources/views/offline.blade.php (SW fallback page), not admin/passages/offline.blade.php which does not exist"
metrics:
  duration: "~25m"
  completed: "2026-06-04"
  tasks: 2
  files: 6
---

# Phase 11 Plan 01: Shared offline flush + zombie recovery + a11y/register fixes

Shared Alpine.store('offlineQueue').flush() pipeline with uploading-zombie recovery and sync-drawer mounted at admin layout level — zero data loss on any admin page.

## Tasks Completed

| # | Task | Commit | Files |
|---|------|--------|-------|
| 1 | Shared offlineQueue.flush() + zombie recovery + D-07 toast tu | ff06233 | app.js, passage-form.js, sync-drawer.js, upload-pipeline.js |
| 2 | sync-drawer at layout + a11y + steppers 56px + tu register | 9974546 | admin.blade.php, create.blade.php, offline.blade.php |

## What Was Built

**Task 1 — Shared flush pipeline (P0 SC-1):**
- New `upload-pipeline.js` module exports `flushPipeline()`, `uploadPassage()`, `recoverOrphans()`, `syncProduits()`, `buildHeaders()`, `UPLOAD_DELAYS` — single authoritative implementation of the passage upload backoff.
- `Alpine.store('offlineQueue')` gains `flush()` method and `syncSuccess` flag. `flush()` calls `flushPipeline()` then refreshes badge; sets `syncSuccess=true` for 3.5s when queue reaches zero.
- `passage-form:flush` event handler in `app.js` now calls `store.flush()` instead of `refresh()` — the event works from any admin page whether `passageForm` is mounted or not.
- `recoverOrphans()` called at `alpine:initialized` — any IDB passage stuck in `uploading` from a killed tab is re-queued to `pending` at boot.
- `sync-drawer.js flushAll()` promotes `uploading` + `error` → `pending` before dispatching flush.
- `passage-form.js _flushQueue()` delegates to `flushPipeline()` (no duplicate logic); retains photo-upload responsibility since photos are only captured on the create screen.
- Warning toast at passage-form.js line 184: "vérifiez votre lecture" → "vérifie ta lecture" (D-07).

**Task 2 — Layout mount + UX fixes:**
- `layouts/admin.blade.php`: `<x-admin.sync-drawer />` mounted once after main grid; sync-success confirmation pill wired to `$store.offlineQueue.syncSuccess`.
- `create.blade.php`: per-page `<x-admin.sync-drawer />` removed; `aria-live="assertive"` → `"polite"` on offline banner; stepper buttons `w-11 h-12` → `w-14 h-14` (56px) on both +/−.
- `offline.blade.php`: "Vous êtes hors ligne" → "Tu es hors ligne"; "au retour du réseau" → "à ton retour sur le réseau" (D-07 operator tu register).

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] offline.blade.php path**
- **Found during:** Task 2 read
- **Issue:** Plan referenced `resources/views/admin/passages/offline.blade.php` which does not exist. The SW offline fallback page is at `resources/views/offline.blade.php`.
- **Fix:** Applied D-07 tu fixes to the correct file.
- **Files modified:** resources/views/offline.blade.php

**2. [Rule 2 - Missing] create.blade.php vous already clean**
- **Found during:** Task 2
- **Issue:** Plan listed lines 55,88,344,404-408 in create.blade.php as needing vous→tu. Grep found zero `vous/votre` — those lines already used tu in the current codebase.
- **Fix:** No change needed; offline.blade.php was the remaining operator-facing vous.

## Known Stubs

None — all store flags, UI elements, and copy are fully wired.

## Threat Flags

No new network endpoints, auth paths, or trust boundaries introduced. The `upload-pipeline.js` module reuses the existing `/api/passages` and `/api/passages/produits` endpoints with the same CSRF + credentials discipline.

## Self-Check: PASSED

| Item | Result |
|------|--------|
| resources/js/upload-pipeline.js | FOUND |
| resources/js/app.js | FOUND |
| resources/views/layouts/admin.blade.php | FOUND |
| commit ff06233 (task 1) | FOUND |
| commit 9974546 (task 2) | FOUND |
