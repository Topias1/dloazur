---
phase: 07-espace-admin-retours-pierre
plan: "01"
subsystem: api-offline-sync
tags: [bug-fix, tdd, notes-privees, upsert, privacy-invariant]
dependency_graph:
  requires: []
  provides: [notes_privees-persiste-en-base, invariant-portail-vie-privee]
  affects: [api/passages-upsert, passage-model, portail-timeline]
tech_stack:
  added: []
  patterns: [tdd-red-green, db-affecting-statement, named-pdo-bindings]
key_files:
  created:
    - database/migrations/2026_06_03_000001_add_notes_privees_to_passages.php
    - tests/Feature/Api/PassageNotesPriveesTest.php
  modified:
    - app/Models/Passage.php
    - app/Http/Controllers/Api/PassageController.php
    - tests/Pest.php
decisions:
  - "notes_privees est une colonne text nullable — aucun cast nécessaire (texte simple)"
  - "Invariant vie privée : PassageTimeline.php et sa vue blade ne sont PAS modifiés"
  - "Test worktree : uses(RefreshDatabase) sans uses(TestCase) explicite pour compatibilité merge"
  - "Vérification GREEN effectuée depuis main repo (copie temporaire) — worktree autoloader pointe vers main repo"
metrics:
  duration: "12m 17s"
  completed: "2026-06-03"
  tasks_completed: 2
  files_modified: 5
---

# Phase 07 Plan 01: Fix bug notes_privees (admin-2) Summary

**One-liner:** Correction bug perte silencieuse `notes_privees` à la synchro offline — migration + `$fillable` + upsert SQL (3 emplacements), invariant portail prouvé par test Pest.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| RED | Tests invariant notes_privees (failing) | 8d88204 | tests/Feature/Api/PassageNotesPriveesTest.php, tests/Pest.php |
| 1 | Migration notes_privees + $fillable Passage | 0dee451 | database/migrations/2026_06_03_000001_add_notes_privees_to_passages.php, app/Models/Passage.php |
| 2 | Fix upsert PassageController + tests GREEN | ea6c625 | app/Http/Controllers/Api/PassageController.php, tests/Feature/Api/PassageNotesPriveesTest.php |

## What Was Fixed

Le bug `admin-2` : `notes_privees` était saisie dans le formulaire offline, envoyée par le JS, validée par l'API — mais **silencieusement effacée à la synchro** car :

1. **Colonne absente** de la table `passages` (corrigée : migration add-column)
2. **Absente de `$fillable`** dans `Passage.php` (corrigée : ajoutée après `'notes'`)
3. **Absente de 3 emplacements de l'upsert** SQL dans `PassageController::store()` :
   - INSERT column list et VALUES : `notes_privees, :notes_privees` ajoutés
   - ON CONFLICT DO UPDATE SET : `notes_privees = EXCLUDED.notes_privees` ajouté
   - Bindings dict : `'notes_privees' => $data['notes_privees'] ?? null` ajouté

## Invariants Prouvés par Test

- **Test 1 (persistance)** : POST /api/passages avec `notes_privees: "code portail 1234"` → `assertDatabaseHas` confirme la valeur en base
- **Test 2 (vie privée)** : `Livewire::test(PassageTimeline::class)` → `assertDontSee('code portail 1234')` — la note privée n'apparaît jamais au portail client
- **Test 3 (idempotence)** : Second POST avec même `client_uuid` met à jour `notes_privees` via ON CONFLICT, statut reste `draft`

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocker] Fix condition détection worktree dans tests/Pest.php**
- **Found during:** Tentative RED des tests
- **Issue:** La condition `str_contains($worktreePath, 'worktrees')` dans le Pest.php du worktree ne corrigeait pas le problème réel : Pest charge TOUJOURS le `Pest.php` du repo principal (via `PHPUNIT_COMPOSER_INSTALL`), pas celui du worktree. La condition dans le repo principal utilisait `dirname(__DIR__)` qui résout vers le repo principal (sans 'worktrees'), donc la branche worktree ne se déclenchait jamais.
- **Fix:** Le test utilise `uses(RefreshDatabase::class)` sans `uses(TestCase::class)` explicite. La vérification GREEN a été effectuée depuis le repo principal avec copie temporaire des fichiers modifiés (restauration immédiate après). Tests 3/3 GREEN confirmés.
- **Files modified:** tests/Pest.php (amélioration de la logique de détection — en attente de merge pour effet réel)
- **Commits:** 8d88204

### Contrainte Projet — Worktree Autoloader Poisoning

La contrainte documentée dans la mémoire projet s'est matérialisée : le vendor symlinké depuis le repo principal fait que le classloader PSR-4 résout `App\` vers `/Users/amnesia/dev/dloazur/app/` (repo principal), pas vers le worktree. Les tests Pest exécutés depuis le worktree chargent donc le code **original non patché** du repo principal.

**Impact** : les tests ne peuvent pas être rendus verts de façon autonome depuis le worktree avec vendor symlinké.

**Résolution** : vérification GREEN effectuée par copie temporaire vers le repo principal (3/3 tests passent). Le repo principal a été restauré immédiatement. Le gate post-merge (CI) confirmera la correction définitive.

**Fichiers concernés** : `app/Http/Controllers/Api/PassageController.php`, `app/Models/Passage.php`, `database/migrations/2026_06_03_000001_add_notes_privees_to_passages.php`, `tests/Feature/Api/PassageNotesPriveesTest.php`

## Known Stubs

Aucun stub. Toutes les données transitent réellement par l'upsert SQL après ce fix.

## Threat Flags

Aucune nouvelle surface de sécurité introduite. `notes_privees` est une colonne interne — elle n'est pas exposée au portail client (invariant vérifié par test).

## Self-Check: PASSED

- migration : FOUND
- Passage.php : FOUND (notes_privees in $fillable)
- PassageController.php : FOUND (5 occurrences notes_privees)
- PassageNotesPriveesTest.php : FOUND
- SUMMARY.md : FOUND
- Commits 8d88204, 0dee451, ea6c625 : FOUND
- Invariant PassageTimeline : notes_privees ABSENT
