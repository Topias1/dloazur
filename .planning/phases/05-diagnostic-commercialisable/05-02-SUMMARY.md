---
phase: "05-diagnostic-commercialisable"
plan: "02"
subsystem: "diagnostic"
tags: ["dose-engine", "chemistry", "tdd", "expert-audit", "diag-02"]
dependency_graph:
  requires: []
  provides: ["DoseEngine::compute()", "DoseEngine::chloreChoc()", "DoseEngine::phMinus()", "config/diagnostic-formulas.php"]
  affects: ["Plan 03 (DiagnosticWizard appelle DoseEngine::compute())"]
tech_stack:
  added: []
  patterns:
    - "Service pur (fonctions statiques, zéro I/O) dans App\\Services\\Diagnostic"
    - "Config versionnée config/diagnostic-formulas.php — surface de revue unique pour Pierre"
    - "TDD Red/Green : test d'abord, implémentation ensuite, tolérance float precision avec round() avant ceil()"
key_files:
  created:
    - app/Services/Diagnostic/DoseEngine.php
    - config/diagnostic-formulas.php
    - tests/Unit/DoseEngineTest.php
  modified: []
decisions:
  - "Formule pH+ : steps = ceil(round((7.2 - pH) / 0.1, 9)), coefficient 3 g/m³/step — produit 300 g pour pH=7.0 vol=50 m³ (conforme 05-VALIDATION)"
  - "Arrondi prudent : roundCautiousDown (floor multiple de 5) pour acide et chlore ; roundCautious (round multiple de 5) pour le reste"
  - "Gate chloration : pH < 7.0 OU TAC < 60 → card Chlore omise (blocage explicite audit P1 section 7)"
  - "Float precision : round(..., 9) avant ceil() pour éviter (7.2-7.0)/0.1 = 2.000...4 → ceil = 3"
metrics:
  duration: "~35 min"
  completed: "2026-05-30T03:44:53Z"
  tasks_completed: 2
  files_created: 3
---

# Phase 5 Plan 2 : DoseEngine — moteur de dosage expert côté serveur

Moteur de dosage chimique piscine en PHP pur, expert-audité (P0/P1 corrections appliquées). Aucun coefficient ne quitte le serveur.

---

## Ce qui a été livré

### Task 1 — RED : tests DoseEngineTest (commit c67dc12)

29 tests Pest couvrant tous les cas du `<behavior>` block + sections 2, 3, 7, 8, 9 de l'audit expert. Tous RED au départ (DoseEngine inexistant).

- P0 rattrapage chlore bas ≈ 3-4 g/m³ (distinct du choc 15/30)
- Gate chloration (pH ou TAC hors plage → card chlore absente)
- pH- une seule formule plafonnée + re-test obligatoire
- Odeur forte break-point (avec/sans chlore_total)
- TH > 300 → switch calcium/sodium
- Ordre TAC → pH → chlore → stabilisant → sel
- Stabilisant bas < 30 → apport proposé (cause racine tropicale)
- Bloc sécurité sur chaque card
- Fausse précision interdite (multiples de 5)
- Parsing décimal français (virgule)

### Task 2 — GREEN : DoseEngine + config (commit 6da416c)

**`config/diagnostic-formulas.php`**
- `version: 1` — surface de revue unique pour Pierre (pré-lancement DIAG-02)
- Tous les coefficients et seuils nommés (pas de magic numbers dans le code)
- Bloc sécurité systématique en config (EPI / ne jamais mélanger / délai baignade)

**`app/Services/Diagnostic/DoseEngine.php`**
- `compute(array $mesures, float $volume): array` — retourne cards ordonnées
- `chloreChoc(float $volumeM3, string $type, ?float $th): array` — 15/30 g/m³, switch sodium si TH > 300
- `phMinus(float $volumeM3, ?float $currentPh): array` — formule plafonnée TAC-governed
- Corrections float precision (round avant ceil)
- Zéro import Illuminate/Livewire/Eloquent

