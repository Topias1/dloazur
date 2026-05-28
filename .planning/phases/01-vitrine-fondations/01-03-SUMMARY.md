---
phase: "01"
plan: "03"
subsystem: vitrine
tags: [tdd, seo, blade, schema-org, sitemap, tailwind, json-ld]
dependency_graph:
  requires: ["01-01", "01-02"]
  provides: ["vitrine-routes", "sitemap", "localbusiness-schema", "vitrine-pages"]
  affects: ["01-04", "01-05", "01-06"]
tech_stack:
  added:
    - "spatie/schema-org — LocalBusiness/Plumber JSON-LD builder"
    - "spatie/laravel-sitemap — XML sitemap generator"
    - "config/pricing.php — D-32 config-driven tarif indicatif"
  patterns:
    - "VitrineController with DI-injected LocalBusinessSchema"
    - "Blade @push('head') for per-page JSON-LD injection"
    - "@@context / @@type Blade escaping for JSON-LD keys"
    - "class_exists() guards for Plan 04 Livewire components"
    - "photo-grade CSS filter utility for all content photos"
key_files:
  created:
    - routes/vitrine.php
    - app/Http/Controllers/VitrineController.php
    - app/Http/Controllers/SitemapController.php
    - app/Support/SchemaOrg/LocalBusinessSchema.php
    - config/pricing.php
    - resources/views/vitrine/home.blade.php
    - resources/views/vitrine/services.blade.php
    - resources/views/vitrine/realisations.blade.php
    - resources/views/vitrine/contact.blade.php
    - resources/views/vitrine/mentions-legales.blade.php
    - resources/views/vitrine/cgv.blade.php
    - resources/views/vitrine/confidentialite.blade.php
    - resources/views/vitrine/services/eau-verte-urgence.blade.php
    - resources/views/vitrine/partials/hero.blade.php
    - resources/views/vitrine/partials/services-grid.blade.php
    - resources/views/vitrine/partials/urgence-eau-verte.blade.php
    - resources/views/vitrine/partials/how-it-works.blade.php
    - resources/views/vitrine/partials/hospitality.blade.php
    - resources/views/vitrine/partials/realisations-grid.blade.php
    - resources/views/vitrine/partials/pierre.blade.php
    - resources/views/vitrine/partials/espace-client-teaser.blade.php
    - resources/views/vitrine/partials/testimonials.blade.php
    - resources/views/vitrine/partials/engagements.blade.php
    - resources/views/vitrine/partials/final-cta.blade.php
    - resources/views/components/icon/sun.blade.php
    - resources/views/components/icon/star.blade.php
    - resources/views/components/icon/sparkle.blade.php
    - resources/views/components/icon/shield.blade.php
    - resources/views/components/icon/calendar.blade.php
    - tests/Feature/HomePageTest.php
    - tests/Feature/SeoTest.php
    - tests/Feature/StaticPagesTest.php
  modified:
    - routes/web.php
    - resources/views/layouts/app.blade.php
decisions:
  - "D-32 tarif indicatif driven by config('pricing.passage_starting') (env PRICING_PASSAGE_STARTING, default 80)"
  - "@@context / @@type Blade escaping required in @push('head') blocks — double-@ renders as single @"
  - "spatie/schema-org toScript() already wraps output in <script type='application/ld+json'> — no double-wrap"
  - "robots.txt route added in web.php for test-client compatibility (kernel routing, not static files)"
  - "HomeController + skeleton-home.blade.php kept on disk as historical artifacts — Plan 01-06 cutover cleanup"
  - "Contact view uses class_exists guard for Livewire ContactForm — deferred to Plan 04"
  - "testimonials.blade.php uses class_exists guard for Livewire GoogleReviews — deferred to Plan 04 (D-28)"
metrics:
  duration: "~4h (TDD RED + GREEN + bug fixes)"
  completed: "2026-05-28"
  tasks_completed: 2
  files_changed: 34
---

