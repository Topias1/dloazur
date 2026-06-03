---
phase: 07-espace-admin-retours-pierre
plan: "03"
subsystem: passage-chimie-offline
tags: [pivot, offline-sync, alpine, indexeddb, api, chimie]
dependency_graph:
  requires: ["07-01"]
  provides: ["passage_produit pivot", "POST /api/passages/produits", "sélecteur produits offline"]
  affects: ["07-04 recap mensuel — source de données réelle"]
tech_stack:
  added: []
  patterns:
    - "BelongsToMany pivot avec prix_snapshot (prix HT au moment de la synchro)"
    - "Soft-fail sync produits : produits_pending flag + retry dans _flushQueue"
    - "Offline-safe data injection : Produit::where(actif) pré-chargé server-side → @js($produits)"
key_files:
  created:
    - database/migrations/2026_06_03_000003_create_passage_produit_table.php
    - app/Http/Controllers/Api/PassageProduitController.php
    - tests/Feature/Api/PassageProduitSyncTest.php
  modified:
    - app/Models/Passage.php
    - app/Models/Produit.php
    - app/Http/Controllers/Admin/PassageCreateController.php
    - resources/views/admin/passages/create.blade.php
    - resources/js/passage-form.js
    - routes/api.php
    - tests/Pest.php
decisions:
  - "prix_snapshot stocke prix HT brut — aucun calcul TVA (franchise 293 B, Phase 3)"
  - "sync() plutôt que attach() : idempotent, un second POST remplace l'ensemble du pivot"
  - "Soft fail produits : _syncProduits ne throw pas — la synchro passage principale reste synced"
  - "Retry via produits_pending flag sur l'item IDB, relu dans _flushQueue après boucle pending"
  - "Pest.php fix : détection worktree via getcwd() au lieu de dirname(__DIR__) (vendor symlinké résout depuis main)"
metrics:
  duration: "~45 minutes"
  completed: "2026-06-03T02:48:29Z"
  tasks_completed: 3
  files_changed: 9
---

# Phase 7 Plan 03: Pivot passage_produit + sélecteur produits offline Summary

**One-liner:** Pivot `passage_produit` avec `prix_snapshot` HT, endpoint `/api/passages/produits` idempotent, sélecteur produits Alpine dans la saisie offline avec retry persistant via flag `produits_pending`.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Pivot passage_produit + relations BelongsToMany | d621287 | migration, Passage.php, Produit.php |
| 2 | PassageProduitController + route API + 4 tests verts | 0c95f43 | PassageProduitController.php, routes/api.php, PassageProduitSyncTest.php, tests/Pest.php |
| 3 | Sélecteur produits offline Blade+Alpine+sync JS avec retry | d2d9d05 | PassageCreateController.php, create.blade.php, passage-form.js |

## What Was Built

### Schéma du pivot

Table `passage_produit` : `passage_id` (FK cascade) + `produit_id` (FK cascade) + `quantite` (decimal 8,2 nullable) + `prix_snapshot` (decimal 10,2 nullable, prix HT au moment de la synchro) + `unique(passage_id, produit_id)`.

Relations : `Passage::produits()` et `Produit::passages()` BelongsToMany symétriques avec `withPivot(['quantite', 'prix_snapshot'])`.

### Endpoint de sync

`POST /api/passages/produits` (déclaré avant `passages/{uuid}/photos` dans routes/api.php) :
- Reçoit `{ passage_client_uuid, produits: [{produit_id, quantite?}] }`
- Résout le passage par `client_uuid`, construit le map sync avec `prix_snapshot = Produit::find($id)->prix_ht`
- Appelle `->produits()->sync($map)` — idempotent, remplace l'ensemble du pivot
- Retourne `{"ok": true}` 200

### Point d'insertion de `_syncProduits` dans le cycle offline

```
_uploadPassage(item)
  → POST /api/passages → 200
  → markStatus('passages', item.id, 'synced')   ← passage OK
  → _syncProduits(item)                          ← chimie (soft fail)
  → _uploadPhotosForPassage(item.client_uuid)    ← photos
```

### Mécanisme `produits_pending` + retry au flush

**Succès `_syncProduits`** : relire item IDB → poser `produits_pending = false` → `db.put`.

**Échec (réseau ou !res.ok)** : ne throw pas → relire item IDB → poser `produits_pending = true` → `db.put` → `console.warn`. Le passage reste `synced`, la chimie est marquée différée.

**`_flushQueue` retry** : après la boucle `pending`, `db.getAll('passages')` → pour chaque item avec `status === 'synced' && produits_pending === true` → `_syncProduits(item)`. La consommation chimie n'est jamais perdue en silence.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Fix Pest.php worktree symlinked-vendor detection**
- **Found during:** Task 2 — tests ne s'exécutaient pas depuis le répertoire worktree
- **Issue:** `dirname(__DIR__)` dans Pest.php pointe toujours vers le main repo (Pest.php chargé depuis main via vendor symlinké). La détection `str_contains($worktreePath, 'worktrees')` ne s'activait jamais depuis le worktree.
- **Fix:** Nouvelle détection via `getcwd()` (le CWD réel = répertoire worktree) + comparaison `vendorFeature !== worktreeFeature`. Enregistrement absolu `pest()->extend()->in($worktreeFeature)` seulement quand les chemins diffèrent.
- **Files modified:** `tests/Pest.php`
- **Commit:** 0c95f43
- **Note:** Tests vérifiés GREEN via copie temporaire dans main repo (worktree isolation empêche l'édition directe de main/tests/).

## Known Stubs

Aucun stub — les données produits viennent de la table `produits` réelle (pré-chargées server-side).

## Threat Flags

Aucun nouveau endpoint exposé sans auth — `POST /api/passages/produits` est sous le même middleware `['web', 'auth']` que les autres routes API (enregistré dans bootstrap/app.php).

## Self-Check

- [x] `database/migrations/2026_06_03_000003_create_passage_produit_table.php` — FOUND
- [x] `app/Http/Controllers/Api/PassageProduitController.php` — FOUND
- [x] `tests/Feature/Api/PassageProduitSyncTest.php` — FOUND
- [x] `app/Models/Passage.php` contient `belongsToMany` — FOUND
- [x] `app/Models/Produit.php` contient `belongsToMany` — FOUND
- [x] `resources/js/passage-form.js` contient `_syncProduits` + `produits_pending` — FOUND
- [x] `resources/views/admin/passages/create.blade.php` contient `produitsDisponibles` — FOUND
- [x] `npx vite build` — PASSED (701ms)
- [x] Commits d621287, 0c95f43, d2d9d05 — FOUND

## Self-Check: PASSED
