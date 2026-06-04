---
phase: 08-vitrine-corrections-retours-pierre
plan: "02"
subsystem: vitrine
tags: [depannage, route, blade, sitemap, services-grid]
dependency_graph:
  requires:
    - 08-01 (DepannageRouteTest stubs RED)
  provides:
    - GET /services/depannage (200, h1 Dépannage, WhatsApp CTA, BreadcrumbList JSON-LD)
    - DepannageRouteTest 6/6 green
    - services-grid Dépannage card links to route('services.depannage')
    - /sitemap.xml includes /services/depannage
  affects:
    - routes/vitrine.php
    - app/Http/Controllers/VitrineController.php
    - resources/views/vitrine/services/depannage.blade.php
    - resources/views/vitrine/partials/services-grid.blade.php
    - resources/views/vitrine/partials/services-detail.blade.php
    - app/Http/Controllers/SitemapController.php
tech_stack:
  added: []
  patterns:
    - Service page following spa.blade.php gabarit (hero + content body + CTA band)
    - BreadcrumbSchema DI injected via controller, rendered by layout
    - <a> card pattern for service grid cards with internal links
decisions:
  - D-05: Page légère (hero + 4 bullets + CTA WhatsApp) — not entretien-recurrent structure
  - D-06: WhatsApp is primary CTA in hero and CTA band
  - D-07: 4 pannes courantes Martinique (pompe, filtration, eau verte, fuite hydraulique)
  - D-08: Dépannage card in services-grid converted from <article> to <a href=services.depannage>
  - D-10: services-detail "sans standard téléphonique ni rotation d'interlocuteurs" replaced with positive "avec un compte-rendu après chaque intervention"
key_files:
  created:
    - resources/views/vitrine/services/depannage.blade.php
  modified:
    - routes/vitrine.php
    - app/Http/Controllers/VitrineController.php
    - resources/views/vitrine/partials/services-grid.blade.php
    - resources/views/vitrine/partials/services-detail.blade.php
    - app/Http/Controllers/SitemapController.php
    - phpunit.xml
metrics:
  duration: "~25 minutes (including worktree setup)"
  completed: "2026-06-04"
  tasks: 2
  files: 6
---

# Phase 08 Plan 02: V5 Dépannage Page Summary

Dedicated `/services/depannage` page created with hero, 4 bullet pannes, WhatsApp CTA, BreadcrumbList JSON-LD; Dépannage card in services-grid now links to the page; sitemap updated; DepannageRouteTest 6/6 green.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Route + controller method for /services/depannage | `a2831d5` | routes/vitrine.php, VitrineController.php |
| 2 | Depannage Blade view + services-grid link + sitemap entry | `9e80afb` | depannage.blade.php, services-grid.blade.php, services-detail.blade.php, SitemapController.php, phpunit.xml |

## Verification Results

- DepannageRouteTest: 6/6 green
- StaticPagesTest: 17/17 green (no regressions)
- HomePageTest: 12/12 green (no regressions)
- GET /services/depannage: 200 — h1 contains "Dépannage", wa.me/596696940054 present, BreadcrumbList JSON-LD present
- GET /services: Dépannage card href = route('services.depannage')
- GET /sitemap.xml: contains /services/depannage

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Worktree PHP class isolation: autoloader resolves App\ to main repo**

- **Found during:** Task 2 verification
- **Issue:** The vendor symlink causes Composer's PSR-4 autoloader to resolve `App\Http\Controllers\VitrineController` from the main repo path (`/Users/amnesia/dev/dloazur/app/`), not the worktree. This is a deeper issue than the APP_BASE_PATH fix used in plan 01 (which only works for view/route file resolution). Result: `Call to undefined method VitrineController::depannage()` on test run.
- **Fix (plan-scoped):** Applied worktree changes to main repo temporarily (copy, test, revert) to verify 6/6 tests pass. Worktree commits contain the real changes. Tests will be canonical after merge to main.
- **APP_BASE_PATH:** Added to phpunit.xml for view isolation (same as plan 01 workaround). Will be stripped on merge per established pattern.
- **Files modified:** phpunit.xml
- **Commit:** `9e80afb`

**2. [Rule 1 - Minor] services-grid Dépannage card was `<article>` not `<a>`**

- **Found during:** Task 2 implementation
- **Issue:** The plan described changing `href="{{ route('services') }}"` on an `<a>` tag, but the actual services-grid.blade.php had the Dépannage card as a bare `<article>` element (no anchor at all). The test required `route('services.depannage')` to appear on the `/services` page.
- **Fix:** Converted the `<article>` to an `<a href="{{ route('services.depannage') }}"` using the pattern from 08-PATTERNS.md §services-grid. Added "En savoir plus" CTA span consistent with the pattern. No other cards touched.
- **Files modified:** resources/views/vitrine/partials/services-grid.blade.php

## Known Stubs

None — all content is real copy, no placeholder text.

## Threat Flags

No new security-relevant surface introduced. `/services/depannage` is a static public GET route under `cache.headers:vitrine` middleware. No user input, no PII, no auth tokens.

## Self-Check: PASSED

- resources/views/vitrine/services/depannage.blade.php: EXISTS, h1 contains "Dépannage", wa.me/596696940054 present
- routes/vitrine.php: Route services.depannage defined inside cache.headers:vitrine group
- app/Http/Controllers/VitrineController.php: depannage() method added after spa()
- resources/views/vitrine/partials/services-grid.blade.php: Dépannage card has href="{{ route('services.depannage') }}"
- app/Http/Controllers/SitemapController.php: /services/depannage entry added
- Commit `a2831d5`: EXISTS (route + controller)
- Commit `9e80afb`: EXISTS (blade + grid + sitemap)
- DepannageRouteTest 6/6: PASSED (verified against merged state)
- StaticPagesTest 17/17 + HomePageTest 12/12: NO REGRESSIONS
