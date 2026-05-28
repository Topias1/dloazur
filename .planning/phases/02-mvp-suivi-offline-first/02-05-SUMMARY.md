---
phase: 02-mvp-suivi-offline-first
plan: "05"
subsystem: passage-form-offline
status: complete
pending_human_verify:
  - id: device-smoke-test
    description: >
      Validation terrain sur appareil réel — iOS Safari + Android Chrome.
      Vérifier offline flow IDB, fallback visibilitychange (Pitfall 1), storage.persist()
      après ajout Home Screen, et sync drawer "Synchroniser maintenant".
    how_to_verify: |
      1. npm run build && php artisan serve --host 0.0.0.0 --port 8000
      2. Login Pierre → cliquer "Nouveau passage" dans le topbar
      3. Vérifier la vue vs mockups/v1/passage.html (côte à côte)
      4. Test online : pH 7.4 + Cl libre 1.5 + 3 actions + photo → "Enregistrer"
         → badge "N en attente" → disparaît → /admin/passages affiche le passage
      5. Test offline : DevTools Network→Offline → saisir passage → badge "1 en attente"
         → réactiver réseau → badge → 0
      6. Test iOS Safari : backgroundSync absent → visibilitychange trigger OK
      7. Lighthouse mobile PWA ≥ 80
    resume_signal: "approved | issues:[description] | iOS Safari fail"
tags: [alpine, indexeddb, offline-first, passage-form, sync-drawer, sync-badge, pwa]
dependency_graph:
  requires: ["02-02", "02-04", "02-06"]
  provides: ["passage-form-offline-core"]
  affects:
    - resources/js/app.js
    - resources/views/components/admin/topbar.blade.php
    - resources/views/components/admin/sidebar.blade.php
    - resources/views/components/admin/mobile-bottom-nav.blade.php
    - routes/admin.php
tech_stack:
  added:
    - "passage-form.js — Alpine.data('passageForm') factory : offline-first IDB + flux queue"
    - "sync-drawer.js — Alpine.store('syncDrawer') : panneau glissant pending list + retry"
  patterns:
    - "crypto.randomUUID() côté client au mount (D-39) — UUIDv4 persist immédiat en IDB"
    - "online + visibilitychange listeners (Pitfall 1 iOS fallback)"
    - "_flushQueue backoff 2s→8s→30s 3 tentatives (D-45)"
    - "409 handler : marque synced + toast conflictMsg (D-40)"
    - "Alpine.store('syncDrawer') partagé entre badge et composant drawer"
    - "[x-cloak] { display: none !important } ajouté dans app.css"
    - "h-13 (52px) save-bar + h-18 (72px) bottom-nav helpers CSS"
key_files:
  created:
    - "resources/js/passage-form.js — factory Alpine.data passageForm (PASS-01..03)"
    - "resources/js/sync-drawer.js — factory Alpine.store syncDrawer (PASS-06)"
    - "resources/views/admin/passages/create.blade.php — vue saisie 1:1 mockup passage.html"
    - "resources/views/components/admin/sync-drawer.blade.php — panneau glissant IDB pending"
    - "resources/views/components/admin/sync-badge.blade.php — badge topbar 'N en attente'"
    - "resources/views/components/admin/pwa-update-toast.blade.php — toast update SW"
    - "app/Http/Controllers/Admin/PassageCreateController.php — GET passages/create, ?client_id"
    - "tests/Feature/PassageCreateViewTest.php — 4 Feature tests (fallback sans Playwright)"
  modified:
    - "resources/js/app.js — Alpine.data(passageForm) + Alpine.store(syncDrawer) + event relay"
    - "routes/admin.php — Route::get passages/create admin.passages.create"
    - "resources/views/components/admin/topbar.blade.php — bouton actif + x-admin.sync-badge"
    - "resources/views/components/admin/sidebar.blade.php — Passages activé + badge IDB"
    - "resources/views/components/admin/mobile-bottom-nav.blade.php — Passages activé + badge"
    - "resources/css/app.css — [x-cloak] + h-13 + h-18"
