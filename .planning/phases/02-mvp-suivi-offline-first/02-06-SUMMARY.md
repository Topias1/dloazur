---
phase: 02-mvp-suivi-offline-first
plan: "06"
subsystem: api-sync
tags: [api, upsert, idempotence, photos, r2, postgresql, tdd]
dependency_graph:
  requires: ["02-01"]
  provides: ["POST /api/passages UPSERT idempotent", "POST /api/passages/{uuid}/photos R2 upload idempotent"]
  affects: ["02-05 (sync Alpine → serveur)", "02-07 (portail client photos)"]
tech_stack:
  added: []
  patterns:
    - "DB::affectingStatement() pour INSERT ... ON CONFLICT ... WHERE status='draft'"
    - "PhotoMeta::updateOrCreate(['client_uuid' => ...]) pour idempotence photo"
    - "Storage::disk('r2')->putFile() direct sans medialibrary"
    - "Bindings PHP pour timestamps (compatibilité SQLite tests + Postgres prod)"
key_files:
  created:
    - app/Http/Controllers/Api/PassageController.php
    - app/Http/Controllers/Api/PassagePhotoController.php
    - tests/Feature/PassageApiTest.php
    - tests/Feature/PhotoUploadTest.php
  modified:
    - app/Models/PhotoMeta.php
    - app/Models/Passage.php
    - routes/api.php
decisions:
  - "Bindings PHP pour NOW() à la place de SQL NOW() — compatibilité SQLite tests (phpunit.xml DB_CONNECTION=sqlite)"
  - "Tests auth 401 (pas 302) — bootstrap/app.php shouldRenderJsonWhen(api/*) retourne 401 JSON pour requêtes Accept:application/json"
  - "::jsonb supprimé du SQL brut — Postgres accepte JSON texte via binding PDO pour colonne JSONB"
metrics:
  duration: "~45 min"
  completed: "2026-05-28T17:51:49Z"
  tasks_completed: 2
  tasks_total: 2
  files_changed: 7
---

# Phase 02 Plan 06: Sync API — UPSERT idempotent passages + photos R2 Summary

UPSERT conditionnel `ON CONFLICT (client_uuid) DO UPDATE WHERE status='draft'` + upload photos vers Cloudflare R2 avec `PhotoMeta::updateOrCreate` idempotent sur `client_uuid`, 17 tests Pest GREEN (TDD RED → GREEN).

## Tasks Completed

| # | Task | Commit | Files |
|---|------|--------|-------|
| 1 | RED — PassageApiTest + PhotoUploadTest + modèles | `0249f4b` | PhotoMeta.php, Passage.php, PassageApiTest.php, PhotoUploadTest.php |
| 2 | GREEN — PassageController + PassagePhotoController + routes | `bcdb45d` | PassageController.php, PassagePhotoController.php, routes/api.php, tests (fix) |

## What Was Built

### POST /api/passages (PassageController)

UPSERT Postgres conditionnel :

```sql
INSERT INTO passages (...) VALUES (...)
ON CONFLICT (client_uuid) DO UPDATE SET ...
WHERE passages.status = 'draft'
```

- **D-38** : `DB::affectingStatement()` retourne 0 si le passage existe avec `status != 'draft'` → 409 `{ error: 'already_closed', server_state: {...} }` (D-40)
- **D-39** : `client_uuid` généré côté Alpine via `crypto.randomUUID()`, reçu en body JSON
- **T-6-05** : bindings nommés PDO, zéro interpolation string
- Validation complète : `required|uuid`, mesures `nullable|numeric`, actions `nullable|array`, strings `max:2000`

### POST /api/passages/{uuid}/photos (PassagePhotoController)

- **D-42** : `PhotoMeta::updateOrCreate(['client_uuid' => $photoUuid], [...])` — UNIQUE constraint `photos_meta.client_uuid` (migration 02-01) garantit l'idempotence par photo
- **D-48** : `mimes:jpeg,jpg` + `max:10240` (10 MB) — T-6-02 (anti-malware)
- `Storage::disk('r2')->putFile("passages/{$passageUuid}/photos", $file)` — path aléatoire généré par Laravel (T-6-03)
- 404 explicite si passage inconnu (`firstOrFail()`)

