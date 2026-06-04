---
phase: 09-espace-client-finitions-retours-pierre
plan: "01"
subsystem: portail-client
tags: [a11y, perf, copy, tests, portail]
dependency_graph:
  requires: [portail-client-phase-2]
  provides: [passage-timeline-a11y, portail-timeline-regression-tests]
  affects: [portail-client]
tech_stack:
  added: []
  patterns: [pest-actingAs-clients-guard, RefreshDatabase, seedTimelineFixture]
key_files:
  created:
    - tests/Feature/PortailTimelineTest.php
  modified:
    - resources/views/livewire/portail/passage-timeline.blade.php
    - .planning/ROADMAP.md
decisions:
  - "T5 compteur photos : asserter l'absence du bloc (SQLite/pas de R2 en test) plutôt que sa présence"
  - "TDD order : fixes Blade appliqués en Task 1 avant les tests Task 2 — tests verts immédiatement (régression forward)"
metrics:
  duration_minutes: 2
  completed_date: "2026-06-04"
  tasks_completed: 2
  files_changed: 3
---

# Phase 09 Plan 01: Finitions portail — copy, a11y accordéon, lazy hero + test régression Summary

**One-liner:** Portail client finalisé : teaser facturation cohérent, aria-controls/id reliant bouton↔panneau accordéon, hero LCP corrigé, et 5 tests Pest de régression sur la timeline historique.

## Tasks Completed

| # | Task | Commit | Files |
|---|------|--------|-------|
| 1 | Corriger copy « Mes documents », a11y accordéon, lazy hero + note ROADMAP | 8387a8c | passage-timeline.blade.php, ROADMAP.md |
| 2 | Test de régression Pest sur la timeline historique | 1c97431 | tests/Feature/PortailTimelineTest.php |

## What Was Built

**Task 1 — 4 fixes en un commit :**

- **(client-3 copy)** Les deux sous-titres de la section « Mes documents » remplacés par « Disponible avec la mise en place de la facturation. » — les badges « Bientôt » et les titres « Contrat d'entretien » / « Factures » sont inchangés.
- **(client-2 a11y)** `aria-controls="passage-panel-{{ $p->id }}"` ajouté sur le `<button>` de chaque accordéon historique ; `id="passage-panel-{{ $p->id }}"` ajouté sur le `<div x-show="open" x-cloak>` correspondant — l'id est unique par passage via `$p->id`.
- **(client-4 perf)** `loading="lazy"` retiré du `<img>` hero bandeau (LCP au-dessus de la ligne de flottaison) ; conservé sur la photo du passage (sous la ligne de flottaison).
- **(D-02 note ROADMAP)** Note de dépendance Phase 3 ajoutée dans la section `### Phase 9:` de ROADMAP.md — REQUIREMENTS.md non touché.

**Task 2 — 5 tests Pest :**

- **T1** — Structure a11y : `aria-expanded`, `aria-controls="passage-panel-{id}"`, `id="passage-panel-{id}"` tous présents.
- **T2** — Mesures dépliées : pH `7,4` / Cl `2,3` / TAC `95` avec virgule décimale exacte (format Blade `number_format`).
- **T3** — Actions réalisées : `Nettoyage filtre` et `Brossage parois` visibles dans l'accordéon.
- **T4** — Notes : `Note historique XQ7Z` visible.
- **T5** — Compteur photos : bloc caméra absent quand aucune photo seedée (cas SQLite sans R2).

Seed pattern : 2 passages obligatoires (passage A récent → `Dernier passage`, passage B historique → accordéon via `skip(1)`).

## Verification

- `vendor/bin/pest tests/Feature/PortailTimelineTest.php` : 5/5 passed
- `vendor/bin/pest tests/Feature/PortailAccessTest.php` : 7/7 passed (non-régression)
- `grep -c 'Disponible avec la mise en place de la facturation.'` = 2
- `grep -c 'Bientôt'` = 2
- `grep -c 'loading="lazy"'` = 1 (photo passage uniquement)
- `git diff … REQUIREMENTS.md` = aucune modification

## Deviations from Plan

None — plan executed exactly as written.

## Known Stubs

- **« Mes documents » (Contrat + Factures)** : les deux lignes restent des teasers `Bientôt`. Le branchement réel (récupération du contrat PDF, téléchargement des factures) est tracé en Phase 3 : Facturation & Odoo. Intentionnel — tracé dans ROADMAP.md.

## Threat Flags

None — aucune nouvelle surface réseau, auth ou schéma introduite par ce plan.

## Self-Check: PASSED

- [x] `resources/views/livewire/portail/passage-timeline.blade.php` — modifié, présent
- [x] `tests/Feature/PortailTimelineTest.php` — créé, présent
- [x] `.planning/ROADMAP.md` — modifié, présent
- [x] Commit `8387a8c` — présent (Task 1)
- [x] Commit `1c97431` — présent (Task 2)
