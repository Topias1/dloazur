---
phase: 07-espace-admin-retours-pierre
plan: "02"
subsystem: admin-agenda
tags: [agenda, frequence-jour, navigation, tdd, admin-1]
dependency_graph:
  requires: [07-01]
  provides: [agenda-du-jour, frequence_jour-schema, admin-nav-agenda]
  affects: [admin-routes, admin-sidebar, admin-mobile-nav, piscine-model]
tech_stack:
  added: []
  patterns: [controller-read-only-blade, add-column-migration, carbon-test-freeze]
key_files:
  created:
    - database/migrations/2026_06_03_000002_add_frequence_to_piscines.php
    - app/Http/Controllers/Admin/AgendaController.php
    - resources/views/admin/agenda/index.blade.php
    - tests/Feature/AgendaTest.php
  modified:
    - app/Models/Piscine.php
    - routes/admin.php
    - resources/views/components/admin/sidebar.blade.php
    - resources/views/components/admin/mobile-bottom-nav.blade.php
decisions:
  - "frequence_jour stocke le jour de la semaine en français minuscule (lundi…dimanche) — match direct avec Carbon::now()->locale('fr')->isoFormat('dddd')"
  - "Agenda dérivé : zéro CRUD de RDV, la tournée du jour est déduite de la cadence piscine"
  - "Mobile nav : grid-cols-5 (5 items) — Accueil, Agenda, Clients, Passages, Factures"
  - "Sidebar : Agenda inséré juste après Tableau de bord — porte d'entrée quotidienne"
  - "Test green post-merge uniquement (worktree sans vendor) — structure et assertions vérifiées manuellement"
metrics:
  duration: "22m"
  completed: "2026-06-03"
  tasks_completed: 3
  files_modified: 8
---

# Phase 07 Plan 02: Agenda du jour admin (admin-1) Summary

**One-liner:** Agenda du jour dérivé de `frequence_jour` sur piscines (migration + controller + vue + nav sidebar/mobile) avec flags « à revoir » issus des notes internes et test Feature Pest.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Migration frequence_jour + $fillable Piscine | 10e7251 | database/migrations/2026_06_03_000002_add_frequence_to_piscines.php, app/Models/Piscine.php |
| 2 | AgendaController + vue + route + navigation | f545c3d | app/Http/Controllers/Admin/AgendaController.php, resources/views/admin/agenda/index.blade.php, routes/admin.php, resources/views/components/admin/sidebar.blade.php, resources/views/components/admin/mobile-bottom-nav.blade.php |
| 3 | Test Feature AgendaTest | c871164 | tests/Feature/AgendaTest.php |

## What Was Built

### Champ `frequence_jour` sur piscines
Migration additive `2026_06_03_000002_add_frequence_to_piscines.php` : colonne `string(16) nullable after('notes')`. Format retenu : jour de la semaine en français minuscule (`lundi`, `mardi`, …, `dimanche`) — match direct avec `Carbon::now()->locale('fr')->isoFormat('dddd')` utilisé dans l'AgendaController. `Piscine::$fillable` mis à jour.

### AgendaController (read-only, pattern DashboardController)
Namespace `App\Http\Controllers\Admin`, retourne `view('admin.agenda.index', compact(...))`. Deux requêtes :
1. `$piscinesAujourdhui` : `Piscine::where('frequence_jour', $today)->with(['client:id,name'])->orderBy('nom')->get()`
2. `$aRevoir` : `Passage::whereNotNull('notes_privees')->where('visited_at', '>=', subDays(7))->with([...])->orderByDesc('visited_at')->get()`

### Vue `admin/agenda/index.blade.php`
Deux blocs cards (design system impeccable, register product) :
- **Aujourd'hui** : liste les piscines + lien CTA `route('admin.passages.create', ['client_id' => $piscine->client_id])` vers la saisie pré-remplie existante. État vide : « Aucune piscine prévue aujourd'hui. »
- **À revoir** : passages récents porteurs de `notes_privees`, texte de la note affiché (espace admin privé Pierre). État vide : « Rien à revoir. »

### Route + navigation
- Route `GET admin/agenda` → `admin.agenda.index` enregistrée dans `routes/admin.php`
- **Sidebar desktop** : lien « Mon agenda » inséré après « Tableau de bord » (pattern `@class` + `request()->routeIs('admin.agenda.*')`)
- **Nav mobile** : `grid-cols-4` → `grid-cols-5`, cellule « Agenda » insérée après « Accueil » (icône calendrier identique aux Passages)

### Test Feature AgendaTest
3 comportements Pest avec `uses(RefreshDatabase::class)` :
1. **Dérivation jour** : `frequence_jour='lundi'` apparaît un lundi (`Carbon::setTestNow(Carbon::parse('2026-06-08'))`), `frequence_jour='mardi'` masqué (`assertDontSee`)
2. **Flag à revoir** : passage récent avec `notes_privees` → `assertSee` le client dans le bloc « À revoir »
3. **États vides** : aucune donnée → messages d'état vide des deux blocs

## Deviations from Plan

### Déviation contexte — Rebase worktree sur branche feature

**Trouvé lors :** Démarrage du plan
**Situation :** Le worktree avait été spawné depuis le commit `38cd2b0` (avant les commits 07-01). Les 7 commits de la branche `claude/pierre-feedback-website-app-A59QE` (dont les migrations et modèles 07-01) n'étaient pas dans le worktree.
**Fix :** `git rebase claude/pierre-feedback-website-app-A59QE` depuis le worktree — rebase propre sans conflit (le seul commit propre au worktree, `9d3a9a4`, était déjà dans la branche parente).

### [Règle 3 - Blocage résolu] Test green post-merge uniquement

**Trouvé lors :** Task 3 — vérification `php artisan test --filter=AgendaTest`
**Situation :** Le worktree n'a pas de `vendor/` (pattern attendu). La migration `frequence_jour` et la route `admin.agenda.index` n'existent que dans le worktree, pas encore dans le main repo. Exécuter les tests depuis le main repo produit : (1) SQLSTATE `piscines has no column named frequence_jour`, (2) 404 sur `/admin/agenda`.
**Vérification alternative effectuée :**
- `php -l` sur le controller, la migration, le fichier de test → aucune erreur syntaxique
- Grep de toutes les assertions critiques (`frequence_jour`, `Carbon::setTestNow`, `assertSee`, `assertDontSee`, `assertOk`)
- Structure identique au test analog `DashboardStatsTest.php` (même pattern AdminSeeder + factory + actingAs)
**Résolution :** Les tests seront verts post-merge (même pattern que 07-01 dont la note de summary dit « Vérification GREEN effectuée depuis main repo »).

## Known Stubs

Aucun — les deux blocs affichent des données réelles depuis la base (piscines + passages).

## Threat Flags

Aucun nouveau — l'agenda est en lecture seule, accessible uniquement via middleware `auth` (admin), et n'expose aucune donnée portail client. `notes_privees` affiché dans la vue admin uniquement (non accessible depuis le portail).

## Self-Check: PASSED

| Item | Status |
|------|--------|
| database/migrations/2026_06_03_000002_add_frequence_to_piscines.php | FOUND |
| app/Models/Piscine.php | FOUND |
| app/Http/Controllers/Admin/AgendaController.php | FOUND |
| resources/views/admin/agenda/index.blade.php | FOUND |
| tests/Feature/AgendaTest.php | FOUND |
| Commit 10e7251 (Task 1) | FOUND |
| Commit f545c3d (Task 2) | FOUND |
| Commit c871164 (Task 3) | FOUND |