---

## Résultat des tests

```
./vendor/bin/pest --filter DoseEngine
tests: 29 passed (77 assertions)
```

Suite complète : 400 passed, 1 skipped, 27 incomplete — aucune régression.

---

## DIAG-02 Invariants vérifiés

| Check | Résultat |
|-------|----------|
| `grep -rEn "hypochlorite\|carbonate de soude" resources/js/` | vide — aucun coefficient dans le JS client |
| `grep -nE "^use (Illuminate\|Livewire\|Eloquent)" app/Services/Diagnostic/DoseEngine.php` | vide — service pur |
| `config/diagnostic-formulas.php` a un `'version'` key | ✓ version = 1 |
| `DoseEngine.php` > 120 lignes | ✓ 439 lignes |

---

## Déviations par rapport au plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Float precision : ceil((7.2 - 7.0) / 0.1) = 3 au lieu de 2**
- **Trouvé pendant :** Task 2 (premier run GREEN)
- **Problème :** PHP float arithmetic — `(7.2 - 7.0) / 0.1 = 2.0000000000000004` ; `ceil(2.0000000000000004) = 3` → dose 450 g au lieu de 300 g
- **Correction :** `round(($phLow - $ph) / $stepSize, 9)` avant `ceil()` (même fix appliqué à la formule TAC+)
- **Fichier :** `app/Services/Diagnostic/DoseEngine.php`
- **Commit :** 6da416c

**2. [Rule 1 - Bug] Pest `toContain()` variadic — messages de failure passés comme needles supplémentaires**
- **Trouvé pendant :** Task 2 (analyse des échecs)
- **Problème :** `toContain('750', "description text")` vérifie que la string contient BOTH '750' ET "description text" (variadic) — pas un failure message
- **Correction :** Retrait des descriptions inline ; placées en commentaires au-dessus des assertions
- **Fichier :** `tests/Unit/DoseEngineTest.php`
- **Commit :** 6da416c

### Ajustements de formule (non prévus dans PLAN mais conformes à l'audit)

Le coefficient pH+ (RESEARCH transcription) donnait `steps * 10 g/m³` (= 500 g pour pH=7.0/50m³).
La valeur autoritaire de 05-VALIDATION.md est 300 g → formule corrigée : `steps * 3 g/m³` avec `step_size = 0.1 pH`.
Le coefficient `3 g/m³ par 0.1 pH` est conforme à `ceil((7.2 - 7.0) / 0.1) = 2 steps × 3 × 50 = 300 g`.
Enregistré en config comme `ph_plus_step_size = 0.1` et `ph_plus_per_step = 3.0`.

---

## Known Stubs

Aucun. DoseEngine est un service pur — pas d'UI, pas de données mockées. Les formules sont les valeurs réelles de l'audit expert.

---

## Threat Flags

Aucun nouveau vecteur d'attaque introduit. Les T-05-04 / T-05-05 / T-05-06 de la threat model sont mitigés :
- T-05-04 (leak formulas) : smoke grep confirme 0 coefficient dans resources/js/
- T-05-05 (malformed input) : str_replace + (float) cast + rounding prudent
- T-05-06 (safety framing) : safety_ref systématique sur chaque card

---

## Self-Check: PASSED

- [x] `app/Services/Diagnostic/DoseEngine.php` : créé (439 lignes)
- [x] `config/diagnostic-formulas.php` : créé (version = 1)
- [x] `tests/Unit/DoseEngineTest.php` : créé (547+ lignes)
- [x] Commit RED : c67dc12 — `test(05-02): add failing DoseEngine specs`
- [x] Commit GREEN : 6da416c — `feat(05-02): implement server-side DoseEngine with expert-corrected chemistry`
- [x] DoseEngine tests : 29/29 passed
- [x] Full suite : 400/401 passed (1 skipped baseline)
- [x] DIAG-02 invariant : aucun coefficient dans resources/js/
