---
phase: "05-diagnostic-commercialisable"
plan: "04"
subsystem: "diagnostic"
tags: ["livewire", "wizard", "escalade", "confiance", "whatsapp", "DIAG-06", "DIAG-01", "DIAG-02"]
dependency_graph:
  requires: ["05-03"]
  provides:
    - "computeEscalade() : preemptif/reactif/aucun — flags arbre + hook Plan 06 retestFailed"
    - "computeConfidence() : eleve/moyen/indicatif (CDC §5.5)"
    - "setSymptomResult() : synchronise la feuille atteinte côté serveur (Alpine→Livewire)"
    - "triggerRetestFailed() : hook réactif Plan 06"
    - "richContextPayload() : symptôme, mesures+fiabilité, filtre+volume, actions tentées, diagnostic, confiance, coordonnées"
    - "whatsappSummary() enrichi — remplace basique Plan 03"
    - "S6 escalade peak : carte marine + CTA WhatsApp vert + hard-stop callout acide/230V + shadow bloom"
    - "S5 chip de confiance : success/élevé, warn/moyen, warn/indicatif — CDC §5.5"
    - "Lien PDF gardé Route::has('diagnostic.pdf')"
    - "DiagnosticWizardTest : 27 tests verts (11 nouveaux Plan 05-04)"
  affects:
    - "app/Livewire/DiagnosticWizard.php (escalade + confiance + richContextPayload)"
    - "config/diagnostic-tree.php (escalade flags sur electro-entartree/panne/usee)"
    - "resources/views/livewire/diagnostic-wizard.blade.php (S6 brand peak + S5 chip + PDF link)"
    - "tests/Feature/DiagnosticWizardTest.php (11 tests ajoutés)"
tech_stack:
  added: []
  patterns:
    - "server-side escalation classification (preemptif/reactif/aucun) — flag en config PHP, classifié Livewire"
    - "Alpine → Livewire sync pour feuille arbre : $wire.setSymptomResult(next.id)"
    - "richContextPayload() tableau → implode('\n') = whatsappSummary() (chaîne encodée Blade urlencode + Alpine encodeURIComponent)"
    - "Route::has('diagnostic.pdf') guard — safe si Plan 05-05 non encore enregistré"
    - "CSS @keyframes shadow-bloom one-time (900ms, non-looping, prefers-reduced-motion honored)"
key_files:
  created: []
  modified:
    - "app/Livewire/DiagnosticWizard.php"
    - "config/diagnostic-tree.php"
    - "resources/views/livewire/diagnostic-wizard.blade.php"
    - "tests/Feature/DiagnosticWizardTest.php"
decisions:
  - "whatsappSummary() Plan 03 basique remplacé par richContextPayload() : même signature, contenu enrichi — rétrocompatible"
  - "Alpine appelle $wire.setSymptomResult() à chaque advance() vers une feuille pour synchroniser l'escalade préemptive côté serveur"
  - "Blade urlencode() sert de fallback JS-off ; Alpine encodeURIComponent() surcharge avec x-on:click.prevent pour garantir l'encodage des accents"
  - "shadow-bloom one-time sur la carte escalade (.escalade-card) via @keyframes — non-looping, respecte prefers-reduced-motion (UI-SPEC S6)"
  - "hard-stop danger callout = full ring (ring-2) + tint bg, JAMAIS une side-stripe border (UI-SPEC S6 / AI-slop check)"
metrics:
  duration: "~60 min"
  completed_date: "2026-05-30"
  tasks_completed: 2
  files_changed: 4
---

# Phase 5 Plan 04 : Moteur d'escalade + indice de confiance + S6 peak WhatsApp riche

Briques 4 + 5 du diagnostic commercialisable : classificateur d'escalade serveur-side (préemptif sur feuilles hors-DIY, réactif via hook Plan 06), guard anti-sur-escalade, indice de confiance CDC §5.5, WhatsApp DIAG-06 enrichi (contexte riche complet), pic d'escalade S6 register brand, chip de confiance S5, lien PDF gardé.

## Ce qui a été livré

### Task 1 — Moteur d'escalade + confiance + contexte riche (commit d23d566)

**config/diagnostic-tree.php — drapeaux escalade :**
- `electro-entartree` : `escalade.niveau = 'preemptif'`, `raison = 'acide-chlorhydrique'`
- `electro-panne` : `escalade.niveau = 'preemptif'`, `raison = '230V'`
- `electro-usee` : `escalade.niveau = 'preemptif'`, `raison = 'electro-usee'`
- Feuilles DIY facile (algues-parois, algues-installees) : aucun flag → guard anti-sur-escalade automatique

**DiagnosticWizard.php — nouvelles propriétés et méthodes :**
- Props : `symptomResultId`, `escaladeNiveau`, `escaladeRaison`, `retestFailed`, `confidenceIndex`, `coordonnees`
- `computeEscalade()` : préemptif (flag arbre) → réactif (retestFailed) → aucun (guard CDC guard-rail 2)
- `computeConfidence()` : élevé (pH+Cl+TAC) / moyen (pH ou Cl seul) / indicatif (aucune mesure) — CDC §5.5
- `setSymptomResult(string $resultId)` : synchronise la feuille Alpine→serveur + déclenche computeEscalade()
- `triggerRetestFailed()` : hook réactif Plan 06 (retestFailed = true → escalade réactive)
- `richContextPayload()` : tableau structuré (symptôme, mesures+fiabilité, filtre+volume, actions tentées, plan, confiance, coordonnées/commune)
- `whatsappSummary()` : remplace basique Plan 03 → `implode("\n", richContextPayload())`
- `computeAndPersist()` : appelle computeConfidence() + computeEscalade() après persist
- `render()` : expose `escaladeNiveau`, `escaladeRaison`, `confidenceIndex` à la vue

