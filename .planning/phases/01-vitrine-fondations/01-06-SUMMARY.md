---
phase: 01-vitrine-fondations
plan: "06"
subsystem: cutover-gate
tags: [cache-headers, middleware, accessibility, tests, playbook, dns, cutover]
one_liner: "Middleware CacheHeaders tri-profil (300s/3600s/no-cache) + suite de tests pré-vol + playbook CUTOVER.md Pierre ADAM + template inventaire Zyro"

dependency_graph:
  requires: [01-01, 01-02, 01-03, 01-04, 01-05]
  provides: [SITE-07-automation]
  affects: [bootstrap/app.php, routes/vitrine.php, routes/blog.php, routes/web.php]

tech_stack:
  added: []
  patterns:
    - "Middleware à deux temps : route middleware pose l'attribut _cache_profile ; middleware global (appendé) applique le header en dernier pour survivre à Livewire::DisableBackButtonCacheMiddleware"
    - "Cache-Control public,max-age=300 sur les routes vitrine statiques (RESEARCH Pitfall 11)"
    - "h1 sr-only sur /services et /realisations (a11y correction Plan 06)"

key_files:
  created:
    - app/Http/Middleware/CacheHeaders.php
    - tests/Feature/CutoverReadinessTest.php
    - tests/Feature/AccessibilityTest.php
    - .planning/phases/01-vitrine-fondations/CUTOVER.md
    - .planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md
  modified:
    - bootstrap/app.php
    - routes/vitrine.php
    - routes/blog.php
    - routes/web.php
    - resources/views/vitrine/services.blade.php
    - resources/views/vitrine/realisations.blade.php

decisions:
  - "Middleware CacheHeaders en deux temps (attribut requête + global append) pour résister à Livewire DisableBackButtonCacheMiddleware qui écrase les en-têtes Cache-Control"
  - "h1 sr-only ajouté sur /services et /realisations : ces pages utilisaient h2 comme premier titre (a11y bug Rule 2)"
  - "Test 8 (STAGING_URL) skippé en local, actif uniquement en CI avec le secret STAGING_URL configuré"
  - "prefers-reduced-motion test vérifie .001ms (Tailwind v4 omet le zéro initial) plutôt que 0.001ms"

metrics:
  duration_minutes: 43
  completed_date: "2026-05-28T14:43:00Z"
  tasks_completed: 1
  tasks_deferred_to_human: 1
  files_created: 5
  files_modified: 6
  tests_added: 48
  tests_total: 153
  tests_passing: 152
  tests_skipped: 1
---

# Phase 1 Plan 06 : Cutover Gate — Middleware Cache + Tests Pré-vol + CUTOVER.md

## Ce qui a été livré

### Task 1 (automatisé) — COMPLÈTE

**Middleware CacheHeaders (`app/Http/Middleware/CacheHeaders.php`)**

Trois profils HTTP Cache-Control :
- `vitrine` : `public, max-age=300, must-revalidate` (5 min) — toutes les pages statiques
- `sitemap` : `public, max-age=3600, must-revalidate` (1 h) — `/sitemap.xml`
- `health`  : `no-cache, no-store, must-revalidate` — `/up`

**Architecture deux temps** (déviation documentée ci-dessous) : le route middleware pose l'attribut `_cache_profile` sur la requête sans toucher la réponse ; un `$middleware->append()` dans `bootstrap/app.php` fait tourner le même middleware en DERNIER dans la pile globale, après `Livewire\DisableBackButtonCacheMiddleware`, et applique le bon header.

**Routes mises à jour**
- `routes/vitrine.php` : toutes les routes statiques wrappées dans `Route::middleware('cache.headers:vitrine')`, `/sitemap.xml` dans `cache.headers:sitemap`, `/contact` exclu (Livewire stateful)
- `routes/blog.php` : groupe `cache.headers:vitrine`
- `routes/web.php` : `/up` wrappé dans `cache.headers:health`