# Phase 01 Plan 03: Vitrine Pages 1:1 + SEO Summary

**One-liner:** Full vitrine Blade transposition from mockup with sitemap.xml, LocalBusiness Plumber JSON-LD (Spatie), OG meta, and 4 post-benchmark differentiators (D-32 tarif, D-33 diagnostic CTA, D-34 urgence eau verte, D-35 engagements).

## TDD Gate Compliance

- RED commit: `064a1e1` — `test(01-03): RED — vitrine pages + SEO + static pages` (29 failing tests)
- GREEN commit: `0f42627` — `feat(01-03): GREEN — vitrine 1:1 transposition + sitemap + LocalBusiness JSON-LD` (29 passing + 6 SkeletonSmokeTest = 35 total)

Both RED and GREEN gate commits exist. TDD cycle complete.

## Vitrine Routes

| Route | Name | Controller Method |
|-------|------|-------------------|
| `GET /` | `home` | `VitrineController::home` |
| `GET /services` | `services` | `VitrineController::services` |
| `GET /services/eau-verte-urgence` | `services.eau-verte-urgence` | `VitrineController::eauVerteUrgence` |
| `GET /realisations` | `realisations` | `VitrineController::realisations` |
| `GET /contact` | `contact` | `VitrineController::contact` |
| `GET /mentions-legales` | `legal.mentions` | `VitrineController::mentionsLegales` |
| `GET /cgv` | `legal.cgv` | `VitrineController::cgv` |
| `GET /confidentialite` | `legal.confidentialite` | `VitrineController::confidentialite` |
| `GET /sitemap.xml` | `sitemap` | `SitemapController::index` |
| `GET /robots.txt` | `robots` | closure (web.php) |

## Walking Skeleton Home Stub Replacement

- **Before:** `GET /` → `HomeController::index` → `skeleton-home.blade.php`
- **After:** `GET /` → `VitrineController::home` (DI-injected `LocalBusinessSchema`) → `vitrine/home.blade.php`
- `HomeController.php` and `skeleton-home.blade.php` kept on disk as historical artifacts (no routes pointing to them). Plan 01-06 cutover cleanup will remove them.

## Home Page Section Order (UI-SPEC)

1. Hero (`partials/hero`) — id="hero", photo-grade bg, D-32 tarif, D-33 Diagnostic gratuit CTA
2. Services grid (`partials/services-grid`) — id="services"
3. Urgence eau verte (`partials/urgence-eau-verte`) — D-34, links to `services.eau-verte-urgence`
4. How it works (`partials/how-it-works`)
5. Hospitality (`partials/hospitality`) — id="hospitality", Devenir partenaire CTA
6. Realisations grid (`partials/realisations-grid`) — id="realisations"
7. Pierre bio (`partials/pierre`) — id="pierre", Dlo c'est l'eau. Azur c'est sa couleur.
8. Espace client teaser (`partials/espace-client-teaser`)
9. Testimonials (`partials/testimonials`) — D-28 guard (empty until Plan 04)
10. Engagements (`partials/engagements`) — D-35, Rapport photo à chaque passage
11. Final CTA (`partials/final-cta`) — id="contact"

## LocalBusiness JSON-LD Output

The `LocalBusinessSchema::build()` via `spatie/schema-org` produces a Plumber schema with:

```json
{
  "@context": "https://schema.org",
  "@type": "Plumber",
  "name": "Dlo Azur Piscines",
  "telephone": "+596696940054",
  "email": "contact@dloazurpiscines.fr",
  "url": "https://dloazurpiscines.fr",
  "logo": "https://dloazurpiscines.fr/assets/brand/logo.svg",
  "image": "https://dloazurpiscines.fr/assets/brand/photos/hero-pierre-piscine.jpg",
  "priceRange": "€€",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Martinique",
    "addressLocality": "Le Lamentin",
    "addressRegion": "Martinique",
    "postalCode": "97232",
    "addressCountry": "FR"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 14.6037,
    "longitude": -61.0594
  },
  "areaServed": [
    {"@type": "City", "name": "Fort-de-France"},
    {"@type": "City", "name": "Le Lamentin"},
    {"@type": "City", "name": "Le Robert"},
    {"@type": "City", "name": "Schoelcher"},
    {"@type": "AdministrativeArea", "name": "Martinique"}
  ],
  "openingHoursSpecification": [
    {"@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"], "opens": "08:00", "closes": "17:00"},
    {"@type": "OpeningHoursSpecification", "dayOfWeek": ["Saturday"], "opens": "09:00", "closes": "12:00"}
  ]
}
```

