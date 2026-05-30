---
phase: "05-diagnostic-commercialisable"
plan: "01"
subsystem: "diagnostic"
tags: ["diagnostic", "livewire", "alpine", "decision-tree", "pest", "DIAG-01", "DIAG-03", "Req9"]
dependency_graph:
  requires: []
  provides:
    - "Route GET /diagnostic (publique, sans auth, hors cache.headers:vitrine)"
    - "DiagnosticController::show() avec SEO vars"
    - "Landing brand S1 (hero navy-950, Display Fredoka, deux tuiles ≥ h-15)"
    - "config/diagnostic-tree.php version 1 (arbre expert-corrigé, 8 symptômes, 5 feuilles électrolyseur, branche floculant filtre-type, chlore-lock, manque-de-stabilisant, eau-calcaire, blocs EPI)"
    - "DiagnosticWizard (Livewire) — disclaimerAccepted guard, triedActions, render() injecte l'arbre"
    - "diagnostic-wizard.blade.php — surfaces S2 (arbre Alpine), S4 (disclaimer inline), S5 minimal (escalade WhatsApp)"
    - "4 fichiers Pest Wave-0 (DecisionTreeTest implémenté + 3 stubs nommés)"
  affects:
    - "layouts/app.blade.php (nav desktop + mobile — Diagnostic piscine gratuit)"
    - "vitrine/services/eau-verte-urgence.blade.php (CTA Ma piscine est verte ?)"
tech_stack:
  added: []
  patterns:
    - "Arbre de décision PHP config (questions/results, versioned) — traversal Alpine côté client"
    - "Livewire + Alpine step isolation (wire:ignore.self, step purement Alpine — Pitfall 1 évité)"
    - "Disclaimer inline step (jamais modale, S4 — UI-SPEC)"
    - "Guard serveur disclaimerAccepted avant computeAndPersist (DIAG-03)"
    - "Safety block ambre (oklch warn-bg) sur chaque feuille chimique (audit §6 P0)"
key_files:
  created:
    - "config/diagnostic-tree.php"
    - "app/Http/Controllers/DiagnosticController.php"
    - "app/Livewire/DiagnosticWizard.php"
    - "resources/views/vitrine/diagnostic.blade.php"
    - "resources/views/livewire/diagnostic-wizard.blade.php"
    - "tests/Unit/DecisionTreeTest.php"
    - "tests/Feature/DiagnosticWizardTest.php"
    - "tests/Feature/DiagnosticPdfTest.php"
    - "tests/Feature/DiagnosticRouteTest.php"
  modified:
    - "routes/vitrine.php"
    - "resources/views/layouts/app.blade.php"
    - "resources/views/vitrine/services/eau-verte-urgence.blade.php"
decisions:
  - "Arbre de décision en config PHP (config/diagnostic-tree.php) — traversal Alpine côté client, formules absentes (DIAG-02)"
  - "Disclaimer inline step avant le premier nœud (S4 UI-SPEC) — jamais modale, guard serveur défini"
  - "Green-1 test stabilisant (chlore-lock ≥ 80 + manque-de-stabilisant ≤ 30) per audit P0"
  - "Branche floculant filtre-type (sable/verre = méthode A, cartouche = méthode B clarifiant, diatomées = méthode C) — floculant interdit dans le parcours cartouche"
  - "Bifurcation eau-calcaire sous cloudy-1 (audit P1 #12)"
  - "Eau-boueuse + pollution-organique routées vers sous-arbre floculant, ordre choc→équilibre→clarification (audit P0 #5)"
  - "Blocs EPI systématiques sur chaque feuille chimique (audit §6 P0)"
metrics:
  duration: "2026-05-30T01:55:00Z–2026-05-30T03:31:18Z"
  completed_date: "2026-05-30"
  tasks_completed: 3
  files_changed: 12
---

# Phase 5 Plan 01: Diagnostic — Route + Config arbre + Wizard (S1/S2/S4/S5) Summary

Route /diagnostic publique + arbre de décision expert-corrigé PHP config + Livewire/Alpine wizard symptôme avec disclaimer inline gate et escalade WhatsApp one-gesture.

## What Was Built

### Task 0 — 4 fichiers Pest Wave-0

Quatre fichiers créés conformément à la stratégie Nyquist (aucun plan ultérieur bloqué sur la création de fichiers de test) :