**DIAG-02 invariant préservé :** classification sémantique pure, aucun coefficient de dosage.

### Task 2 — S6 escalade peak + S5 chip de confiance + PDF link + tests (commit ae8a8c6)

**diagnostic-wizard.blade.php :**

- Alpine `advance()` : appel `$wire.setSymptomResult(next.id)` à chaque feuille atteinte
- **S5 chip de confiance** (résultats chimie) :
  - success chip `élevé` (oklch(0.700 0.150 155)) + « basé sur tes mesures. »
  - warn chip `moyen` (oklch(0.800 0.130 80)) + « affine en mesurant le stabilisant / le TAC. »
  - warn chip `indicatif` + « diagnostic visuel sans mesure — pour confirmer, mesure ton eau ou demande à Pierre. »
- **PDF link gardé** : `@if(Route::has('diagnostic.pdf'))` → `route('diagnostic.pdf', $savedDiagnosticId)` + texte « Télécharger le rapport (PDF) »
- **S6 carte escalade chimie** (register: brand, oklch(0.232 0.052 251)) :
  - hard-stop callout full-ring danger (oklch(0.620 0.210 25)) pour acide/230V (jamais side-stripe)
  - titre adaptatif (preemptif vs secondaire)
  - contexte récap « Pierre recevra : symptôme · mesures · filtre + volume · actions tentées · diagnostic · confiance »
  - CTA « Demander une intervention à Pierre » (#25D366) : Blade urlencode() + Alpine x-on:click.prevent encodeURIComponent
  - shadow-bloom @keyframes 900ms one-time, prefers-reduced-motion → none
- **S6 arbre symptôme** : hard-stop `<template x-if>` pour acide/230V ; WhatsApp riche Alpine encodeURIComponent (diagnostic + analyse + triedActions)

**DiagnosticWizardTest.php — 11 nouveaux tests :**
- Escalade préemptive : electro-panne (230V), electro-entartree (acide-chlorhydrique)
- Guard anti-sur-escalade : algues-parois, algues-installees → `aucun`
- Hook réactif : triggerRetestFailed() → `reactif` + `echec-retest`
- Confidence : indicatif (aucune mesure), élevé (pH+Cl+TAC), moyen (pH seul)
- WhatsApp riche : 596696940054 + Mesures + « Déjà tenté » + Confiance + Dlo Azur
- richContextPayload : pH + triedActions + « Indice de confiance »
- Accents intacts dans la chaîne serveur

## Résultats des tests

```
./vendor/bin/pest --filter DiagnosticWizard
tests: 27 passed (63 assertions)

./vendor/bin/pest
tests: 418 passed, 1 skipped
```

(1 skipped = test incomplet pré-existant ; 0 failure)

## Déviations par rapport au plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Méthode whatsappSummary() dupliquée (redéclaration fatale)**
- **Trouvé pendant :** Task 1 post-édition
- **Problème :** L'insertion de la nouvelle méthode `whatsappSummary()` dans la section `Escalade + Confiance` a laissé l'ancienne méthode `whatsappSummary()` Plan 03 en place → `Cannot redeclare` PHP fatal.
- **Correction :** Suppression de l'ancienne méthode (lignes 487-546 originales) ; la nouvelle est complète et rétrocompatible (même signature, contenu enrichi via `richContextPayload()`).
- **Fichier :** `app/Livewire/DiagnosticWizard.php`
- **Commit :** d23d566

## Known Stubs

Aucun. Les données sont réelles :
- Escalade basée sur les flags config réels
- Confiance calculée depuis les champs saisis
- WhatsApp pre-filled depuis le payload serveur réel
- Le lien PDF est conditionnel (Route::has guard) mais fonctionnel quand Plan 05-05 est présent (l'est sur cette branche)

## Threat Surface Scan

| Threat | Mitigation appliquée |
|--------|----------------------|
| T-05-12 (XSS/broken link via user input dans WhatsApp) | Blade urlencode() + Alpine encodeURIComponent() ; jamais `{!! !!}` dans la vue |
| T-05-13 (hiding pro-redirect acide/230V / sur-escalade DIY) | Hard-stop callout full-ring mandatory sur acide/230V (asserté) ; guard anti-sur-escalade (algues-parois → aucun, asserté) |
| T-05-14 (dose formulas in client bundle) | DIAG-02 invariant : `grep -rE "[0-9]+ \* (volume|m3)" resources/js/` → exit 1 ✓ |

## Self-Check: PASSED

Fichiers modifiés :
- app/Livewire/DiagnosticWizard.php ✓
- config/diagnostic-tree.php ✓
- resources/views/livewire/diagnostic-wizard.blade.php ✓
- tests/Feature/DiagnosticWizardTest.php ✓

Commits :
- d23d566 feat(05-04): moteur d'escalade + indice de confiance + contexte riche WhatsApp (serveur) ✓
- ae8a8c6 feat(05-04): pic d'escalade S6 (register brand) + chip de confiance S5 + lien PDF gardé + DiagnosticWizardTest étendu ✓

Grep assertions :
- `wa.me/596696940054` dans diagnostic-wizard.blade.php : ✓ (lignes 1111, 1112, 1153, 1272, 1423)
- `diagnostic.pdf` dans diagnostic-wizard.blade.php : ✓ (lignes 1046, 1049, 1579, 1582)
- DIAG-02 : `grep -rE "[0-9]+ \* (volume|m3)" resources/js/` → exit 1 ✓

Tests :
- `./vendor/bin/pest --filter DiagnosticWizard` → 27/27 passed ✓
- `./vendor/bin/pest` → 418/419 passed, 1 skipped ✓
