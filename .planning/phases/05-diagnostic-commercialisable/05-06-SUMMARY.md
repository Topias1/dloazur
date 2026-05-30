---
phase: "05-diagnostic-commercialisable"
plan: "06"
subsystem: "diagnostic"
tags: ["carnet", "local-only", "indexeddb", "localstorage", "re-test", "escalade-reactive", "diag-07", "diag-06", "s9", "alpine", "zero-infra"]
dependency_graph:
  requires: ["05-04", "05-05"]
  provides:
    - "resources/js/diagnostic-carnet.js — store on-device (localStorage) save/all/get/clear/hasEntries/latestEntry/markRetested, 0 réseau (DIAG-07)"
    - "Boucle re-test légère en session « As-tu re-testé ? Ça a marché ? » (Oui/Non) — Non → escalade réactive Plan 04 (DIAG-06 réactif)"
    - "Liste S9 « Mes diagnostics passés » lisible hors-ligne + reprendre + effacer (inline confirm) + état vide"
    - "Strip landing « Reprendre mon dernier diagnostic » (visiteur de retour, Alpine carnetResumeStrip)"
    - "tests/Browser/CarnetLocalTest.php — DIAG-07 (6 assertions d'existence + 3 browser Playwright skip documentés)"
  affects:
    - "resources/js/app.js (enregistrement Alpine.data carnetStore + carnetResumeStrip)"
    - "resources/views/livewire/diagnostic-wizard.blade.php (save-on-complete + re-test loop + liste S9 + reprendre/effacer)"
    - "resources/views/vitrine/diagnostic.blade.php (strip visiteur de retour)"
    - "tests/Pest.php + phpunit.xml (dossier Browser ajouté aux suites)"
tech_stack:
  added: []
  patterns:
    - "Carnet on-device : localStorage (JSON léger, pas de courbes lourdes — CDC 5.8), 0 serveur / 0 sync / 0 compte"
    - "DIAG-02 invariant : le carnet stocke le texte du résultat déjà calculé, jamais de formules/coefficients"
    - "XSS T-05-20 : rendu des entrées carnet via x-text Alpine uniquement, jamais raw HTML"
    - "Re-test loop in-session : Non → triggerRetestFailed() hook Livewire → escaladeNiveau='reactif' (réutilise Plan 04, pas de push/scheduler)"
    - "Destructive action : inline Alpine confirm (« Effacer tout l'historique de cet appareil ? »), pas de modal réflexe"
key_files:
  created:
    - "resources/js/diagnostic-carnet.js"
    - "tests/Browser/CarnetLocalTest.php"
  modified:
    - "resources/js/app.js"
    - "resources/views/livewire/diagnostic-wizard.blade.php"
    - "resources/views/vitrine/diagnostic.blade.php"
    - "tests/Pest.php"
    - "phpunit.xml"
decisions:
  - "localStorage retenu plutôt qu'IndexedDB/idb : le payload sauvé est du JSON léger (id, date, symptôme/diagnostic, confiance, mesures clés, résumé) — pas de courbes lourdes (CDC 5.8), zéro dépendance"
  - "NEW diagnostic reste online (doses serveur DIAG-02) ; seules les LECTURES d'historique sont locales/offline — unique assouplissement de la ligne online-only du SPEC, scopé au carnet"
  - "Re-test loop sans push ni scheduler (V2 différé) : purement in-session ; Non recâble le hook réactif Plan 04 (BLUEPRINT 4.2 : le lead le plus chaud)"
  - "Carnet sert surtout le visiteur anonyme ; pour un client connecté la persistance serveur existe déjà (Req5, Plan 03)"
metrics:
  duration: "~?"
  completed_date: "2026-05-30"
  tasks_completed: 2
  files_changed: 9
  human_gate_pending: "Task 3 — sign-off chimie/légal Pierre (LAUNCH GATE, bloquant, non auto-approuvable)"
---

# Phase 5 Plan 06 : Carnet local-only (DIAG-07) + boucle re-test légère (DIAG-06 réactif)

Les deux multiplicateurs de rétention livrés à **zéro infra récurrente** (contrainte directrice : infra mini × whaou maxi). Le carnet sauve chaque diagnostic terminé **sur l'appareil** (0 serveur / 0 sync / 0 compte) ; la boucle re-test capte le lead le plus chaud (essai raté) en réutilisant l'escalade réactive du Plan 04.

## Ce qui a été livré

### Task 1 — Store carnet on-device + save-on-complete + boucle re-test (commit 944a3ab)

**resources/js/diagnostic-carnet.js (219 lignes) :**
- Store `localStorage` exposant `save(entry)`, `all()`, `get(id)`, `clear()`, `hasEntries()`, `latestEntry()`, `markRetested(id, ok)`
- **DIAG-02 invariant** : stocke uniquement le texte du résultat déjà calculé (id, date, symptôme/diagnostic, confiance, mesures clés, résumé pour reprise) — **aucune formule ni coefficient de dose**
- **XSS T-05-20** : rendu via `x-text` Alpine uniquement
- Enregistré comme `Alpine.data` : `carnetStore` + `carnetResumeStrip` dans `resources/js/app.js`

**diagnostic-wizard.blade.php :**
- `x-init` sauvegarde automatique dans le carnet à la fin de `computeAndPersist` (client-side, 0 réseau)
- Boucle re-test : prompt « As-tu re-testé ? Ça a marché ? » (Oui/Non) en session — **0 push, 0 scheduler**
  - **Oui** : note positive lagon + `markRetested(id, true)`
  - **Non** : `triggerRetestFailed()` → hook Livewire `escaladeNiveau = 'reactif'` (Plan 04) + `markRetested(id, false)`