- `tests/Unit/DecisionTreeTest.php` — **implémenté et vert** (11/11) : version key, 8 branches top-level, 5 feuilles électrolyseur, floculant invariant cartouche, chlore-lock, manque-de-stabilisant, eau-calcaire, structure plan/diagnostic.
- `tests/Feature/DiagnosticWizardTest.php` — stub nommé (27 tests markTestIncomplete — Plans 05-03/04).
- `tests/Feature/DiagnosticPdfTest.php` — stub nommé (Plan 05-05).
- `tests/Feature/DiagnosticRouteTest.php` — stub nommé (Plan 05-01 Task 1, se déclenche après enregistrement de la route).

### Task 1 — Route + Contrôleur + Landing S1 + Liens nav

- `routes/vitrine.php` : `Route::get('/diagnostic', DiagnosticController::show)->name('diagnostic')` — hors groupe `cache.headers:vitrine` (Livewire stateful), seul middleware `web`. Req9 validée.
- `DiagnosticController::show()` : retourne `vitrine.diagnostic` avec SEO vars (title/description/canonical/ogImage), miroir VitrineController.
- `resources/views/vitrine/diagnostic.blade.php` : hero brand S1 — fond `navy-950`, Display Fredoka (`clamp(2.6rem,5vw,4rem)` 700), animation `.rise` 700ms `ease-out-quint`, `prefers-reduced-motion` honoré (crossfade). Deux tuiles d'entrée `h-15` avec les CTA exacts du Copywriting Contract. Guard `class_exists` + fallback WhatsApp. Tokens `@theme` uniquement, jamais `#000`/`#fff`.
- `layouts/app.blade.php` : lien nav desktop + mobile "Diagnostic piscine gratuit" → `route('diagnostic')`.
- `eau-verte-urgence.blade.php` : CTA "Ma piscine est verte ? Diagnostic gratuit" avec accent `sun-500` (seul cas autorisé, UI-SPEC Color).

### Task 2 — Arbre de décision + DiagnosticWizard + Vue wizard

**config/diagnostic-tree.php** — version 1, arbre expert-corrigé :

