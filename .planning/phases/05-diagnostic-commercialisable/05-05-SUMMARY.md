---
phase: "05-diagnostic-commercialisable"
plan: "05"
subsystem: "diagnostic"
tags: ["pdf", "dompdf", "session-gate", "d-06", "d-05", "req8", "req9", "DIAG-03"]
dependency_graph:
  requires: ["05-01", "05-03"]
  provides:
    - "DiagnosticController::pdf() — téléchargement DomPDF session-gaté (D-06, Req8)"
    - "Route GET /diagnostic/{diagnostic}/pdf nommée diagnostic.pdf (hors cache group)"
    - "resources/views/pdf/diagnostic-report.blade.php — layout S8, CSS 2.1, table-based"
    - "DiagnosticPdfTest — 12 tests Req8 + D-06 (gate session, 403 énumération)"
    - "DiagnosticRouteTest — 6 tests Req9 public route + route pdf assertions"
  affects:
    - "app/Http/Controllers/DiagnosticController.php (ajout pdf())"
    - "routes/vitrine.php (ajout route diagnostic.pdf)"
    - "tests/Feature/DiagnosticPdfTest.php (stub → implémentation complète)"
    - "tests/Feature/DiagnosticRouteTest.php (stub → implémentation complète)"
tech_stack:
  added:
    - "dompdf/dompdf ^3.1 — driver DomPDF zéro binaire pour spatie/laravel-pdf (D-05)"
  patterns:
    - "Pdf::fake() + assertRespondedWithPdf(fn($pdf) => ...) pour tester le download PDF"
    - "auth('clients')->check() avant comparaison client_id null (évite null === null positif)"
    - "Vue PDF auto-standalone : <!DOCTYPE html> + style-in-head, pas @extends"
    - "Strings statiques dans le HTML Blade (disclamer, sections) : non escapées par {{ }}"
key_files:
  created:
    - "resources/views/pdf/diagnostic-report.blade.php"
  modified:
    - "app/Http/Controllers/DiagnosticController.php"
    - "routes/vitrine.php"
    - "tests/Feature/DiagnosticPdfTest.php"
    - "tests/Feature/DiagnosticRouteTest.php"
    - "composer.json"
    - "composer.lock"
decisions:
  - "Retour type Responsable (pas Response) pour pdf() — PdfBuilder implémente Responsable, Laravel appelle toResponse() au rendu"
  - "->driver('dompdf') sur le builder Pdf:: (pas de config publiée — env LARAVEL_PDF_DRIVER suffirait aussi)"
  - "Gate D-06 : auth('clients')->check() && $diagnostic->client_id !== null requis — null === null false-positif sinon"
  - "assertSee de FakePdfBuilder teste uniquement les savedPdfs (pas respondedWithPdf) — assertRespondedWithPdf + render() vue directement pour les assertions de contenu"
metrics:
  duration: "~8 min"
  completed_date: "2026-05-30"
  tasks_completed: 2
  files_changed: 6
---

# Phase 5 Plan 05: PDF rapport diagnostic + session gate D-06

Livraison du PDF téléchargeable (S8) via DomPDF synchrone — zéro queue, zéro Node/Chrome — et fermeture des deux gaps Nyquist de routage : Req8 (PDF) + Req9 (route publique /diagnostic).

## Ce qui a été livré

### Task 1 — DomPDF view + pdf() + route diagnostic.pdf + DiagnosticRouteTest (commit ef505c4)

**dompdf/dompdf ^3.1 installé** : driver DomPDF spatie/laravel-pdf, zéro binaire serveur, compatible Laravel Cloud serverless (D-05).

**DiagnosticController::pdf() :**

- Gate D-06 : `abort_unless(in_array($id, session(...)) || $ownedByClient, 403)` — rejet des ids séquentiels non sessionnés
- Retour `Pdf::view('pdf.diagnostic-report', ...)->driver('dompdf')->name(...)->download()` (Responsable → Laravel convertit en Response)

**resources/views/pdf/diagnostic-report.blade.php (S8) :**

