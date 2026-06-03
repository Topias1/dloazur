---
phase: 07-espace-admin-retours-pierre
plan: "04"
subsystem: admin
tags: [recap, chimie, facturation-teaser, blade, eloquent]
dependency_graph:
  requires: ["07-03"]
  provides: ["admin.recap.index route", "RecapMensuelController", "admin/recap/index.blade.php"]
  affects: ["admin navigation", "Passage model (produits relation)"]
tech_stack:
  added: []
  patterns: ["Client::withCount + having + with eager-load", "pivot aggregation in Blade @php block"]
key_files:
  created:
    - app/Http/Controllers/Admin/RecapMensuelController.php
    - resources/views/admin/recap/index.blade.php
  modified:
    - routes/admin.php
    - app/Models/Passage.php
decisions:
  - "Agrégation chimie en Blade @php (pas en controller) pour garder le controller léger — recollection simple sur les produits déjà eager-loadés"
  - "Sélecteur mois/année soumis au onChange + bouton Voir (double déclencheur UX)"
  - "Bouton facture = <span aria-disabled> cursor-not-allowed, aucun href — teaser sans aucune action"
  - "Quantité : sommée quand renseignée, affichée — quand null (tous les passages)"
  - "Scope fence tenue : zéro TVA, zéro TTC, zéro PDF/Odoo"
metrics:
  duration: "~20min"
  completed: "2026-06-03"
  tasks_completed: 2
  files_count: 4
---

# Phase 07 Plan 04: Récap mensuel par client Summary

Page « Récap mensuel par client » — cahier de fin de mois numérisé : nb de passages + chimie consommée HT brut par client, navigable par mois/année, avec bouton « Générer la facture » inerte (teaser Phase 3).

## What Was Built

**RecapMensuelController** (`app/Http/Controllers/Admin/RecapMensuelController.php`)
- `index(Request): View` — filtre par `whereBetween('visited_at', [$debut, $fin])`
- `withCount(['passages as nb_passages' => ...])` + `having('nb_passages', '>', 0)` — ne montre que les clients actifs sur le mois
- Eager-load `passages.produits` avec pivot `quantite` + `prix_snapshot`
- Passe `$clients`, `$mois`, `$annee`, `$debut` à la vue

**Route** (`routes/admin.php`)
- `GET admin/recap` → `RecapMensuelController@index` → nommée `admin.recap.index`

**Vue** (`resources/views/admin/recap/index.blade.php`)
- Sélecteur mois/année GET vers `admin.recap.index` (onChange + bouton Voir)
- Affichage mois courant en français via `$debut->locale('fr')->isoFormat('MMMM YYYY')`
- Par client : card `rounded-2xl ring-1 ring-navy-900/8 shadow-xs` avec nom, nb passages, chimie agrégée
- Agrégation chimie en `@php` : somme des quantités par produit_id, affichage libelle + quantité + prix HT brut
- Bouton « Générer la facture » : `<span cursor-not-allowed aria-disabled>` sans href — inerte
- État vide global : « Aucun passage sur cette période »
- Zero TVA, zero TTC (franchise 293 B tenue)

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Relation `produits()` manquante sur `Passage`**
- **Found during:** Task 1 — le controller fait `->with('produits')` mais la relation n'existait pas dans `Passage.php`
- **Issue:** Plan 07-03 aurait dû l'ajouter mais ce worktree ne l'avait pas (wave parallèle)
- **Fix:** Ajout de `produits(): BelongsToMany` avec `withPivot(['quantite', 'prix_snapshot'])` sur `Passage`
- **Files modified:** `app/Models/Passage.php`
- **Commit:** d07995e

## Self-Check

- [x] `app/Http/Controllers/Admin/RecapMensuelController.php` — créé, syntaxe OK
- [x] `resources/views/admin/recap/index.blade.php` — créé, « Générer la facture » présent, `cursor-not-allowed`, aucune TVA
- [x] `routes/admin.php` — import + route `recap.index` ajoutés
- [x] `app/Models/Passage.php` — relation `produits()` ajoutée
- [x] Commits : d07995e (Task 1) + f7bf654 (Task 2)

## Self-Check: PASSED
