---
phase: 01-vitrine-fondations
plan: 02
status: complete
completed_at: 2026-05-28T13:43:00Z
commits: 2
requirements:
  - SITE-01
  - SITE-04
  - AUTH-01
subsystem: database/schema
tags: [migrations, eloquent, factories, seeder, d-07, d-08, d-09, d-28]
dependency_graph:
  requires:
    - plan: 01-01
      reason: Laravel 13 scaffold + all Phase 1 packages pre-installed
  provides:
    - all business tables in Postgres (clients, piscines, produits, contrats, passages, photos_meta, factures, signatures, diagnostics, google_reviews)
    - Eloquent models with fillable + casts + relationships
    - factories (Client, Piscine, Passage)
    - env-gated DatabaseSeeder + DevDataSeeder
  affects:
    - plan: 01-03
      via: clients/piscines tables used by future portail-client routes
    - plan: 01-04
      via: google_reviews cache table consumed by SyncGoogleReviewsCommand
    - plan: 01-05
      via: users table + passages table ready for PierreSeeder + Fortify auth
    - phase: 02
      via: passages.client_uuid UUID unique enables offline idempotent upsert
    - phase: 03
      via: factures.odoo_id + factures.numero + factures.tva_rate pre-set for Odoo integration
tech_stack:
  added: []
  patterns:
    - anonymous-class migrations (Laravel 13 default)
    - env-gated DatabaseSeeder (D-09 pattern)
    - savepoint-based unique constraint tests for PostgreSQL
key_files:
  created:
    - database/migrations/2026_05_28_000001_create_clients_table.php
    - database/migrations/2026_05_28_000002_create_piscines_table.php
    - database/migrations/2026_05_28_000003_create_produits_table.php
    - database/migrations/2026_05_28_000004_create_contrats_table.php
    - database/migrations/2026_05_28_000005_create_passages_table.php
    - database/migrations/2026_05_28_000006_create_photos_meta_table.php
    - database/migrations/2026_05_28_000007_create_factures_table.php
    - database/migrations/2026_05_28_000008_create_signatures_table.php
    - database/migrations/2026_05_28_000009_create_diagnostics_table.php
    - database/migrations/2026_05_28_000010_create_google_reviews_table.php
    - app/Models/Client.php
    - app/Models/Piscine.php
    - app/Models/Produit.php
    - app/Models/Contrat.php
    - app/Models/Passage.php
    - app/Models/PhotoMeta.php
    - app/Models/Facture.php
    - app/Models/Signature.php
    - app/Models/Diagnostic.php
    - app/Models/GoogleReview.php
    - database/factories/ClientFactory.php
    - database/factories/PiscineFactory.php
    - database/factories/PassageFactory.php
    - database/seeders/DevDataSeeder.php
    - tests/Feature/MigrationsTest.php
  modified:
    - database/seeders/DatabaseSeeder.php
decisions:
  - D-07 schema: all 10 business tables created in one deploy — clients, piscines, produits, contrats, passages, photos_meta, factures, signatures, diagnostics, google_reviews
  - D-08 columns: passages.client_uuid UUID unique (offline idempotence), passages.signature_path nullable (Phase 3), factures.odoo_id bigint nullable (Phase 3 Odoo bridge)
  - D-09 seeder: DatabaseSeeder env-gated to local/testing only; production is strict no-op
  - D-28 amended: google_reviews is a denormalized passive cache (no FK), populated by SyncGoogleReviewsCommand
  - contrats.type widened to string(24) (DEVIATION — see below)
  - PostgreSQL savepoint pattern: unique constraint tests use SAVEPOINT/ROLLBACK to recover from aborted transactions
metrics:
  duration_minutes: 45
  tasks_completed: 2
  tasks_total: 2
  files_created: 25
  files_modified: 1
  tests_passed: 13
  tests_total: 13
---

# Phase 01 Plan 02: Business Schema — SUMMARY

## One-liner

Full business schema (10 tables) + Eloquent models + factories + env-gated dev seeder, with D-07/D-08/D-09 forward-compat columns baked in from the first deploy.

## What was built

A complete Postgres 17 schema covering every business entity needed for all Phase 1-5 features, deployed in a single idempotent migration set so future phases never need to retrofit columns.

### Migration apply order (FK dependency safe)

