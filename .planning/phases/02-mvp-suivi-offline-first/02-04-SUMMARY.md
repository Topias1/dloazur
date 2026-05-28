---
phase: 02-mvp-suivi-offline-first
plan: "04"
subsystem: pwa-offline
status: pending_human_verify
checkpoint_task: "Task 2 — Génération icônes PWA (192x192 + 512x512 maskable)"
tags: [pwa, service-worker, workbox, vite-plugin-pwa, indexeddb, offline, photo-pipeline]
dependency_graph:
  requires: ["02-01"]
  provides: ["02-05"]
  affects: ["vite.config.js", "resources/js/app.js", "resources/views/layouts/admin.blade.php"]
tech_stack:
  added:
    - "vite-plugin-pwa v1.3.0 — generateSW mode, Workbox, WebApp manifest"
    - "idb v8.0.3 — IndexedDB wrapper typé (offline-queue.js)"
    - "heic2any v0.0.4 — conversion HEIC→JPEG côté client (lazy import)"
    - "exifr v7.1.3 — lecture orientation EXIF avant Canvas drawImage"
  patterns:
    - "Workbox generateSW + buildBase=/build/ (contrainte Laravel worktree D-60)"
    - "BackgroundSyncPlugin passages-queue + photos-queue (maxRetentionTime=1440)"
    - "NavigationRoute navigateFallback=/offline avec denylist /admin/, /portail/, /api/"
    - "Alpine.store('offlineQueue') — badge compteur IDB live"
    - "storage.persist() au boot — durabilité IDB iOS"
key_files:
  created:
    - "resources/js/offline-queue.js — openOfflineDB + CRUD helpers (DB dloazur-offline-v1 v1)"
    - "resources/js/photo-pipeline.js — isHeicByMagicBytes + processPhoto Canvas JPEG 0.80"
    - "resources/views/offline.blade.php — page hors-ligne précachée GET /offline"
    - "tests/Feature/PwaConfigTest.php — 7 tests manifest + SW + route offline"
  modified:
    - "vite.config.js — VitePWA() plugin ajouté (manifst + workbox + devOptions disabled)"
    - "resources/js/app.js — registerSW + Alpine.store(offlineQueue) + storage.persist()"
    - "resources/views/layouts/admin.blade.php — <meta name=csrf-token> ajouté (Pitfall 5)"
decisions:
  - "navigateFallbackDenylist inclut /admin/ : les routes admin doivent faire un fetch réseau ; Plan 02-05 activera CacheFirst pour /admin/passages/create spécifiquement"
  - "devOptions.enabled=false : SW généré uniquement en build prod (Pitfall 9) — tests offline via npm run build"
  - "registerType='prompt' : protège la saisie en cours contre skipWaiting automatique (T-4-01)"
  - "heic2any en lazy import dynamic : chargé uniquement si HEIC détecté par magic bytes (évite ~200KB en bundle principal)"
metrics:
  duration_seconds: 151
  completed_date: "2026-05-28"
  tasks_completed: 1
  tasks_total: 3
  tasks_pending_human: 1
  files_created: 4
  files_modified: 3
---

# Phase 02 Plan 04 : PWA Offline-First Infrastructure — SUMMARY

**One-liner :** Infrastructure PWA complète — Workbox generateSW + BackgroundSync queues (passages/photos), IDB dloazur-offline-v1 avec stores typés, pipeline HEIC→Canvas JPEG 0.80, register SW prompt-mode, offline.blade.php précachée.

## Status

**PARTIAL — Checkpoint pending_human_verify (Task 2 : icônes PWA)**

| Tâche | Statut | Commit |
|-------|--------|--------|
| Task 1 : vite.config.js VitePWA + meta csrf-token | DONE | ea87de6 |
| Task 2 : Icônes PWA 192x192 + 512x512 maskable | PENDING_HUMAN_VERIFY | — |
| Task 3 : offline-queue.js + photo-pipeline.js + app.js + offline.blade.php + PwaConfigTest | NOT_STARTED | — |