decisions:
  - "Alpine.store('syncDrawer') choisi plutôt que Alpine.data('syncDrawer') — partage d'état plus simple entre topbar badge (sync-badge) et composant drawer"
  - "CustomEvent 'sync-drawer:open' dispatchté depuis sync-badge, écouté dans syncDrawerStore.init() — pas besoin d'un store 'open' séparé"
  - "Feature test (fallback) créé plutôt que Browser Playwright — Playwright non disponible dans CI à cette étape ; tests Browser reportés en Plan QA mobile"
  - "passage-index.blade.php non modifié — ce fichier appartient au scope de 02-03 (agent parallèle) ; sa modification est déportée sur ce plan ou sur 02-03"
metrics:
  duration_seconds: 619
  completed_date: "2026-05-28"
  tasks_completed: 1
  tasks_total: 2
  tasks_pending_human: 1
  files_created: 8
  files_modified: 6
---

# Phase 02 Plan 05 : Passage Form Alpine+IndexedDB — SUMMARY

**One-liner :** Formulaire saisie passage offline-first (Alpine.data passageForm + IDB) avec steppers mesures, pipeline photo HEIC→JPEG, flush queue backoff 3 retries, sync drawer glissant, badge topbar "N en attente" et activation nav Passages.

## Status

**PARTIAL — Task 1 DONE, Task 2 (device validation) PENDING_HUMAN_VERIFY**

| Tâche | Statut | Commit |
|-------|--------|--------|
| Task 1 : passage-form.js + sync-drawer.js + Blade views + route + nav activation | DONE | fff16a4 |
| Task 2 : smoke test iOS Safari + Android Chrome (device réel) | PENDING_HUMAN_VERIFY | — |

## Completed Tasks

### Task 1 : Alpine passage-form + sync UI + nav activation (fff16a4)

**resources/js/passage-form.js** — Factory `Alpine.data('passageForm')` :
- `crypto.randomUUID()` + persist IDB au mount `init()` (D-39)
- Watchers mesures + actions + notes → debounced 500ms auto-save draft
- Validation soft plages SOFT_RANGES (pH [5,9], Cl libre [0,10], etc. — D-63) → toast warning 4s
- Steppers `incr()`/`decr()` avec precision `toFixed()`
- `onPhotoSelect()` → `processPhoto()` (Plan 02-04) → `savePhoto()` IDB store photos (D-42)
- `submit()` → `_saveToIDB('pending')` → `_flushQueue()` si online
- `_flushQueue()` : `getPassagesByStatus('pending')` → `_uploadPassage()` chaque item
- `_uploadPassage()` : fetch POST `/api/passages` + gestion 409 D-40 + backoff 2s/8s/30s (D-45)
- `_uploadPhotosForPassage()` → `_uploadPhoto()` séquentiel FormData backoff (D-46)
- `_headers(isJson)` : X-CSRF-TOKEN meta tag (Pitfall 5), Accept: application/json
- Listeners `online` + `visibilitychange` → `_flushQueue()` (Pitfall 1 iOS fallback)
- Listener `passage-form:flush` CustomEvent (depuis sync-drawer)

**resources/js/sync-drawer.js** — Factory `syncDrawerStore()` :
- `Alpine.store('syncDrawer')` partagé (pas Alpine.data)
- `init()` : écoute `sync-drawer:open` CustomEvent
- `toggle()`, `refresh()` : lit IDB getAllFromStore('passages') filter non-synced
- `retry(id)` : repasse en pending + dispatch flush
- `flushAll()` : tous errors → pending + dispatch flush
- `statusLabel()` / `statusClass()` : chip statut ambre/azure/danger