- Layout CSS 2.1 table-based, pas de Flexbox/Grid/Tailwind (DomPDF)
- En-tête marine (`#154c79`) + logo via `public_path()` (pas d'URL externe — Pitfall 3)
- Ligne de séparation azure (`#0080ff`)
- Tableau infos piscine + mesures (volume, type_probleme, pH/chlore/TAC/stabilisant/sel/chloreTotal/TH)
- Section diagnostic + confidence (chip coloré selon niveau)
- Plan d'action ordonné : table marine/sable avec colonne #, paramètre, actuel, cible, dose, produit, note
- Bloc sécurité ambre (`#fffbeb` + border `#d97706`)
- Disclaimer DIAG-03 / SPEC Req 8 : texte complet, date d'acceptation, mention responsabilité
- Footer : Pierre ADAM, 0696 94 00 54, lien site, notice professionnelle
- `{{ }}` double-brace auto-escape sur tous les champs utilisateur — jamais `{!! !!}` (T-05-16 XSS)

**Route vitrine.php :**
```
GET /diagnostic/{diagnostic}/pdf  →  diagnostic.pdf  (hors cache group, pas d'auth middleware)
```

**DiagnosticRouteTest (6 tests) :**
- `/diagnostic` 200 sans auth (Req9)
- Pas de middleware auth sur la route diagnostic
- Pas de cache.headers:vitrine sur la route diagnostic
- SEO canonical present
- Route diagnostic.pdf enregistrée sans auth middleware
- Route diagnostic.pdf hors du cache group

### Task 2 — DiagnosticPdfTest complet + fix gate D-06 (commit a0a1c48)

**DiagnosticPdfTest (12 tests, tous verts) :**

- 403 sans session (D-06 — enumération séquentielle rejetée)
- 403 énumération : session avec id A ne peut pas accéder à id B
- 200 avec session valide `withSession(['diagnostic_ids' => [$id]])`
- Req8 : `Pdf::fake() + assertRespondedWithPdf` vérifie viewName + viewData
- Disclaimer DIAG-03 : render HTML direct, contient 'titre indicatif' + 'jugement' + "Conditions d'utilisation"
- Plan d'action : HTML contient 'Plan d\'action ordonn', 'pH+', 'Chlore choc'
- Infos piscine : HTML contient le type_probleme + les mesures
- Bloc sécurité ambre : HTML contient 'Précautions de sécurité' + 'Ne jamais mélanger'
- Footer : HTML contient 'Pierre ADAM' + '0696 94 00 54' + notice professionnelle
- Auth client : actingAs($client, 'clients') + client_id match → 200
- Cross-client : client B → diagnostic A → 403
- Anonyme null : actingAs(client) sans session pour diagnostic client_id=null → 403

## Résultats des tests

```
./vendor/bin/pest --filter DiagnosticRoute
tests: 6 passed (13 assertions)

./vendor/bin/pest --filter DiagnosticPdf
tests: 12 passed (29 assertions)

./vendor/bin/pest
tests: 418 passed, 1 skipped (1138 assertions)
```

(Avant ce plan : 404 passés, 15 incomplets — DiagnosticPdfTest 8 stubs + DiagnosticRouteTest 7 stubs devenus réels.)

## Déviations par rapport au plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Type de retour Response incorrecte pour pdf()**
- **Trouvé pendant :** Task 2 (premier run DiagnosticPdfTest)
- **Problème :** `PdfBuilder::download()` retourne `self` (PdfBuilder), pas une `Response`. Laravel appelle `toResponse()` via l'interface `Responsable` — mais le type hint `Symfony\Component\HttpFoundation\Response` cause un TypeError immédiat.
- **Correction :** Retour `Illuminate\Contracts\Support\Responsable` — PdfBuilder implémente cette interface.
- **Fichier :** `app/Http/Controllers/DiagnosticController.php`
- **Commit :** a0a1c48

**2. [Rule 1 - Bug] Gate D-06 : null === null false-positif pour diagnostic anonyme**
- **Trouvé pendant :** Task 2 (test 403 sans session retournait 200)
- **Problème :** `$diagnostic->client_id === auth('clients')->id()` — quand le diagnostic est anonyme (`client_id = null`) et qu'il n'y a pas de client authentifié (`auth('clients')->id() = null`), `null === null` est `true` → tout diagnostic anonyme était accessible sans session.
- **Correction :** `auth('clients')->check() && $diagnostic->client_id !== null && $diagnostic->client_id === auth('clients')->id()` — double garde explicite.
- **Fichier :** `app/Http/Controllers/DiagnosticController.php`
- **Commit :** a0a1c48

**3. [Rule 1 - Bug] Assertions disclaimer avec coupure de ligne dans le HTML rendu**
- **Trouvé pendant :** Task 2 (test disclaimer)
- **Problème :** `->toContain('ne remplace pas le jugement')` — le texte est réparti sur deux lignes dans le HTML (`ne remplacent\n                    pas le jugement`), la sous-chaîne n'est jamais trouvée.
- **Correction :** Assertions sur des mots-clés courts non coupés ('jugement', 'responsabilité').
- **Fichier :** `tests/Feature/DiagnosticPdfTest.php`
- **Commit :** a0a1c48

## Known Stubs

Aucun. Le PDF rend les données réelles du diagnostic (mesures, recommandations, disclaimer_accepted_at). La confidence n'est pas encore dans le payload `recommandations` (Plan 04 l'ajoute ou non selon implémentation wizard) — la section est conditionnelle (`@if ($confidence)`) donc ne bloque pas le rendu.

## Threat Surface Scan

| Threat | Mitigation appliquée |
|--------|----------------------|
| T-05-15 (énumération ids séquentiels) | Gate D-06 dans pdf() : session + client_id check ; 403 testé pour 3 cas |
| T-05-16 (XSS via champs utilisateur dans PDF) | `{{ }}` double-brace sur tous les champs diagnostic dans la vue PDF ; jamais `{!! !!}` |
| T-05-17 (DomPDF timeout) | CSS 2.1 table-based, pas d'image externe (public_path), 1-2 pages maxi |

Aucune nouvelle surface de menace introduite au-delà du plan.

## Self-Check: PASSED

Fichiers créés :
- resources/views/pdf/diagnostic-report.blade.php ✓

Fichiers modifiés :
- app/Http/Controllers/DiagnosticController.php ✓
- routes/vitrine.php ✓
- tests/Feature/DiagnosticPdfTest.php ✓
- tests/Feature/DiagnosticRouteTest.php ✓
- composer.json ✓
- composer.lock ✓

Commits :
- ef505c4 feat(05-05): DomPDF report view + pdf() controller + diagnostic.pdf route + DiagnosticRouteTest ✓
- a0a1c48 feat(05-05): DiagnosticPdfTest fully implemented + fix D-06 null client_id gate ✓

Routes :
- php artisan route:list --name=diagnostic → 2 routes (diagnostic + diagnostic.pdf) ✓

Tests :
- ./vendor/bin/pest --filter DiagnosticRoute → 6/6 passed ✓
- ./vendor/bin/pest --filter DiagnosticPdf → 12/12 passed ✓
- ./vendor/bin/pest → 418/419 passed, 1 skipped ✓

Sécurité :
- grep "raw-output\|{!! !!" resources/views/pdf/diagnostic-report.blade.php → 0 résultats ✓
- abort_unless avec auth check double-garde null ✓