### Modifications modèles

- **PhotoMeta** : `client_uuid` ajouté à `$fillable` + cast `'string'`
- **Passage** : helper `latestPhoto(): HasOne` via `latestOfMany('captured_at')` (utile Plan 02-07)

### Routes

```
POST api/passages        → api.passages.store
POST api/passages/{uuid}/photos → api.passages.photos.store (whereUuid constraint)
```

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] SQL NOW() et ::jsonb incompatibles avec SQLite tests**
- **Trouvé pendant :** Task 2 GREEN — 500 errors sur tests UPSERT
- **Cause :** `phpunit.xml` utilise `DB_CONNECTION=sqlite` et `DB_DATABASE=:memory:`. SQLite ne reconnaît pas `NOW()` (retournerait la chaîne littérale "NOW()") ni le cast `::jsonb` (syntaxe Postgres exclusivement).
- **Fix :** Timestamps passés comme bindings PHP `$now = now()->toDateTimeString()` avec clés distinctes `:synced_at`, `:synced_at2`, `:updated_at`, `:updated_at2`. Cast `::jsonb` supprimé — Postgres accepte du JSON texte via binding PDO pour une colonne JSONB.
- **Impact prod :** Aucun. Postgres prod reçoit des valeurs correctement typées via PDO bindings.
- **Fichiers modifiés :** `app/Http/Controllers/Api/PassageController.php`

**2. [Rule 1 - Bug] Tests auth attendaient 302 redirect mais reçoivent 401 JSON**
- **Trouvé pendant :** Task 2 GREEN — tests auth échouaient avec 401 au lieu de redirect
- **Cause :** `bootstrap/app.php` configure `$exceptions->shouldRenderJsonWhen(fn => $request->is('api/*'))` — toutes les requêtes `api/*` non authentifiées reçoivent 401 JSON, y compris avec `postJson()` (qui envoie `Accept: application/json`). Le plan spécifiait `assertRedirect('/login')` mais c'est incompatible avec cette configuration.
- **Fix :** Tests 6 (PassageApiTest) et 7 (PhotoUploadTest) corrigés pour `assertStatus(401)`. Ce comportement est correct — l'Alpine PWA gère le 401 JSON, pas une redirect HTML.
- **Fichiers modifiés :** `tests/Feature/PassageApiTest.php`, `tests/Feature/PhotoUploadTest.php`

## Known Stubs

Aucun stub — les controllers retournent des réponses réelles basées sur la DB et le storage R2.

## TDD Gate Compliance

- RED gate : commit `0249f4b` — `test(02-06): RED — PassageApi + PhotoUpload tests` (16/17 tests RED, 1 test 404 passe par nature)
- GREEN gate : commit `bcdb45d` — `feat(02-06): GREEN — UPSERT idempotent passages + photos R2` (17/17 tests GREEN)

## Threat Surface Scan

Aucun nouveau endpoint ou pattern de sécurité hors du `<threat_model>` du plan. Les mitigations T-6-01..T-6-07 sont toutes implémentées :

- T-6-01 (passage clos immuable) : `WHERE passages.status = 'draft'` + Test 3
- T-6-02 (mime check) : `mimes:jpeg,jpg` + `max:10240` + Test 3 PhotoUpload
- T-6-03 (path R2) : `putFile()` aléatoire, pas de nom user-supplied
- T-6-05 (injection SQL) : bindings nommés PDO exclusivement
- T-6-06 (auth) : `middleware(['web','auth'])` actif, CSRF exempt mais session cookie auth

## Self-Check: PASSED

- All 7 created/modified files found on disk
- Both commits (0249f4b, bcdb45d) verified in git log
- 17/17 new tests GREEN + 15/15 MigrationsTest GREEN (32/32 total)
- Routes `api.passages.store` + `api.passages.photos.store` verified via `php artisan route:list`