**resources/js/app.js** :
- `import { passageForm } from './passage-form.js'`
- `import { syncDrawerStore } from './sync-drawer.js'`
- `Alpine.store('syncDrawer', syncDrawerStore())`
- `Alpine.data('passageForm', passageForm)`
- `syncDrawer.init()` après `alpine:initialized`
- Relay event `passage-form:flush` → `offlineQueue.refresh()`

**resources/views/admin/passages/create.blade.php** :
- `@extends('layouts.admin')` + sections sidebar/topbar/main
- `x-data="passageForm({ clientId: ..., piscineId: ... })"` + `x-init="init()"`
- Sticky header : retour + nom client + date `translatedFormat('l j F')`
- Bandeau hors-ligne ambre `x-show="!online"` `role="status"` `aria-live="assertive"` (UI-SPEC)
- 7 cartes mesures (pH avant/après, Cl libre/total, TAC, Sel, TH) avec steppers h-12 ≥ 44px
- 10 actions cochables `x-for` + `toggleAction()` + ring-1 bleu si cochée
- Section Photos grille 3col + badge statut inline + `<input capture="environment">`
- Notes textarea + notes internes textarea
- Sticky save-bar h-13 (52px) `@click="submit()"` + indicateur "Brouillon sauvegardé"
- Toasts warnings mesures + toast 409 conflictMsg
- `<x-admin.mobile-bottom-nav />` + `<x-admin.sync-drawer />` + `<x-admin.pwa-update-toast />`

**3 composants Blade créés** : sync-drawer, sync-badge, pwa-update-toast (UI-SPEC respecté)

**Navigation activée** :
- Topbar : bouton disabled → `<a href="{{ route('admin.passages.create') }}">` + `<x-admin.sync-badge />`
- Sidebar : `<span aria-disabled>` → `<a>` avec `@class` active + badge IDB `x-show`
- Mobile bottom-nav : `<span aria-disabled>` → `<a>` `relative` + badge superposé `absolute`

**app.css** : `[x-cloak] { display: none !important }` + `.h-13 { height: 3.25rem }` + `.h-18`

**PassageCreateController** : `?client_id=X` → Client::with('piscines')->find() → auto-pick piscine unique (D-64)

**routes/admin.php** : `Route::get('passages/create', ...)→name('passages.create')` — sans toucher aux routes dashboard (scope 02-03)

## Deviations from Plan

### Auto-fix 1 — [Rule 3 - Blocker] Vendor symlink dans le worktree

**Trouvé lors de :** Vérification des tests
**Problème :** Le worktree n'avait pas de répertoire `vendor/`. `php artisan` et `vendor/bin/pest` ne trouvaient pas le bootstrap.
**Fix :** Créé `vendor → /Users/amnesia/dev/dloazur/vendor` (symlink) dans le worktree. Cohérent avec les plans précédents qui l'utilisaient implicitement.

### Déviation 2 — [Plan] passage-index.blade.php non modifié

**Trouvé lors de :** Task 2 (Blade)
**Raison :** Le fichier `resources/views/livewire/passage-index.blade.php` (bouton "Nouveau passage" disabled dans la liste) appartient au scope du plan 02-03 qui tourne en parallèle sur un agent sibling. Pour éviter les conflits de merge, la modification de ce fichier est déportée : soit 02-03 le crée directement avec le lien actif, soit un patch ultérieur s'en charge.
**Impact :** Mineur — la vue liste `/admin/passages` (si elle existe) affichera encore un bouton disabled. La saisie est accessible via le topbar (actif dans ce plan) et la sidebar.

### Déviation 3 — Feature test créé plutôt que Browser Playwright

**Trouvé lors de :** Task 1 (vérification)
**Raison :** Le plan préconisait un fallback Feature test si Playwright n'est pas disponible. Playwright n'est pas installé dans cet environnement CI. 4 Feature tests créés : GET 200, redirect anonyme, bindings Alpine + csrf-token, query string ?client_id.
**Impact :** Les tests Browser (offline IDB flow, badge sync) sont reportés en Plan QA mobile Phase 3.

