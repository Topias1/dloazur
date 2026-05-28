---
phase: 02-mvp-suivi-offline-first
plan: 01
subsystem: auth
tags: [laravel, auth, guard, eloquent, authenticatable, migration, pwa, service-worker, medialibrary, magiclink, flysystem, s3]

# Dependency graph
requires:
  - phase: 01-vitrine-fondations
    provides: "schema DB complet (photos_meta, passages, clients), bootstrap/app.php, modèles Eloquent, tests Pest Phase 1"

provides:
  - "Migration photos_meta.client_uuid UUID UNIQUE NULLABLE (idempotence photo D-42)"
  - "Client model extends Authenticatable + Notifiable (guard 'clients' opérationnel)"
  - "Guard 'clients' isolé du guard 'web' dans config/auth.php"
  - "cesargb/laravel-magiclink ^2.27 + spatie/laravel-medialibrary ^11.22 + league/flysystem-aws-s3-v3 ^3 installés"
  - "ServiceWorkerHeaders middleware (Service-Worker-Allowed: / sur /build/sw.js)"
  - "routes/api.php + routes/portail.php stubs câblés dans bootstrap/app.php"
  - "CSRF exempt api/* via validateCsrfTokens"
  - "ROADMAP Phase 2 SC#2 plateforme-agnostique (D-37 : sur smartphone)"

affects: [02-02, 02-04, 02-06, 02-07]

# Tech tracking
tech-stack:
  added:
    - "cesargb/laravel-magiclink v2.27.1 (magic link portail client)"
    - "spatie/laravel-medialibrary v11.22.x (gestion médias passages)"
    - "league/flysystem-aws-s3-v3 v3.34.0 (driver S3/R2)"
  patterns:
    - "Guard multi-tenant Laravel : guard 'clients' session/eloquent isolé de guard 'web'"
    - "CSRF exempt + auth guard session pour routes API same-domain (T-2-03)"
    - "Middleware appended global pour headers HTTP spécifiques (ServiceWorkerHeaders)"
    - "Stubs routes vides câblés bootstrap/app.php pour plans downstream"

key-files:
  created:
    - "database/migrations/2026_05_28_000011_add_client_uuid_to_photos_meta.php"
    - "database/migrations/2026_05_28_132229_create_media_table.php"
    - "app/Http/Middleware/ServiceWorkerHeaders.php"
    - "routes/api.php"
    - "routes/portail.php"
    - "tests/Feature/ClientsGuardTest.php"
  modified:
    - "app/Models/Client.php (extends Authenticatable + Notifiable)"
    - "config/auth.php (guard clients + provider clients)"
    - "bootstrap/app.php (routes api/portail + SW middleware + CSRF exempt)"
    - "tests/Feature/MigrationsTest.php (tests 14-15 photos_meta.client_uuid)"
    - ".planning/ROADMAP.md (D-37 sur iPhone → sur smartphone)"
    - "composer.json / composer.lock"

key-decisions:
  - "Test 14 réécrit sans information_schema (SQLite-incompatible) → test nullabilité par INSERT sans client_uuid"
  - "vendor/ installé localement dans le worktree (symlink vendor → main repo cassait inferBasePath Laravel)"
  - "flysystem-aws-s3-v3 ajouté explicitement (medialibrary ne le tire pas comme dépendance obligatoire)"
  - "medialibrary migrations publiées et appliquées (table media requise pour service provider auto-discover)"

patterns-established:
  - "SAVEPOINT pattern pour tests UNIQUE Postgres dans SQLite-compatible test suite"
  - "Guard isolation : Auth::guard('clients') vs Auth::guard('web') — provider séparé, session séparée"
  - "bootstrap/app.php then: block pour routes partitionnées par middleware"

requirements-completed: [AUTH-02, AUTH-03, AUTH-04, PASS-03, PASS-04]

# Metrics
duration: 65min
completed: 2026-05-28
---

# Phase 2 Plan 01: Wave 0 Fondations Summary

**Guard clients isolé (Client extends Authenticatable), photos_meta.client_uuid UUID UNIQUE, packages magiclink+medialibrary+flysystem installés, SW scope header middleware, route stubs API/portail câblés**

## Performance

- **Duration:** ~65 min
- **Started:** 2026-05-28T16:20:00Z
- **Completed:** 2026-05-28T17:25:10Z
- **Tasks:** 3 (2 TDD + 1 auto)
- **Files modified:** 13

## Accomplishments

- Migration `photos_meta.client_uuid UUID UNIQUE NULLABLE` créée et appliquée, 2 tests dédiés verts (Tests 14-15 dans MigrationsTest)
- `Client extends Authenticatable + Notifiable`, guard `clients` opérationnel et isolé de `web` — 3 tests ClientsGuardTest verts
- `cesargb/laravel-magiclink` v2.27.1 + `spatie/laravel-medialibrary` ^11.22 + `league/flysystem-aws-s3-v3` v3.34 installés, migrations médias appliquées
- `ServiceWorkerHeaders` middleware + CSRF exempt `api/*` + route stubs câblés, ROADMAP D-37 corrigé

## Task Commits

1. **Task 1: Migration photos_meta.client_uuid + Tests 14-15** - `88671d9` (feat)
2. **Task 2: Client Authenticatable + guard clients + ClientsGuardTest** - `94e9dbb` (feat)
3. **Task 3: Packages composer + SW middleware + route stubs + ROADMAP D-37** - `d6ef58f` (feat)

## Files Created/Modified

