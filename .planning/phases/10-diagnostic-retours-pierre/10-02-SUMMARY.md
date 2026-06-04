---
phase: 10-diagnostic-retours-pierre
plan: "02"
subsystem: diagnostic-wizard
tags: [alpine, livewire, ux-simplification, pierre-feedback]
dependency_graph:
  requires: [10-01]
  provides: [direct-tree-entry, chemistry-link-setmode, carnet-on-disclaimer]
  affects:
    - resources/views/livewire/diagnostic-wizard.blade.php
    - tests/Feature/DiagnosticWizardTest.php
tech_stack:
  added: []
  patterns: [alpine-x-init-migration, discret-link-secondary-cta]
key_files:
  created: []
  modified:
    - resources/views/livewire/diagnostic-wizard.blade.php
    - tests/Feature/DiagnosticWizardTest.php
decisions:
  - "assertSee needle non encodé (false) car le Livewire SSR émet l'apostrophe littérale, pas &#039;"
  - "Lien chimie discret (text-sm underline) pas un bouton carte — cohérent avec la hiérarchie visuelle disclaimer"
  - "x-init='loadCarnetEntries()' migré sur la div racine disclaimer (était sur le bouton S0 supprimé)"
metrics:
  duration: "12m"
  completed_date: "2026-06-04"
  tasks: 3
  files_changed: 2
---

# Phase 10 Plan 02: Suppression S0, entrée directe arbre symptôme Summary

**One-liner:** Suppression de l'écran de fourche mode (S0), entrée directe dans l'arbre symptôme via le disclaimer (S4) avec Analyser mon eau et Carnet en actions secondaires — fidélité au proto Pierre (diag-1/diag-2).

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Supprimer S0, basculer état Alpine initial, corriger cibles step:'mode' | cb9cc73 | diagnostic-wizard.blade.php |
| 2 | Greffer Analyser mon eau + Carnet sur disclaimer, câbler setMode('chemistry') | 4f95ac7 | diagnostic-wizard.blade.php |
| 3 | Réactiver assertion J'accepte, vérifier parcours complet | f5e44d4 | DiagnosticWizardTest.php |

## What Was Built

**Task 1 (D-01/D-02/D-07):**
- Bloc S0 entier supprimé (~70 lignes) — 3 boutons `data-mode-symptom/chemistry/carnet` disparus
- `step: 'mode'` → `step: 'tree'` dans x-data (état initial)
- `resumeFromCarnet()`: `this.step = 'tree'` (D-07)
- 2 boutons Recommencer: `step = 'tree'` (évite page blanche sur écran supprimé)
- 2 boutons Retour Carnet: `step = 'tree'; nodeId = 'start'`
- La branche `if (this.step === 'mode')` dans `advance()` est conservée inerte (plan : ne pas toucher)

**Task 2 (D-03/D-04/D-05/D-06):**
- `x-init="loadCarnetEntries()"` migré sur la div racine du disclaimer (D-06)
- CTA renommé `J'accepte et commence` (D-03/D-08)
- Lien discret `Vous avez vos mesures ? → Analyser mon eau` avec `$wire.call('setMode', 'chemistry')` + `advance({value:'chemistry', next:{kind:'wizard',id:'chemistry'}})` (D-04) — T-10-01 couvert (whitelist faite en Plan 01)
- Bouton `Mes diagnostics passés` conditionnel `x-show="carnetEntries.length > 0"` (D-05)

**Task 3 (D-08):**
- `assertSee("J'accepte", false)` — `false` obligatoire car le HTML Livewire SSR contient l'apostrophe littérale (pas `&#039;`)
- Suite complète : 61 tests passés, 3 skipped (Playwright), 0 failures

## Verification

```
grep "step: 'tree'" resources/views/livewire/diagnostic-wizard.blade.php
# → ligne 24 : step: 'tree',

grep -c "step = 'mode'\|step === 'mode' && !showCarnet\|data-mode-" resources/views/livewire/diagnostic-wizard.blade.php
# → 0

grep "J'accepte et commence\|setMode\|loadCarnetEntries\|carnetEntries.length > 0" resources/views/livewire/diagnostic-wizard.blade.php
# → 4 lignes confirmées

vendor/bin/pest tests/Feature/DiagnosticWizardTest.php tests/Feature/DiagnosticRouteTest.php tests/Feature/DiagnosticPdfTest.php tests/Browser/CarnetLocalTest.php
# → 61 passed, 3 skipped (Playwright), 0 failures
```

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] assertSee escaping apostrophe**
- **Found during:** Task 3
- **Issue:** `assertSee("J'accepte")` (défaut : encode le needle) cherche `J&#039;accepte` mais le HTML Livewire SSR contient `J'accepte` littéral — test échoue
- **Fix:** `assertSee("J'accepte", false)` — second param `false` désactive l'encodage HTML du needle
- **Files modified:** tests/Feature/DiagnosticWizardTest.php
- **Commit:** f5e44d4

## Known Stubs

None.

## Threat Flags

None — T-10-01 couvert par la whitelist `setMode()` (Plan 01) ; T-10-03 invariant `x-text` conservé ; T-10-04 accepté (guard DIAG-03 suffisant).

## Self-Check: PASSED

- [x] `resources/views/livewire/diagnostic-wizard.blade.php` modifié — S0 supprimé, `step: 'tree'`, liens chimie+carnet sur disclaimer
- [x] `tests/Feature/DiagnosticWizardTest.php` modifié — assertSee("J'accepte", false)
- [x] Commit cb9cc73 existe (Task 1)
- [x] Commit 4f95ac7 existe (Task 2)
- [x] Commit f5e44d4 existe (Task 3)
- [x] `grep -c "step = 'mode'" diagnostic-wizard.blade.php` → 0
- [x] `grep -c "data-mode-" diagnostic-wizard.blade.php` → 0
- [x] 61/61 tests verts (3 skipped Playwright attendu)