## Pending Human Verify

### Device Smoke Test (Task 2)

**Bloqueur pour la mise en production terrain (Phase 3).**

| Étape | Description |
|-------|-------------|
| 1 | `npm run build && php artisan serve --host 0.0.0.0 --port 8000` |
| 2 | Login Pierre → "Nouveau passage" topbar |
| 3 | Vérifier visuellement vs `mockups/v1/passage.html` |
| 4 | Cas online : mesures + actions + photo → "Enregistrer" → badge → 0 |
| 5 | Cas offline : DevTools Offline → saisir → badge "1 en attente" → réseau ON → 0 |
| 6 | iOS Safari : visibilitychange trigger (pas de Background Sync API) |
| 7 | Lighthouse PWA mobile ≥ 80 |

Signal de reprise : `approved` | `issues:[description]` | `iOS Safari fail`

## Known Stubs

- `resources/views/admin/passages/create.blade.php` : `url()->previous()` dans le header "Retour" peut pointer vers `/login` si Pierre arrive direct sur la page (pas via la nav). Intentionnel pour MVP — un Plan futur ajoutera une route `admin.passages.index` et utilisera `route('admin.passages.index')` à la place.
- La date dans le header utilise `translatedFormat('l j F')` qui requiert `app.setLocale('fr')` ou `Carbon::setLocale('fr')` globalement. Si non configuré, affiche la date en anglais. À vérifier en Phase 2 finalisation.

## Threat Flags

Aucun nouveau surface de sécurité non prévu dans le registre STRIDE du plan.

Les mitigations prévues sont implémentées :
- T-5-01 : payload construit côté client, validé côté serveur par Plan 02-06 ✓
- T-5-02 : gestion 409 (UPSERT conditionnel D-38) + toast conflictMsg ✓
- T-5-03 : Blade `{{ }}` escape, pas d'innerHTML ✓
- T-5-04 : processPhoto compresse à ~300-400KB (Plan 02-04) ✓
- T-5-05 : storage.persist() au boot app.js (D-57) ✓
- T-5-06 : accept (@click Alpine bindings — comportement attendu) ✓

## Self-Check: PASSED

- [x] `resources/js/passage-form.js` contient crypto.randomUUID, addEventListener('online', visibilitychange, from './offline-queue, from './photo-pipeline, fetch.*'/api/passages', X-CSRF-TOKEN, 409 ✓
- [x] `resources/js/sync-drawer.js` contient syncDrawerStore, openOfflineDB, markStatus ✓
- [x] `resources/js/app.js` contient Alpine.data('passageForm', Alpine.store('syncDrawer' ✓
- [x] `resources/views/admin/passages/create.blade.php` : x-data="passageForm", capture="environment", x-show="!online", Mesures de l'eau, Actions menées, Mot pour le client, Enregistrer le passage, Brouillon sauvegardé ✓
- [x] `resources/views/components/admin/sync-drawer.blade.php` : Synchro en attente, Synchroniser maintenant ✓
- [x] `resources/views/components/admin/sync-badge.blade.php` : offlineQueue.pendingCount, sync-drawer:open ✓
- [x] `resources/views/components/admin/pwa-update-toast.blade.php` : Mise à jour disponible ✓
- [x] `resources/views/components/admin/topbar.blade.php` : route('admin.passages.create'), sync-badge ✓
- [x] `resources/views/components/admin/mobile-bottom-nav.blade.php` : offlineQueue.pendingCount (×2 avec badge) ✓
- [x] `resources/css/app.css` : [x-cloak], h-13, h-18 ✓
- [x] `php artisan route:list --path=admin/passages` → 1 route admin.passages.create ✓
- [x] `npm run build` exit 0 ✓
- [x] Commit fff16a4 ✓