- `database/migrations/2026_05_28_000011_add_client_uuid_to_photos_meta.php` — colonne client_uuid UUID UNIQUE NULLABLE
- `database/migrations/2026_05_28_132229_create_media_table.php` — table media spatie/medialibrary
- `app/Models/Client.php` — extends Authenticatable + use Notifiable (relations préservées)
- `config/auth.php` — guard 'clients' + provider 'clients' → App\Models\Client
- `app/Http/Middleware/ServiceWorkerHeaders.php` — Service-Worker-Allowed: / sur /build/sw.js
- `bootstrap/app.php` — routes api/portail enregistrées, CSRF exempt api/*, SW headers appended
- `routes/api.php` — stub vide (Plan 02-06)
- `routes/portail.php` — stub vide (Plan 02-07)
- `tests/Feature/ClientsGuardTest.php` — 3 tests guard isolation
- `tests/Feature/MigrationsTest.php` — Tests 14-15 client_uuid

## Decisions Made

- **Test 14 sans information_schema** : le test original interrogeait `information_schema.columns` (PostgreSQL uniquement). Réécrit pour tester la nullabilité par INSERT sans `client_uuid` → compatible SQLite et Postgres.
- **vendor local dans le worktree** : symlink vendor → main repo cassait `Application::inferBasePath()` (ClassLoader retournait le chemin main repo). `composer install` local résout définitivement le problème.
- **flysystem-aws-s3-v3 ajouté explicitement** : medialibrary ne tire pas ce package comme dépendance obligatoire (il est dans ses suggest), mais le projet utilise le disk R2 (S3-compatible). Ajouté pour éviter une erreur runtime en Plan 02-06.
- **Migrations medialibrary publiées** : `php artisan vendor:publish --tag=medialibrary-migrations` nécessaire pour que le service provider ne lève pas d'erreur sur la table `media` manquante en tests downstream.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Test 14 information_schema incompatible SQLite**
- **Found during:** Task 1 (TDD GREEN)
- **Issue:** Le test utilisait `information_schema.columns` (Postgres uniquement). La suite de tests utilise SQLite en mémoire (phpunit.xml), donc le test échouait même après la migration appliquée.
- **Fix:** Remplacement par un test de nullabilité via INSERT sans `client_uuid` — valide sur SQLite ET Postgres.
- **Files modified:** `tests/Feature/MigrationsTest.php`
- **Verification:** 15/15 MigrationsTest verts
- **Committed in:** 88671d9

**2. [Rule 3 - Blocking] vendor/ symlink cassait inferBasePath**
- **Found during:** Task 1 (RED phase — pest ne pouvait pas booter l'app)
- **Issue:** `ln -s /main/vendor vendor` → `Application::inferBasePath()` retournait le chemin main repo, le test ne trouvait pas l'app worktree.
- **Fix:** `composer install` dans le worktree pour un vendor local.
- **Files modified:** aucun code — opération filesystem.
- **Verification:** `vendor/bin/pest tests/Feature/MigrationsTest.php` passe après installation.
- **Committed in:** non committé (vendor est gitignored)

**3. [Rule 2 - Missing Critical] flysystem-aws-s3-v3 non installé automatiquement**
- **Found during:** Task 3 (vérification acceptance criteria)
- **Issue:** `composer show league/flysystem-aws-s3-v3` retournait une erreur. Le projet utilise le disk R2 (S3-compatible) — sans ce package, toute opération médias en production lèvera une erreur.
- **Fix:** `composer require league/flysystem-aws-s3-v3:"^3.0"` ajouté explicitement.
- **Files modified:** composer.json, composer.lock
- **Verification:** `composer show | grep flysystem-aws` retourne v3.34.0
- **Committed in:** d6ef58f

---

**Total deviations:** 3 auto-fixed (1 Rule 1 bug test, 1 Rule 3 blocking env, 1 Rule 2 missing critical dep)
**Impact on plan:** Tous nécessaires pour la correction et la compatibilité. Aucun scope creep.

## Issues Encountered

- Worktree sans vendor local : pattern inhabituel qui casse l'inférence du basePath Laravel. Solution pérenne = `composer install` dans chaque worktree (le vendor est gitignored, donc pas de conflit).
- Le test `information_schema.columns` est valide pour l'acceptance criteria final (CI Postgres) mais invalide en suite de tests locale (SQLite). Les tests Phase 1 avec savepoints fonctionnent car SQLite supporte les SAVEPOINTs depuis 3.6.8. L'`information_schema` n'est pas supporté — la nullabilité est testée comportementalement.

## Known Stubs

- `routes/api.php` — aucune route définie, stub vide intentionnel pour Plan 02-06
- `routes/portail.php` — aucune route définie, stub vide intentionnel pour Plan 02-07

Ces stubs sont intentionnels — leur plan cible est documenté.

## User Setup Required

None — fondations purement backend, aucune configuration externe requise.

## Next Phase Readiness

- **Plan 02-02** (CRUD clients/piscines) : guard `clients` + model Authenticatable prêts
- **Plan 02-04** (PWA/SW) : `ServiceWorkerHeaders` middleware câblé, `buildBase: '/build/'` à configurer dans `vite.config.js`
- **Plan 02-06** (endpoint API passages) : `routes/api.php` câblé avec `middleware(['web','auth'])->prefix('api')`, `photos_meta.client_uuid` en place pour UPSERT idempotent
- **Plan 02-07** (portail client) : `routes/portail.php` câblé avec `middleware('web')`, guard `clients` opérationnel
- Aucun bloqueur.

---
*Phase: 02-mvp-suivi-offline-first*
*Completed: 2026-05-28*