## Contact View Guard

`resources/views/vitrine/contact.blade.php` uses:

```blade
@if(class_exists(\App\Livewire\ContactForm::class))
    <livewire:contact-form />
@else
    <p class="text-ink-600 italic">Formulaire en cours de chargement…</p>
@endif
```

When Plan 04 registers the Livewire `ContactForm` component, this guard activates automatically. Until then, the fallback placeholder is rendered.

## D-32 Tarif Indicatif Config

`config/pricing.php`:
- Key: `pricing.passage_starting` (env `PRICING_PASSAGE_STARTING`, default `80`)
- Key: `pricing.currency` (`€`)
- Key: `pricing.disclaimer` (`selon volume, accès et traitement`)

Hero renders: `À partir de 80€/passage — selon volume, accès et traitement`

## Known Stubs

All photo paths use placeholder filenames under `assets/brand/photos/`. These images do not exist yet — Pierre (the operator) must provide real photos before the Plan 01-06 cutover.

| File | Photo path | Description |
|------|-----------|-------------|
| `partials/hero.blade.php:7` | `assets/brand/photos/hero-pierre-piscine.jpg` | Hero background (Pierre working) |
| `partials/services-grid.blade.php:12` | `assets/brand/photos/entretien-dos-logo.jpg` | Services section feature photo |
| `partials/hospitality.blade.php:30` | `assets/brand/photos/villa-hospitality.jpg` | Hospitality section villa photo |
| `partials/pierre.blade.php:6` | `assets/brand/photos/pierre-portrait.jpg` | Pierre portrait |
| `partials/pierre.blade.php:23` | `assets/brand/photos/entretien-dos-logo.jpg` | Pierre action photo |
| `partials/realisations-grid.blade.php:15` | `assets/brand/photos/avant-apres.jpg` | Before/after réalisation |
| `partials/realisations-grid.blade.php:27-48` | `piscine-propre.jpg`, `piscine-hors-sol.jpg`, `montage-hors-sol.jpg`, `balai-detail.jpg` | Réalisations gallery |
| `partials/espace-client-teaser.blade.php:39,43` | `piscine-propre.jpg`, `balai-detail.jpg` | Espace client preview photos |
| `partials/urgence-eau-verte.blade.php:28,35` | `avant-apres.jpg`, `piscine-propre.jpg` | Urgence eau verte gallery |
| `vitrine/services/eau-verte-urgence.blade.php:63,73` | `avant-apres.jpg`, `piscine-propre.jpg` | Dedicated page gallery |
| `vitrine/realisations.blade.php:14-49` | `piscine-propre.jpg`, `balai-detail.jpg`, `montage-hors-sol.jpg`, `piscine-hors-sol.jpg`, `avant-apres.jpg`, `entretien-dos-logo.jpg` | Full realisations page gallery |

These stubs do NOT prevent the plan's goal — all pages render correctly (browser shows broken image icons until assets are provided). This is an expected pre-launch state.

**Other content stubs:**
- `vitrine/mentions-legales.blade.php:17` — Legal content body marked `TODO: Pierre à compléter avant cutover`
- `vitrine/cgv.blade.php:22` — TVA rate footnote marked `TODO: confirmer taux TVA avec comptable local avant premières factures`

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Vite manifest missing in test environment**
- **Found during:** Task 2 (GREEN) — all view-rendering tests returned HTTP 500
- **Issue:** `npm install` had not been run in the worktree; no `public/build/manifest.json`
- **Fix:** Ran `npm install && npm run build` to generate the manifest
- **Files modified:** `public/build/` (generated, gitignored)
- **Commit:** inline fix during 0f42627