| # | File | Tables / Key columns |
|---|------|---------------------|
| 1 | `2026_05_28_000001_create_clients_table.php` | clients — uuid unique, magic_link_token, magic_link_expires_at |
| 2 | `2026_05_28_000002_create_piscines_table.php` | piscines — FK→clients, volume_m3, equipements JSON |
| 3 | `2026_05_28_000003_create_produits_table.php` | produits — sku unique, prix_ht, actif |
| 4 | `2026_05_28_000004_create_contrats_table.php` | contrats — FK→clients, type string(24) |
| 5 | `2026_05_28_000005_create_passages_table.php` | passages — client_uuid UUID unique (D-08), signature_path nullable (D-08), FK→clients/piscines nullOnDelete |
| 6 | `2026_05_28_000006_create_photos_meta_table.php` | photos_meta — FK→passages cascadeOnDelete, disk default 'r2' |
| 7 | `2026_05_28_000007_create_factures_table.php` | factures — numero nullable unique (Pitfall 5), tva_rate decimal(4,2) default 8.50 (Pitfall 4), odoo_id nullable (D-08) |
| 8 | `2026_05_28_000008_create_signatures_table.php` | signatures — FK→passages/clients cascadeOnDelete |
| 9 | `2026_05_28_000009_create_diagnostics_table.php` | diagnostics — FK→clients/piscines nullOnDelete, disclaimer_accepted_at (DIAG-03) |
| 10 | `2026_05_28_000010_create_google_reviews_table.php` | google_reviews — google_review_id unique, rating unsignedTinyInteger, indexes on rating+reviewed_at (D-28 amended) |

### Critical D-08 columns confirmed

| Column | Table | Type | Constraint | Purpose |
|--------|-------|------|-----------|---------|
| `client_uuid` | passages | UUID | UNIQUE (not FK) | Offline idempotence — Phase 2 IndexedDB sync |
| `signature_path` | passages | string nullable | — | Phase 3 electronic signature |
| `odoo_id` | factures | unsignedBigInteger nullable | — | Phase 3 Odoo bridge |
| `numero` | factures | string nullable | UNIQUE | Phase 3 CGI sequential number |
| `tva_rate` | factures | decimal(4,2) | DEFAULT 8.50 | Martinique TVA pre-set (Pitfall 4) |

### Eloquent models

All 10 models created with `$fillable`, `$casts`, and relationships:
- **Client**: hasMany(Piscine, Passage, Contrat, Facture)
- **Piscine**: belongsTo(Client), hasMany(Passage)
- **Passage**: belongsTo(Client, Piscine), hasMany(PhotoMeta), hasOne(Signature)
- **Facture**: belongsTo(Client, Contrat, Passage)
- **PhotoMeta**: `$table = 'photos_meta'` override, belongsTo(Passage)
- **GoogleReview**: `$table = 'google_reviews'`, no factory, no relationships (passive cache)

### Factories

| Factory | Key features |
|---------|-------------|
| ClientFactory | fr_FR faker, Str::uuid(), Martinique cities, numerify('0696######') |
| PiscineFactory | client_id via Client::factory(), French pool types/filtration/treatment |
| PassageFactory | client_uuid via Str::uuid(), ph_avant=7.2, chlore_libre=1.5, status='draft' |

### DatabaseSeeder env gate (D-09)

```php
if (! app()->environment(['local', 'testing'])) { return; }
$this->call(DevDataSeeder::class);
```

Verified: `APP_ENV=production php artisan db:seed --force` outputs "DatabaseSeeder skipped — production environment" and creates 0 client rows.

### DevDataSeeder (dev/test fixtures)

Creates: 3 clients (fr_FR names) + 1 piscine each + 1 contrat each + 5 produits (standard catalogue).

### MigrationsTest results

`./vendor/bin/pest tests/Feature/MigrationsTest.php --ci` → **13/13 PASSED** in ~410ms

| # | Test | Coverage |
|---|------|---------|
| 1 | asserts every business table exists | D-07: all 10 tables |
| 2 | asserts passages.client_uuid is unique and indexed | D-08: offline idempotence |
| 3 | asserts passages.signature_path is nullable string | D-08: Phase 3 signature |
| 4 | asserts factures.numero is nullable unique string distinct from id | Pitfall 5 |
| 5 | asserts factures.odoo_id is nullable bigint | D-08: Phase 3 Odoo |
| 6 | asserts factures.tva_rate defaults to 8.50 | Pitfall 4: Martinique TVA |
| 7 | asserts clients.uuid is unique and is UUID v4 valid | T-2-02 |
| 8 | asserts clients.magic_link_token and magic_link_expires_at exist | Phase 2 magic link placeholder |
| 9 | asserts photos_meta.disk defaults to r2 | Plan 01 disk wiring |
| 10 | asserts diagnostics.disclaimer_accepted_at exists for DIAG-03 | Phase 5 disclaimer |
| 11 | asserts contrats.type accepts the three contract kinds | contrats enum values |
| 12 | asserts DatabaseSeeder is a no-op in production | D-09: production safety |
| 13 | asserts google_reviews table and critical columns for D-28 amended | D-28: Google Places cache |