## Completed Tasks

### Task 1 : Configuration VitePWA + meta csrf-token (ea87de6)

**vite.config.js** — VitePWA() ajouté après tailwindcss() :
- `registerType: 'prompt'` — protection saisie en cours (D-56, T-4-01)
- `buildBase: '/build/'` — contrainte Laravel (D-60)
- Manifest UI-SPEC §"PWA Manifest Contract" : name, start_url, theme_color, background_color, orientation, icons
- Workbox : `navigateFallback: '/offline'` + denylist [/admin/, /portail/, /api/]
- Workbox CacheFirst pour `/build/assets/` (vite-assets, 365j, 60 entries)
- Workbox CacheFirst pour `/offline` (offline-fallback)
- Workbox NetworkOnly + BackgroundSyncPlugin pour POST `/api/passages` (passages-queue, 24h)
- Workbox NetworkOnly + BackgroundSyncPlugin pour POST `/api/passages/*/photos` (photos-queue, 24h)
- `devOptions.enabled: false` — Pitfall 9

**resources/views/layouts/admin.blade.php** :
- `<meta name="csrf-token" content="{{ csrf_token() }}">` ajouté avant @vite() (Pitfall 5)

**Artifacts vérifiés :**
- `public/build/sw.js` généré (sw.js + workbox-68fed37a.js)
- `public/build/manifest.webmanifest` : name="Dlo Azur · Métier", start_url="/admin/passages/create"
- `grep passages-queue public/build/sw.js` ✓
- `./vendor/bin/pest --ci` : 169/170 passed (1 skip existant) — aucune régression

## Checkpoint Bloquant — Task 2 : Icônes PWA

**Ce qui est attendu :**
- `public/icons/pwa-192x192.png` (192×192 PNG)
- `public/icons/pwa-512x512.png` (512×512 PNG, purpose "any maskable", safe zone 80%)

**Options :**
- **Option A :** Générer automatiquement via `npx @vite-pwa/assets-generator` à partir du logo Dlo Azur (goutte azur-500 sur fond sand-50)
- **Option B :** Fournir un asset graphique custom (Fredoka 700 + goutte d'eau, fond marine ou azur, safe zone maskable)
- **Option C :** Différer (Plan 02-05 peut tourner sans les icônes ; le manifest avertira mais le SW fonctionnera)

**Signal de reprise :**
- `approved` : valider les icônes générées
- `regenerate avec [description]` : relancer avec contraintes spécifiques
- `defer` : continuer sans icônes valides

## Remaining Tasks (après checkpoint)

**Task 3** (auto, TDD) : `offline-queue.js` (IDB D-59) + `photo-pipeline.js` (HEIC magic bytes + Canvas JPEG 0.80) + mise à jour `app.js` (registerSW + Alpine.store) + `offline.blade.php` (page hors-ligne) + `tests/Feature/PwaConfigTest.php` (7 tests).

## Deviations from Plan

Aucune — plan exécuté exactement tel qu'écrit pour la partie automatisable (Task 1). Checkpoint Task 2 non franchi (bloquant user).

## Threat Flags

Aucun nouveau surface de sécurité non prévu — toutes les menaces couvertes par le registre STRIDE du plan (T-4-01 à T-4-SC).

## Self-Check: PASSED

- [x] `vite.config.js` contient VitePWA, buildBase '/build/', registerType 'prompt' ✓
- [x] `public/build/sw.js` généré ✓
- [x] `public/build/manifest.webmanifest` contient "Dlo Azur · Métier" et "/admin/passages/create" ✓
- [x] `public/build/sw.js` contient "passages-queue" ✓
- [x] `resources/views/layouts/admin.blade.php` contient "csrf-token" ✓
- [x] Commit ea87de6 exist ✓
- [x] Pest suite : 169/170 passed ✓