**Tests automatisés** (8 + 6 = 14 tests au total, 47/48 passent)

`CutoverReadinessTest.php` :
1. Chaque route publique retourne 200 (14 routes en dataset)
2. GET /admin sans auth → 302 vers /login
3. GET /admin authentifié (PierreSeeder) → 200
4. robots.txt contient `Sitemap:` avec URL → `/sitemap.xml`
5. Routes statiques vitrine → Cache-Control contient `public` + `max-age=300` (7 routes)
6. /up → pas de directive `public`
7. /sitemap.xml → `max-age=3600`
8. STAGING_URL (skippé localement, actif en CI)

`AccessibilityTest.php` :
9. Chaque page publique déclare `lang="fr"`
10. Chaque page a exactement 1 `<h1>` (8 pages)
11. Chaque `<img>` de la home a un attribut `alt`
12. Skip link « Aller au contenu principal » avant `<main>` sur / et /services
13. CSS compilé contient `focus-visible` + `outline`
14. CSS compilé contient `prefers-reduced-motion` + `.001ms`

**Playbook CUTOVER.md** — `.planning/phases/01-vitrine-fondations/CUTOVER.md`

Document complet exécutable par Pierre ADAM (Phases 0, A, B, C, D, E) avec checklist, scores Lighthouse, validation Brevo, bascule Hostinger DNS, rollback.

**Template ZYRO-URL-INVENTORY.md** — `.planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md`

Template que Pierre ADAM remplit depuis Google Search Console avant la Phase B du playbook.

---

### Task 2 (human-verify) — EN ATTENTE PIERRE ADAM

Task 2 est un checkpoint humain bloquant. Les validations suivantes ne peuvent pas être automatisées :

#### Phase A — Validations staging (Pierre remplit dans CUTOVER.md)

- [ ] 1. `./vendor/bin/pest --ci` → exit 0
- [ ] 2. Rendu visuel sur staging (Chrome + iOS Safari)
- [ ] 3. Lighthouse mobile (Perf ≥ 85, SEO ≥ 90, A11y ≥ 90)
- [ ] 4. Sitemap validator (xml-sitemaps.com)
- [ ] 5. Google Rich Results Test (LocalBusiness Plumber)
- [ ] 6. Facebook OG debugger (hero image + title + description)
- [ ] 7. WhatsApp CTA mobile (numéro 0696 94 00 54)
- [ ] 8. Formulaire contact → email réel à contact@dloazurpiscines.com via Brevo EU
- [ ] 9. Login staging avec credentials Pierre → /admin → logout

#### Phase B — Inventaire Zyro (Pierre remplit ZYRO-URL-INVENTORY.md)

- [ ] 10. URLs indexées depuis Google Search Console
- [ ] 11. Décision redirection : aucune si ≤ 3 URLs, sinon Route::redirect()

#### Phase C — Bascule DNS (optionnel, per D-25)

- [ ] 12-23. Voir CUTOVER.md Phase C (peut être différé — Phase 1 est valide sur staging)

**Signal de reprise :** `phase-a-green | inventory-captured | phase-c-{executed|deferred}`

---

## Déviations par rapport au plan

### [Rule 3 - Blocking] Architecture deux temps pour CacheHeaders vs. Livewire

**Trouvé pendant :** Task 1 — premier run des tests cache

**Problème :** `Livewire\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware` est injecté comme middleware global via `$kernel->pushMiddleware()` et s'exécute EN DERNIER dans la direction sortante (réponse), écrasant tous les `Cache-Control: public` posés par les middlewares de route ou de groupe. La page `/` (avec `<livewire:google-reviews />`) recevait `max-age=0, must-revalidate, no-cache, no-store, private`.