- Bouton S9 « Mes diagnostics passés » visible depuis l'écran de sélection de mode

### Task 2 — Liste S9 + strip landing + CarnetLocalTest (commit 5443444)

**Liste S9 « Mes diagnostics passés »** (dans le wizard) :
- Cartes anté-chronologiques : date, symptôme/diagnostic, chip de confiance, mesures clés
- Par carte : « Reprendre ce diagnostic » (re-hydrate le flow) + « Voir le PDF » (lien `diagnostic.pdf` si id serveur)
- Effacer : **inline confirm Alpine** (« Effacer tout l'historique de cet appareil ? » / « Effacer l'historique » danger / « Garder ») — pas de modal
- État vide : « Aucun diagnostic pour l'instant » + corps de confidentialité on-device + CTA primaire « Lancer un diagnostic »

**diagnostic.blade.php (landing, +47 lignes) :**
- Strip « Reprendre mon dernier diagnostic » (Alpine `carnetResumeStrip`)
- Visible **uniquement si** le carnet a des entrées (`hasLatest()` → 0 réseau, localStorage pur)
- Accent lagon `oklch(0.720 0.113 207)`, OKLCH only, pas de #000/#fff

**tests/Browser/CarnetLocalTest.php (151 lignes, DIAG-07) :**
- 6 assertions d'existence vertes : module carnet, invariant DIAG-02 (pas de math de dose dans le JS), markup S9, strip landing, re-test, route PDF
- 3 browser tests Playwright **skipped avec raison explicite** (Playwright non disponible dans ce projet)
- Vérifications manuelles documentées (cf. 05-VALIDATION.md Manual-Only : persistance cross-session, lecture offline 0 réseau, clear vide la liste)
- `tests/Pest.php` + `phpunit.xml` : dossier `Browser` ajouté aux suites

## Résultats des tests

```
./vendor/bin/pest --filter Carnet      → assertions d'existence vertes, 3 browser skip documentés
./vendor/bin/pest                       → 428 tests : 424 passed, 4 skipped (1166 assertions), 0 échec
```

Aucune régression (avant ce plan : 418 passed / 1 skipped).

## Déviations par rapport au plan

Aucune déviation de code. Choix `localStorage` plutôt qu'IndexedDB/idb assumé (payload JSON léger, CDC 5.8) — conforme à la latitude prévue dans le plan (« localStorage if the saved shape is small JSON »).

## Task 3 — LAUNCH GATE (bloquant, NON satisfait)

**`<task type="checkpoint:human-verify" gate="blocking-human">` — sign-off chimie + disclaimer/légal de Pierre.**

État : **PENDING**. `05-VALIDATION.md` « Validation Sign-Off » lit toujours `Approval: pending`. Aucune commande automatique ne peut satisfaire ce gable de responsabilité (conseil de dosage chimique = enjeu légal). À obtenir de Pierre avant tout lancement public du diagnostic :
1. Doses `DoseEngine` (`config/diagnostic-formulas.php`) conformes P0/P1 de `05-DIAGNOSTIC-EXPERT-AUDIT.md` (chlore-bas = rattrapage ~3-4 g/m³ ≠ choc ; ordre TAC→pH→chlore→stab→sel ; green-1 teste stabilisant ; acide détartrage opt-in/redirect)
2. Arbre de décision (`config/diagnostic-tree.php`) sain + chemin cartouche ne disant jamais « floculant »
3. Bloc sécurité (EPI / ne jamais mélanger / produit dans l'eau / délai baignade) sur chaque geste chimique
4. Disclaimer + mention légale
5. Numéro WhatsApp 0696 94 00 54

→ Voir mémoire `phase-5-awaiting-expert` : ce sign-off est le **gate de lancement bloquant** de la Phase 5.

## Threat Surface Scan

| Threat | Mitigation appliquée |
|--------|----------------------|
| T-05-18 (fuite des formules de dose dans le JS client) | Le carnet stocke le texte du résultat déjà calculé ; aucune arithmétique de dose dans `diagnostic-carnet.js` (DIAG-02) |
| T-05-19 (PII carnet sur appareil partagé) | Accepté : on-device only, 0 serveur/0 sync, l'utilisateur efface (inline confirm) ; faible sensibilité |
| T-05-20 (XSS via texte carnet rendu) | Rendu `x-text` Alpine uniquement, jamais raw HTML |
| T-05-SC (installs) | Accepté : zéro nouvelle dépendance (localStorage natif) |

## Self-Check: PASSED (code) — Task 3 LAUNCH GATE pending

Fichiers créés : `resources/js/diagnostic-carnet.js` ✓ · `tests/Browser/CarnetLocalTest.php` ✓
Fichiers modifiés : `app.js` ✓ · `diagnostic-wizard.blade.php` ✓ · `diagnostic.blade.php` ✓ · `tests/Pest.php` ✓ · `phpunit.xml` ✓
Commits : `944a3ab` (carnet + re-test) ✓ · `5443444` (liste S9 + strip + test) ✓
Tests : suite complète 424 passed / 4 skipped / 0 échec ✓
DIAG-02 : grep aucune math de dose dans `diagnostic-carnet.js` ✓
Reste : Task 3 sign-off Pierre (bloquant lancement, hors code).