## Commits

| Commit | Type | Description |
|--------|------|-------------|
| `51b99c1` | test | RED — MigrationsTest covering D-07 and D-08 (13 tests, all failing) |
| `1463455` | feat | GREEN — 10 migrations + 10 models + 3 factories + env-gated seeder |

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] contrats.type widened from string(16) to string(24)**
- **Found during:** Task 2, GREEN phase
- **Issue:** Plan specified `string(16)` for contrats.type but `'forfait_saisonnier'` is 18 characters — Postgres raised `value too long for type character varying(16)` on insert
- **Fix:** Changed column to `string(24)` — accommodates all 3 values (ponctuel=9, forfait_mensuel=15, forfait_saisonnier=18) with room for future types
- **Files modified:** `database/migrations/2026_05_28_000004_create_contrats_table.php`
- **Commit:** `1463455`

**2. [Rule 1 - Bug] PostgreSQL transaction recovery via SAVEPOINTs in unique constraint tests**
- **Found during:** Task 2, GREEN phase
- **Issue:** After a `UniqueConstraintViolationException`, PostgreSQL marks the current transaction as aborted and rejects all further SQL until rollback. The `expect()->toThrow()` helper doesn't run inside a nested transaction, so subsequent inserts in the same test failed with "current transaction is aborted"
- **Fix:** Replaced `expect()->toThrow()` pattern with explicit SAVEPOINT/ROLLBACK TO SAVEPOINT/RELEASE pattern for tests 2, 4, 7, 13
- **Files modified:** `tests/Feature/MigrationsTest.php`
- **Commit:** `1463455`

**3. [Rule 2 - Missing critical functionality] Installed PostgreSQL 17 locally**
- **Found during:** Task 1, before running tests
- **Issue:** No local Postgres server running; .env.testing points to pgsql:127.0.0.1:5432
- **Fix:** `brew install postgresql@17 && brew services start postgresql@17` + created testing user/database
- **Impact:** Local CI-equivalent test run now possible; this was an environment setup issue, not a code issue

## TDD Gate Compliance

- RED gate commit: `51b99c1` (`test(01-02): add MigrationsTest covering D-07 and D-08 (RED)`) — 13 tests, 0 passing
- GREEN gate commit: `1463455` (`feat(01-02): complete business schema + Eloquent models + dev seeder per D-07/D-08/D-09`) — 13 tests, 13 passing

## Threat Surface Scan

No new network endpoints, auth paths, or external-facing changes introduced. All new surface is internal (Eloquent models, migrations, seeders) with the following threat mitigations applied per T-2-01/T-2-02/T-2-06:

- T-2-01 mitigated: DatabaseSeeder env gate verified by Test 12
- T-2-02 mitigated: clients.uuid uses Str::uuid() (UUID v4, 122 bits entropy)
- T-2-06 mitigated: factures.numero unique constraint at DB level confirmed by Test 4

## Known Stubs

None. No UI components or rendered data in this plan — pure schema layer.

## Self-Check: PASSED

| Check | Result |
|-------|--------|
| All 10 migration files exist under database/migrations/ | PASSED |
| All 10 model files exist under app/Models/ | PASSED |
| Passage.php contains fillable, casts, belongsTo Client/Piscine, client_uuid/actions/visited_at/synced_at casts | PASSED |
| Facture.php contains belongsTo Client, tva_rate decimal:2, lignes array, odoo_id, numero | PASSED |
| PhotoMeta.php contains `$table = 'photos_meta'` | PASSED |
| ClientFactory.php contains Str::uuid() | PASSED |
| PassageFactory.php contains 'client_uuid' => (string) Str::uuid() | PASSED |
| DatabaseSeeder.php contains app()->environment(['local', 'testing']) | PASSED |
| DevDataSeeder.php contains Client::factory() | PASSED |
| Commit 51b99c1 exists (RED gate) | PASSED |
| Commit 1463455 exists (GREEN gate) | PASSED |
| php artisan migrate:fresh --env=testing exits 0 (13 migrations applied) | PASSED |
| ./vendor/bin/pest tests/Feature/MigrationsTest.php --ci exits 0 with 13 passing | PASSED |
| php artisan db:seed --env=testing runs DevDataSeeder, 3 Client rows created | PASSED |
| APP_ENV=production php artisan db:seed --force produces 0 Client rows | PASSED |