**2. [Rule 1 - Bug] Blade parses @context / @type as directives in @push('head')**
- **Found during:** Task 2 (GREEN) — JSON-LD inside `@push('head')` caused ParseError
- **Issue:** Blade interprets `@context` and `@type` as undefined directives, crashing template compilation
- **Fix:** Escaped as `@@context` / `@@type` — Blade renders `@@x` → `@x` in output
- **Files modified:** `resources/views/vitrine/services/eau-verte-urgence.blade.php`
- **Commit:** inline fix during 0f42627

**3. [Rule 1 - Bug] robots.txt 404 in test environment**
- **Found during:** Task 2 (GREEN) — `$this->get('/robots.txt')` returned 404
- **Issue:** Laravel test client routes through the kernel; `public/robots.txt` is a static file not served by the kernel
- **Fix:** Added explicit route in `routes/web.php` that reads `public_path('robots.txt')`
- **Files modified:** `routes/web.php`
- **Commit:** inline fix during 0f42627

**4. [Rule 1 - Bug] Pest toContain() does not accept failure message parameter**
- **Found during:** Task 1 (RED) — test syntax error on `->toContain(needle, message)`
- **Issue:** Pest's `toContain()` treats the second argument as another search string, not a message
- **Fix:** Removed all failure messages from `toContain()` calls in SeoTest.php
- **Files modified:** `tests/Feature/SeoTest.php`
- **Commit:** 064a1e1

**5. [Rule 1 - Bug] Blade escapes apostrophes to &#039; in attribute values**
- **Found during:** Task 2 (GREEN) — meta description assertion failed
- **Issue:** `{{ $description }}` inside HTML attributes auto-escapes `'` → `&#039;`; exact string match failed
- **Fix:** Simplified meta description test to partial match: `assertSee('content="Entretien, d', false)` + separate `assertSee('eau de votre piscine en Martinique', false)`
- **Files modified:** `tests/Feature/HomePageTest.php`
- **Commit:** 064a1e1

**6. [Rule 2 - Missing] Contact fallback text for pre-Plan-04 state**
- **Found during:** Task 2 (GREEN) — StaticPagesTest assumed `livewire:contact-form` would be rendered; ContactForm class doesn't exist yet
- **Issue:** Test was too strict; contact page guard correctly hides tag when class unregistered
- **Fix:** Updated test to check for EITHER livewire tag OR fallback text `Formulaire en cours de chargement`; added fallback `<p>` to contact.blade.php
- **Files modified:** `tests/Feature/StaticPagesTest.php`, `resources/views/vitrine/contact.blade.php`
- **Commit:** 0f42627

## Self-Check: PASSED

| Artifact | Status |
|----------|--------|
| `.planning/phases/01-vitrine-fondations/01-03-SUMMARY.md` | FOUND |
| `routes/vitrine.php` | FOUND |
| `app/Http/Controllers/VitrineController.php` | FOUND |
| `app/Http/Controllers/SitemapController.php` | FOUND |
| `app/Support/SchemaOrg/LocalBusinessSchema.php` | FOUND |
| `config/pricing.php` | FOUND |
| `resources/views/vitrine/home.blade.php` | FOUND |
| `resources/views/vitrine/services/eau-verte-urgence.blade.php` | FOUND |
| `tests/Feature/HomePageTest.php` | FOUND |
| `tests/Feature/SeoTest.php` | FOUND |
| `tests/Feature/StaticPagesTest.php` | FOUND |
| RED commit `064a1e1` | FOUND |
| GREEN commit `0f42627` | FOUND |
| 35 tests passing | VERIFIED |