**Fix :** Le middleware `CacheHeaders` utilise un mécanisme deux temps :
1. **Route middleware** `cache.headers:vitrine` → pose l'attribut `_cache_profile` sur la requête (pas de header)
2. **Middleware global appendé** (`$middleware->append(CacheHeaders::class)`) → tourne APRÈS Livewire dans la direction sortante et applique le bon header via l'attribut

Le middleware est no-op si `_cache_profile` est absent (routes admin, login, etc.).

**Fichiers modifiés :** `app/Http/Middleware/CacheHeaders.php`, `bootstrap/app.php`

### [Rule 2 - Missing a11y] h1 absent sur /services et /realisations

**Trouvé pendant :** Task 1 — AccessibilityTest Test 10

**Problème :** Les pages `/services` et `/realisations` utilisaient `<h2>` comme premier titre de section, sans `<h1>` de page. Violation a11y (structure de titres WCAG 1.3.1).

**Fix :** Ajout d'un `<h1 class="sr-only">` (visually hidden) au début du `@section('content')` de chaque page. Le texte est accessible aux lecteurs d'écran sans perturber le design visuel.

**Fichiers modifiés :** `resources/views/vitrine/services.blade.php`, `resources/views/vitrine/realisations.blade.php`

### [Rule 1 - Bug] prefers-reduced-motion test corrigé : .001ms vs 0.001ms

**Trouvé pendant :** Task 1 — AccessibilityTest Test 14

**Problème :** Le plan spécifiait `'0.001ms'` mais Tailwind v4 CSS minifié génère `.001ms` (sans zéro initial). L'assertion aurait toujours échoué.

**Fix :** Assertion modifiée pour rechercher `'.001ms'`.

---

## Chirurgie cross-plans (Wave 3)

| Fichier | Plan propriétaire | Type de modification |
|---------|-------------------|---------------------|
| `bootstrap/app.php` | Plan 01-01 | Ajout alias + append global CacheHeaders |
| `routes/vitrine.php` | Plan 01-03 | Wrapping dans Route::middleware('cache.headers:*') |
| `routes/blog.php` | Plan 01-04 | Wrapping dans Route::middleware('cache.headers:vitrine') |
| `routes/web.php` | Plan 01-01 | /up wrappé dans cache.headers:health |

Aucun conflit — Wave 3 s'exécute strictement après Wave 2 (Plans 01-01 à 01-05 complétés).

---

## Stubs connus

Aucun stub fonctionnel. Le ZYRO-URL-INVENTORY.md est un template intentionnellement vide — Pierre ADAM le remplit manuellement depuis Google Search Console (Phase B). Le CUTOVER.md contient des cases à cocher intentionnellement vides.

---

## Self-Check: PASSED

| Vérification | Résultat |
|---|---|
| app/Http/Middleware/CacheHeaders.php existe | FOUND |
| tests/Feature/CutoverReadinessTest.php existe | FOUND |
| tests/Feature/AccessibilityTest.php existe | FOUND |
| .planning/.../CUTOVER.md existe | FOUND |
| .planning/.../ZYRO-URL-INVENTORY.md existe | FOUND |
| .planning/.../01-06-SUMMARY.md existe | FOUND |
| Commit 3c52249 existe | FOUND |
| CacheHeaders contient `public, max-age=300` | OK |
| CacheHeaders contient `public, max-age=3600` | OK |
| CacheHeaders contient `no-cache, no-store, must-revalidate` | OK |
| bootstrap/app.php a l'alias `cache.headers` | OK |
| routes/vitrine.php a `cache.headers:vitrine` + `cache.headers:sitemap` | OK |
| routes/blog.php a `cache.headers:vitrine` | OK |
| routes/web.php a `cache.headers:health` | OK |
| CUTOVER.md contient : Lighthouse, sitemap-validator, TTL, Hostinger, Brevo, rollback | OK |
| ZYRO-URL-INVENTORY.md contient `Google Search Console` | OK |
| Suite Pest : 152 passed, 1 skipped, 0 failed | PASSED |
