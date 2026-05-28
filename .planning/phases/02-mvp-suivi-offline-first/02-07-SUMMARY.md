---
phase: "02"
plan: "07"
subsystem: portail-client
tags: [magic-link, auth, livewire, portail, security]
dependency_graph:
  requires: ["02-01"]
  provides: [portail-client-auth, passage-timeline-view]
  affects: [auth, portail, routes]
tech_stack:
  added: []
  patterns:
    - "Guard isolation via bootstrap/app.php exceptions.render (D-53)"
    - "D-50 pattern: GET confirme → statique, POST confirme → consomme token"
    - "Token format cesargb/laravel-magiclink: id:secret"
    - "Anti-énumération: usleep(random_int) + message générique + throttle"
key_files:
  created:
    - app/Http/Controllers/Portail/MagicLinkController.php
    - app/Http/Requests/MagicLinkRequest.php
    - app/Mail/MagicLinkMail.php
    - resources/views/emails/magic-link.blade.php
    - resources/views/portail/magic-link-request.blade.php
    - resources/views/portail/confirm.blade.php
    - resources/views/portail/passages.blade.php
    - app/Livewire/Portail/PassageTimeline.php
    - resources/views/livewire/portail/passage-timeline.blade.php
    - tests/Feature/MagicLinkTest.php
    - tests/Feature/ClientSessionTest.php
    - tests/Feature/PortailAccessTest.php
  modified:
    - app/Providers/FortifyServiceProvider.php
    - routes/portail.php
    - bootstrap/app.php
decisions:
  - "D-50 SafeLinks: GET /auth/confirm purement statique — aucun side-effect, aucun appel magiclink"
  - "Token passé comme query param ml=id:secret, parsé par getValidMagicLinkByToken()"
  - "Guard clients redirect via exceptions.render dans bootstrap/app.php (pas de redirectTo dans config/auth.php)"
  - "LoginAction::run() appelé pour l'auth, puis $magicLink->visited() appelé manuellement (bypass du middleware package)"
metrics:
  duration: "~90 minutes (2 sessions)"
  completed_date: "2026-05-28"
  tasks_completed: 2
  tasks_total: 2
  files_created: 12
  files_modified: 3
---

# Phase 02 Plan 07: Portail Client Magic-Link + PassageTimeline Summary

Magic-link auth flow complet pour guard `clients` avec PassageTimeline Livewire navy-drenched — GET confirm statique (SafeLinks M365), isolation inter-clients T-2-07G, 18 tests verts.

## Tasks Completed

| Task | Name | Commit | Files clés |
|------|------|--------|-----------|
| 1 | MagicLinkController + auth flow | cc8b331 | MagicLinkController, MagicLinkMail, vues portail, routes/portail.php |
| 1 RED | Tests M1-M8 + S1-S3 (RED) | fcf7bab | MagicLinkTest.php, ClientSessionTest.php |
| 2 | PassageTimeline + PortailAccessTest | b1b66c5 | PassageTimeline.php, passage-timeline.blade.php, PortailAccessTest.php |

## Test Results

| Suite | Tests | Résultat |
|-------|-------|---------|
| MagicLinkTest (M1-M8) | 8 | PASS |
| ClientSessionTest (S1-S3) | 3 | PASS |
| PortailAccessTest (P1-P7) | 7 | PASS |
| **Total plan 02-07** | **18** | **PASS** |
| Suite complète | 188 | 187 pass / 1 skip |

Tests critiques validés :
- **P3** : Client A ne voit pas les passages de Client B (filtre `where('client_id')` — T-2-07G)
- **P6** : Guard `web` (admin) ne donne pas accès au portail `clients`
- **M4** : GET /auth/confirm ne consomme pas le token (D-50 — SafeLinks M365)

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 2 - Missing critical functionality] Redirect guard clients → /auth/magic manquant**
- **Found during:** Exécution des tests P1 et P6
- **Issue:** `auth:clients` middleware redirige vers `/login` (guard web) par défaut — isolation inter-guard brisée
- **Fix:** Ajout `exceptions.render` dans `bootstrap/app.php` — intercepte `AuthenticationException` pour les guards `clients` et redirige vers `portail.magic-link.request`
- **Files modified:** `bootstrap/app.php`
- **Commit:** cc8b331

### TDD Gate Compliance

**Session break — déviation de procédure TDD :**
Le test RED pour Task 2 (PortailAccessTest) n'a pas eu un commit RED séparé avant l'implémentation — la session précédente s'est terminée avec l'implémentation Task 2 déjà créée. Le commit `fcf7bab` (RED) couvre uniquement Task 1. Les tests Task 2 ont été créés et exécutés directement en GREEN. Les 7 tests PortailAccessTest passent correctement avec l'implémentation.

## Known Stubs

Aucun stub critique. Les données affichées dans PassageTimeline proviennent directement de la base via Eloquent (pas de données hardcodées). L'état vide ("Aucun passage enregistré pour le moment.") est intentionnel et fonctionnel.

## Threat Flags

| Flag | Fichier | Description |
|------|---------|-------------|
| threat_flag: auth-redirect | bootstrap/app.php | Nouveau gestionnaire d'exception auth — redirige guard clients vers /auth/magic. Correction D-53 : sans ça un User admin (guard web) pourrait être redirigé vers /login et tenter de s'authentifier via le portail client. |

## Self-Check: PASSED

Tous les fichiers créés existent dans le worktree.
Commits vérifiés : fcf7bab (RED Task 1), cc8b331 (GREEN Task 1), b1b66c5 (GREEN Task 2).
Suite complète : 187 tests pass / 1 skip (aucune régression).