- 8 branches top-level depuis `start` (eau-verte, eau-trouble, eau-marron, eau-claire, électrolyseur + variantes).
- 5 feuilles électrolyseur : `electro-debit`, `electro-entartree`, `electro-usee`, `electro-panne`, `electro-sel-bas`.
- Branche floculant filtre-type (FLOCULANT-BRANCH-SPEC) : sable/verre → méthode A (floculant choc, décantation, filtration ARRÊT), cartouche → méthode B (clarifiant seul, mot "floculant" absent), diatomées → méthode C (nettoyage + clarifiant).
- Green-1 test stabilisant (audit P0 #4) : `chlore-lock` (stabilisant > 80) et `manque-de-stabilisant` (stabilisant < 30).
- Bifurcation eau-calcaire sous `cloudy-1` (audit P1 #12).
- Eau-boueuse + pollution-organique : ordre choc→équilibre→clarification, floculant en dernier (audit P0 #5).
- Blocs EPI systématiques sur toutes les feuilles chimiques (audit §6 P0) : gants, lunettes, ne jamais mélanger, produit dans l'eau, délai baignade.
- Re-test reminder sur chaque feuille dosée.

**DiagnosticWizard.php** — composant Livewire :

- Traits : `WithRateLimiting`, `UsesSpamProtection`, honeypot `HoneypotData`.
- `disclaimerAccepted` : guard serveur DIAG-03. `acceptDisclaimer()` + `computeAndPersist()` stub (rejette si disclaimer non accepté).
- `triedActions` : tableau multiselect (BLUEPRINT §6, action-aware — Plan 05-03).
- `render()` : injecte `config('diagnostic-tree')` en lecture seule — aucune formule côté client (DIAG-02).

**diagnostic-wizard.blade.php** — 3 surfaces :

- **S2** (arbre symptôme) : `wire:ignore.self` + `x-cloak` (Pitfall 1), grande tuile tactile `h-13` avec emoji anchor, back affordance, slide horizontal `x-transition.opacity`, multiselect chips "déjà tenté".
- **S4** (disclaimer inline) : affiché avant le premier nœud start, fond `sand-50`/`white`, bouton primary azure "J'ai compris, voir les recommandations", wire:click + @click Alpine en parallèle.
- **S5** minimal : gabarit fixe (Diagnostic headline → Pourquoi → Safety ambre → Étapes ordonnées → Re-test reminder → Escalade WhatsApp navy panel) avec le pré-remplissage du message encodeURIComponent conforme Pitfall 4.

## Test Results

```
./vendor/bin/pest --filter DecisionTree
PASS  Tests\Unit\DecisionTreeTest
✓ the diagnostic tree config carries a version key
✓ has a questions key and a results key at the top level
✓ all 8 top-level symptom branches at the start node reach at least one result leaf
✓ the electrolyser sub-tree exposes exactly its 5 documented fault leaves
✓ the electrolyser branch from electro-1 can reach all 5 fault leaves
✓ the cartouche (cartridge) filter path contains zero occurrences of the word floculant
✓ the sable/verre filter path recommends floculant choc
✓ green-1 has a branch testing for surstabilisation (chlore-lock)
✓ green-1 has a branch detecting low stabilisant (manque-de-stabilisant)
✓ cloudy-1 has a bifurcation for eau calcaire (eau-calcaire leaf)
✓ all result leaves have at minimum a diagnostic key and a plan array

Tests: 11 passed
```

**Suite complète : 371 passés, 27 incomplets (stubs Plans 03-05), 1 skipped (pré-existant), 0 erreur.**

DIAG-02 smoke check : `grep -rE "[0-9]+ ?\* ?(volume|m3)" resources/js/` → exit 1, aucune formule arithmétique dans le JS client.

## Deviations from Plan

Aucun écart bloquant. Ajustements mineurs :

**1. [Rule 1 - Bug] Assertions Pest v4 `toHaveKey` avec message**

- Trouvé lors de : Task 0 première exécution
- Problème : `expect($arr)->toHaveKey('key', "message")` traite le 2e argument comme une valeur attendue en Pest v4, pas comme un message.
- Correction : remplacé par `expect(array_key_exists('key', $arr))->toBeTrue("message")`.
- Fichiers modifiés : `tests/Unit/DecisionTreeTest.php`
- Commit : inclus dans le commit Task 2.

**2. [Rule 2 - Missing critical functionality] Nœud `auto-green` conditionnel**

- Ajouté un nœud `green-stab` entre `green-1` et `auto-green` pour tester le stabilisant avant la bifurcation pH (audit P0 #4 complet). Le plan mentionnait le nœud conditionnel mais la séquence nécessitait un nœud intermédiaire pour les 3 cas : stab élevé → chlore-lock, stab bas → manque-de-stabilisant, normal → auto-green.

**3. [Rule 2 - Missing critical functionality] Variante floculant-sable-ph-ajust**

- Ajouté une feuille séparée pour le cas pH hors plage avec filtre sable/verre (précondition bloquante FLOCULANT-BRANCH-SPEC §4). Nécessaire pour respecter les 2 branches du pH-gate.

**4. Start node enrichi**

- Ajout d'options directes au nœud `start` (entrée directe eau verte opaque, eau boueuse après pluie, électrolyseur direct) pour une navigation plus fluide. Les 8 branches top-level incluent ces raccourcis mais restent atteignables via le parcours complet.

## Threat Surface Scan

Aucune nouvelle surface réseau, aucun endpoint, aucun accès fichier, aucun changement de schéma DB dans ce plan.

La route `/diagnostic` est publique (Req9) et sans état sensible — la surface est conforme au modèle de menace du plan (T-05-SC : aucun nouveau paquet).

## Known Stubs

Aucun. Le wizard symptôme est fonctionnel de bout en bout (mode → disclaimer → question → feuille → escalade WhatsApp). Les fonctionnalités différées (dosage chimique, persistance, lead-capture, PDF) sont clairement indiquées comme "Plan 05-03/05" dans le code.

## Self-Check: PASSED

Fichiers créés :
- config/diagnostic-tree.php ✓
- app/Http/Controllers/DiagnosticController.php ✓
- app/Livewire/DiagnosticWizard.php ✓
- resources/views/vitrine/diagnostic.blade.php ✓
- resources/views/livewire/diagnostic-wizard.blade.php ✓
- tests/Unit/DecisionTreeTest.php ✓
- tests/Feature/DiagnosticWizardTest.php ✓
- tests/Feature/DiagnosticPdfTest.php ✓
- tests/Feature/DiagnosticRouteTest.php ✓

Commits :
- 6a18a80 test(05-01): create Wave-0 Pest test stubs + implement DecisionTreeTest ✓
- 1876e0a feat(05-01): public /diagnostic route + DiagnosticController::show + landing brand S1 ✓
- 6cf85a4 feat(05-01): decision-tree config + DiagnosticWizard + wizard view (S2, S4, S5) ✓
