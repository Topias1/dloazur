# Phase 1: Vitrine & Fondations — Research

**Researched:** 2026-05-28
**Domain:** Greenfield Laravel monolith — vitrine SEO Martinique + back-office shell + schéma DB complet + déploiement Laravel Cloud EU/Frankfurt + Scaleway S3 (Paris)
**Confidence:** HIGH (stack, libraries, déploiement) · MEDIUM (driver mail choice, ROI cache, Tailwind 4 path) · LOW (none)

---

## Summary

Walking Skeleton end-to-end pour une vitrine pisciniste : `composer create-project` sur un repo qui contient déjà `.planning/`, `mockups/v1/`, `PRODUCT.md`, `DESIGN.md`, `CLAUDE.md` (à préserver) ; Laravel Cloud EU/Frankfurt avec Postgres managé (Neon-backed, scale-to-zero hibernation, cold-wake « quelques centaines de ms ») et Scaleway Object Storage Paris (S3-compatible) ; Fortify headless branché sur des vues Blade stylées avec le design system OKLCH du mockup ; migrations complètes pour toutes les tables du roadmap (clients/piscines/passages/photos_meta/produits/contrats/factures/signatures/diagnostics) avec `client_uuid`/`odoo_id`/`signature_path` dès le départ même si les tables sont vides ; blog markdown-in-repo via `spatie/laravel-markdown` ; sitemap via `spatie/laravel-sitemap` ; LocalBusiness JSON-LD via `spatie/schema-org` (sous-type `Plumber`) ; formulaire contact Livewire avec `spatie/laravel-honeypot` + `danharrin/livewire-rate-limiting` (pas de captcha, conformément D-14) ; Pest 4 + GitHub Actions.

**Trois corrections importantes** par rapport au CONTEXT/UI-SPEC : (1) **Tailwind v4 supprime `tailwind.config.js`** — les tokens vont dans `resources/css/app.css` via `@theme`, pas dans un fichier JS séparé ; (2) **Laravel 11 est EOL le 12 mars 2026** (déjà passé) — un greenfield démarré en mai 2026 devrait choisir Laravel 12 (sécurité jusqu'en 2027-02) ou Laravel 13 (actuel, sorti 2026-03-17), à arbitrer avec l'utilisateur ; (3) **Mailgun recommandé** pour D-15 (EU data residency explicite + plan Flex 1€/1000 emails sans minimum mensuel, vs Postmark sans option EU documentée, vs SES sans support live).

**Primary recommendation:** Démarrer Phase 1 sur **Laravel 12.x** (compromis entre la stack lock D-01 « Laravel 11 » et la réalité EOL — `laravel/fortify ^1.37` supporte 11/12/13, idem `cesargb/laravel-magiclink ^2.27`, `spatie/laravel-medialibrary ^11.22`, `spatie/laravel-pdf ^2.11`, `laravel/cashier ^16.5`, `pestphp/pest ^4.7`) avec PHP 8.3+, **Tailwind v4 CSS-first** (`@theme` dans `app.css`), **Fortify headless** (vues Blade `resources/views/auth/login.blade.php` stylées par le mockup), **migrations idempotentes complètes en Wave 0**, **vitrine en Phase 2 du plan**, **back-office shell + déploiement en Phase 3 du plan**.

---

## User Constraints (from CONTEXT.md)

### Locked Decisions
**Stack & infra (carried forward, locked) :**
- **D-01:** Stack Laravel 11 + Livewire 3 + Alpine.js 3 + Tailwind 4 + PostgreSQL 16 (PROJECT.md Key Decisions, CLAUDE.md)
- **D-02:** Hébergement Laravel Cloud EU/Francfort + Postgres managé ; photos sur Scaleway Object Storage (Paris)
- **D-03:** Auth pro via `laravel/fortify` (headless, branché sur vues Livewire) — pas Breeze, pas Jetstream
- **D-04:** Pest 4 comme test runner (default Laravel 11 + meilleure DX)
- **D-05:** Tailwind 4 — transposer `mockups/v1/theme.js` (tokens OKLCH) et `mockups/v1/app.css` dans `tailwind.config.js` + classes utilitaires custom
- **D-06:** Mapping 1:1 du design depuis `mockups/v1/vitrine.html` (mockup verrouillé, design system impeccable)

**Schéma complet dès Phase 1 :**
- **D-07:** Toutes les migrations métier déployées en Phase 1, même celles consommées plus tard (clients, piscines, passages, photos_meta, produits, contrats, factures, signatures, diagnostics)
- **D-08:** Colonnes critiques à présentes dès le 1er deploy : `client_uuid` UUID v4 unique (idempotence offline Phase 2), `odoo_id` nullable bigint (Phase 3), `signature_path` nullable string (Phase 3)
- **D-09:** Pas de seed data métier en production. Seeds dev uniquement (factory utilisateur pro + ~3 clients démo) pour tests local/CI.

**Blog (SITE-04) — Markdown-in-repo :**
- **D-10:** Articles de blog en `.md` dans `resources/content/blog/` avec front matter YAML (title, date, excerpt, slug). Parser via `spatie/laravel-markdown`. Git push pour publier. Volume cible ~3-6 articles/an ne justifie pas un CRUD admin.
- **D-11:** Liste chronologique simple, pas de système de tags/catégories à v1 (déferrable si volume augmente)
- **D-12:** Routes : `/blog` (index) + `/blog/{slug}` (article). SEO : meta + OG + structured data Article JSON-LD par post.

**Contact (SITE-05) — Email + honeypot :**
- **D-13:** Form Livewire `/contact` → `Mail::to('contact@dloazurpiscines.com')` via Laravel Mail. Pas de persistance DB des soumissions à v1.
- **D-14:** Anti-spam : honeypot field caché + rate-limit Laravel `RateLimiter` (5/min par IP). **Pas de captcha** (friction UX inutile, perte de leads).
- **D-15:** Driver mail : Mailgun ou Postmark (à confirmer par recherche-phase — choisir le moins cher avec bon délivery EU). Logo expéditeur `contact@dloazurpiscines.com`.
- **D-16:** Fallback WhatsApp visible sous le form (bouton « ou écrire sur WhatsApp »). WhatsApp reste le CTA principal partout ailleurs.

**Back-office post-login (AUTH-01) — Shell pré-câblé :**
- **D-17:** Route `/admin` (ou `/dashboard`) post-login. Layout admin réutilisable (sidebar gauche + topbar + slot main) construit en Blade + Livewire layouts.
- **D-18:** Sidebar nav : Tableau de bord (actif), Clients (grisé), Passages (grisé), Factures (grisé), Catalogue (grisé). Les items grisés portent une mention « bientôt » + sont non-cliquables. Phase 2 active les routes Clients/Passages.
- **D-19:** Page « Tableau de bord » stub : carte de bienvenue avec prénom de Pierre + 3 placeholders de stats affichant `—` pour l'instant. Sert de canevas réutilisé par Phase 2.
- **D-20:** Logout via Fortify default route.

**Cutover Zyro → Laravel Cloud (SITE-07) :**
- **D-21:** Déploiement Phase 1 sur subdomain de staging d'abord. Préférence : `preprod.dloazurpiscines.com` ou `dloazur.laravel.cloud`.
- **D-22:** Validation avant cutover : Lighthouse mobile ≥ 90 perf/SEO/a11y, sitemap.xml accessible, structured data testé via Schema.org validator, OG preview vérifié, formulaire contact + WhatsApp testés en réel.
- **D-23:** TTL DNS baissé à 300s ~24h avant le switch. Switch = bascule CNAME `dloazurpiscines.com` → Laravel Cloud. Validation manuelle post-switch.
- **D-24:** Inventaire des URLs Zyro indexées avant le switch. Si > 3 URLs uniques : redirect map 301 dans `routes/web.php` ou middleware. Si site Zyro plat (juste `/`) : aucun redirect.
- **D-25:** Phase 1 livre la vitrine **validée sur staging**. Le DNS switch est un acte opérationnel déclenché par Pierre.

**SEO local Martinique (SITE-07) :**
- **D-26:** Données structurées JSON-LD `LocalBusiness` (sous-type approprié : `Plumber`/`HomeAndConstructionBusiness`) avec : `name`, `image`, `address` (Martinique), `geo`, `telephone`, `priceRange`, `openingHoursSpecification`, `areaServed` (Martinique + communes).
- **D-27:** Sitemap XML généré dynamiquement (route Laravel) couvrant pages publiques + tous les articles de blog.
- **D-28:** Pas de Google Reviews widget intégré. Lien externe vers fiche Google Business à la place.

**CI/Deploy :**
- **D-29:** GitHub Actions `.github/workflows/tests.yml` : `composer install` + `npm ci` + `npm run build` + `./vendor/bin/pest --ci`. PHP 8.3.
- **D-30:** Déploiement Laravel Cloud : auto-deploy sur push `main` après CI vert. Migrations exécutées automatiquement au deploy (`php artisan migrate --force`).
- **D-31:** Branch strategy : work sur `feature/*` → PR vers `main` → merge déclenche deploy.

### Claude's Discretion
- Choix précis du driver mail (Mailgun vs Postmark vs SES) — recherche-phase évaluera prix/délivery EU et tranchera.
- Structure exacte des Blade layouts (`layouts/app.blade.php` pour vitrine, `layouts/admin.blade.php` pour back-office) et des composants Livewire vs Blade components purs (sans état).
- Naming Postgres : convention Laravel par défaut (`snake_case`, pluriel pour tables).
- Mise en cache des pages publiques (full-page cache via Laravel response cache) ou pas — recherche-phase évaluera ROI vs scale-to-zero Laravel Cloud.

### Deferred Ideas (OUT OF SCOPE)
- Tags/catégories blog (D-11) — réévaluer si volume > 10 articles
- DB-backed admin pour blog (D-10 alternative)
- Persistance DB des soumissions contact (D-13)
- Google Reviews widget embed (D-28)
- Préchargement / cache full-page vitrine
- 2FA pour auth pro (Fortify le supporte, mais Pierre seul utilisateur)
- Email verification sur signup (Pierre pré-créé via seeder)

---

## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| SITE-01 | Visiteur voit page d'accueil (hero, services, réalisations, confiance, CTA WhatsApp) | UI-SPEC mapping 1:1 vitrine.html → Blade ; vitrine Livewire en routes web |
| SITE-02 | Visiteur consulte page services détaillée | Pages Blade statiques `/services`, mêmes layouts + tokens design |
| SITE-03 | Visiteur parcourt galerie de réalisations | Page `/realisations`, photo grid (UI-SPEC §Réalisations grid), photos sourcées Phase 1 |
| SITE-04 | Visiteur lit le blog | `spatie/laravel-markdown` + `spatie/yaml-front-matter`, fichiers `resources/content/blog/*.md`, prose styles |
| SITE-05 | Visiteur envoie message via formulaire contact | Livewire form + `spatie/laravel-honeypot` (Livewire integration) + `danharrin/livewire-rate-limiting` ; Mailable via Mailgun |
| SITE-06 | Contact WhatsApp en un tap | CTA WhatsApp `https://wa.me/596696940054` partout (header, FAB mobile, footer, CTA section) |
| SITE-07 | Pages publiques optimisées SEO local Martinique | `spatie/laravel-sitemap` (route dynamique) + `spatie/schema-org` (LocalBusiness JSON-LD sous-type `Plumber`) + meta/OG/canonical |
| AUTH-01 | Pro se connecte avec email + mot de passe | `laravel/fortify` headless, vues Blade stylées, seeder Pierre (1 user), shell admin post-login |

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Vitrine HTML rendering (home, services, gallery, contact, legal pages) | Frontend Server (Laravel + Blade) | CDN/Static (Laravel Cloud edge cache) | Pages SEO indexables, server-rendered pour first contentful paint + canonical/OG dans HTML initial |
| Blog markdown rendering | Frontend Server (Laravel + spatie/laravel-markdown) | — | Pas de DB, fichiers `.md` lus à la volée (cache view safe), `prose` Tailwind ; Phase 4+ pourrait migrer en DB |
| Contact form submission | API/Backend (Livewire + Mail) | — | Validation server-side, rate-limit IP, envoi via Mailgun ; pas de persistance Phase 1 |
| Authentication (login pro) | API/Backend (Fortify) | Frontend Server (Blade login view) | Fortify gère POST `/login`, session, mot de passe ; Blade affiche les vues |
| Admin shell (post-login) | Frontend Server (Blade layout + Livewire stubs) | — | Pages connectées, OK pour Livewire ; pas d'offline Phase 1 |
| Photo storage configuration | Database/Storage (Scaleway S3 disk wiring) | — | Disk `s3` configuré dès Phase 1 même si non utilisé ; spatie/medialibrary installé pour Phase 2 |
| Database schema (toutes tables) | Database/Storage (PostgreSQL Neon serverless) | — | Migrations complètes dès Phase 1 (D-07), tables vides en attendant Phase 2+ |
| Sitemap.xml + JSON-LD | Frontend Server (Laravel routes, response cached) | CDN/Static (HTTP cache headers) | Routes dynamiques `/sitemap.xml`, rendu serveur ; LocalBusiness inline dans `<head>` home |
| Static assets (photos, fonts, CSS, JS) | CDN/Static (Laravel Cloud `/public/build/` + Vite manifest) | — | Vite build versionné, Laravel Cloud sert avec hash + cache-control |
| Deployment pipeline | API/Backend (Laravel Cloud build/deploy commands) | CI (GitHub Actions tests) | GH Actions = gate de qualité, Laravel Cloud = build + deploy serverless |

---

## Standard Stack

### Core (verrouillés via CLAUDE.md + carried-forward research)

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel | 12.x (recommandé — voir Pitfall #1 ci-dessous) | Backend framework | Laravel 11 EOL 2026-03-12 ; Laravel 12 supporté jusqu'à 2027-02 (sécurité). Tous les packages cibles supportent 11/12/13. `[CITED: laravel.com/docs/13.x/releases]` |
| Livewire | 3.x | Server-rendered reactive UI | Stack lock D-01. Vues admin + contact form. **PAS** sur saisie passage (Phase 2 Alpine). `[VERIFIED: livewire.laravel.com]` |
| Alpine.js | 3.x | Micro-interactivité (nav mobile, segmented toggle auth) | Phase 1 usage limité aux interactions sans état (menu burger, toggle Pro/Client visuel). PWA island = Phase 2. `[CITED: alpinejs.dev]` |
| Tailwind CSS | 4.x | Utility CSS, design tokens via `@theme` CSS-first | **Tailwind v4 supprime `tailwind.config.js`** — tokens en CSS directement (@theme dans `app.css`). `[VERIFIED: tailwindcss.com/blog/tailwindcss-v4]` |
| PostgreSQL | Neon serverless (Laravel Cloud managed) | Database | Autoscaling .25-4 compute units, pgbouncer inclus (10k connections), cold-wake « quelques centaines de ms ». **Version PostgreSQL non documentée publiquement** — Neon utilise PG 15+ en pratique. `[CITED: cloud.laravel.com/docs/resources/databases/postgres]` |
| Laravel Cloud | EU Central (Frankfurt) | Hébergement | EU Central (Frankfurt) confirmé disponible. Free tier inclut $5 crédit / 14 jours. Starter plan : pay-per-use, scale-to-zero hibernation. `[CITED: cloud.laravel.com/pricing]` |
| Scaleway Object Storage | fr-par (Paris) | Médias photos (Phase 2 usage, configuré Phase 1) | S3-compatible, EU GDPR, ~0.01€/Go. Endpoint `https://s3.fr-par.scw.cloud`, `use_path_style_endpoint=true`. `[VERIFIED: scaleway.com/docs]` |
| PHP | 8.3 | Runtime (CI + Laravel Cloud) | Laravel 12 supporte 8.2-8.5, Laravel 13 requires 8.3+. PHP 8.5 dispo local (8.5.6) mais 8.3 safer pour CI lockstep avec Laravel Cloud. `[VERIFIED: cloud.laravel.com — supports 8.2 through 8.5]` |

### Supporting (Phase 1 install)

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| `laravel/fortify` | ^1.37 | Auth backend headless (email+password) | AUTH-01 — vues Fortify publiées + stylées avec mockup auth.html. 2FA/passkeys déjà disponibles si activés plus tard. `[VERIFIED: Packagist, latest v1.37.2 — laravel.com/docs/13.x/fortify]` |
| `spatie/laravel-markdown` | ^2.8 | Markdown rendering (blog) | SITE-04 — composant `<x-markdown>` Blade, Shiki highlighting bundled, support YAML front-matter via dépendance. `[VERIFIED: Packagist v2.8.0, Slopcheck OK]` |
| `spatie/laravel-sitemap` | ^8.1 | Sitemap XML générer | SITE-07 D-27 — `Sitemap::create()->add(...)` ou crawl ; pour MVP, build manuel via route Laravel `/sitemap.xml` qui liste vitrine + posts blog. `[VERIFIED: Packagist v8.1.0]` |
| `spatie/schema-org` | ^4.0 | LocalBusiness JSON-LD typesafe | SITE-07 D-26 — sous-type `Plumber` (HomeAndConstructionBusiness), `Schema::plumber()->name(...)->address(...)->geo(...)`. Évite les erreurs JSON-LD manuelles. `[VERIFIED: Packagist v4.0.2, Slopcheck OK]` |
| `spatie/laravel-honeypot` | ^4.7 | Anti-spam form contact | SITE-05 D-14 — Livewire integration via trait `UsesSpamProtection` + `<x-honeypot livewire-model="extraFields" />` + `protectAgainstSpam()` dans submit. Pas de captcha. `[VERIFIED: Packagist v4.7.1, Slopcheck OK]` |
| `danharrin/livewire-rate-limiting` | ^2.2 | Rate-limit Livewire actions | SITE-05 D-14 — trait `WithRateLimiting`, `$this->rateLimit(5, 60)` dans submit (5/min). Complément du honeypot. `[VERIFIED: Packagist v2.2.0, Slopcheck OK]` |
| `dompdf/dompdf` | ^3.1 | PDF driver (installé Phase 1, pas utilisé) | Phase 3 dependency — installé tôt pour valider que la stack PDF fonctionne sur Laravel Cloud serverless. `[VERIFIED: Packagist v3.1.5, Slopcheck OK]` — Phase 3 ajoutera `spatie/laravel-pdf` |
| `pestphp/pest` | ^4.7 | Test runner | D-04 — default Laravel 11+, Pest 4 supporte browser tests Playwright (utile Phase 2). `[VERIFIED: Packagist v4.7.0, Slopcheck OK]` |
| `pestphp/pest-plugin-laravel` | ^4.1 | Helpers Laravel pour Pest | Required pour `actingAs()`, `assertDatabaseHas()`, etc. `[VERIFIED: Packagist v4.1.0, Slopcheck OK]` |

### Supporting (Phase 1 install — npm)

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| `@tailwindcss/vite` | ^4 | Plugin Tailwind v4 pour Vite | Remplace l'ancien `tailwindcss` PostCSS. Setup CSS-first. `[CITED: tailwindcss.com/docs/guides/laravel]` |
| `tailwindcss` | ^4 | Tailwind core | Avec plugin vite + import dans `app.css`. `[CITED: tailwindcss.com/blog/tailwindcss-v4]` |
| `@tailwindcss/typography` | ^0.5 | `prose` styles pour blog (D-10 SITE-04) | Articles markdown ont besoin de `<article class="prose prose-lg">` pour rendu lisible. `[CITED: tailwindcss.com — official plugin]` |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| `spatie/laravel-honeypot` | Cloudflare Turnstile (invisible CAPTCHA) | Turnstile demande JS Cloudflare, viole D-14 « pas de captcha », ajoute dépendance externe ; honeypot 100% serveur natif |
| `spatie/laravel-sitemap` | Sitemap maison (route Laravel + Blade XML) | Custom route plus simple à déboguer mais perd: récursion crawl auto, conventions `<priority>` `<changefreq>`. Acceptable pour 10-15 URLs statiques. **Recommandation: utiliser le package, c'est 50 lignes de code vs 200.** |
| `spatie/schema-org` | JSON-LD inline manuel | Inline = risque de typo/syntaxe ; package = typesafe + validation `@context`/`@type`. **Utiliser le package.** |
| Mailgun (recommandé D-15) | Postmark | Postmark = meilleure délivrabilité réputée mais **pas d'EU data residency documentée** (US-only data processing) — friction RGPD. Mailgun = EU data residency explicite + plan Flex 1€/1000 emails sans minimum. `[CITED: mailgun.com/compare, deliciousbrains.com/ses-vs-mailgun-vs-sendgrid]` |
| Mailgun (recommandé) | Amazon SES | SES = $0.10/1000 emails (10x moins cher à l'échelle) mais **support inexistant en non-enterprise**, setup SPF/DKIM manuel, pas de UI lisible. Pour ~50 emails/mois (contact form solo), prix négligeable et la simplicité Mailgun gagne. |
| `laravel/fortify` | Laravel Breeze | Breeze génère du scaffold Blade/views couplé qu'on devrait ensuite remplacer pour matcher le design system ; Fortify = headless, vues 100% custom. D-03 acte. |
| `laravel/fortify` | Laravel Jetstream | Jetstream = équipes, profil, 2FA UI — overkill pour Pierre solo. D-03 acte. |

### Verified Versions (Packagist, 2026-05-28)

| Package | Latest | PHP | Laravel | Confidence |
|---------|--------|-----|---------|-----------|
| laravel/laravel (skeleton) | v13.8.0 | 8.3+ | 13.x | HIGH |
| laravel/fortify | v1.37.2 | 8.2+ | 11/12/13 | HIGH |
| livewire/livewire | 3.x (Context7) | 8.2+ | 11/12/13 | HIGH |
| spatie/laravel-markdown | 2.8.0 | 8.2+ | 11/12/13 | HIGH |
| spatie/laravel-sitemap | 8.1.0 | 8.2+ | 11/12/13 | HIGH |
| spatie/schema-org | 4.0.2 | 8.2+ | n/a (PHP library) | HIGH |
| spatie/laravel-honeypot | 4.7.1 | 8.2+ | 11/12/13 | HIGH |
| danharrin/livewire-rate-limiting | v2.2.0 | 8.2+ | 11/12/13 | HIGH |
| dompdf/dompdf | v3.1.5 | 8.0+ | n/a (PHP library) | HIGH |
| pestphp/pest | v4.7.0 | 8.2+ | n/a (test framework) | HIGH |
| pestphp/pest-plugin-laravel | v4.1.0 | 8.2+ | 11/12/13 | HIGH |
| tailwindcss (npm) | 4.x | n/a | n/a | HIGH `[CITED: tailwindcss.com]` |
| @tailwindcss/vite (npm) | 4.x | n/a | n/a | HIGH |

---

## Package Legitimacy Audit

> Slopcheck 0.6.1 ran successfully against Packagist registry on all recommended packages.

| Package | Registry | Age | Source Repo | slopcheck | Disposition |
|---------|----------|-----|-------------|-----------|-------------|
| laravel/fortify | Packagist | ~6 yrs (v1.0.0 2020-12) | github.com/laravel/fortify | [OK] | Approved (official Laravel) |
| spatie/laravel-markdown | Packagist | ~5 yrs (v0.0.1 2021) | github.com/spatie/laravel-markdown | [OK] | Approved |
| spatie/laravel-sitemap | Packagist | ~9 yrs (v1.0.0 2017) | github.com/spatie/laravel-sitemap | [OK] | Approved |
| spatie/schema-org | Packagist | ~8 yrs (v1.0.0 2018) | github.com/spatie/schema-org | [OK] | Approved |
| spatie/laravel-honeypot | Packagist | ~5 yrs (v1.0.0 2020) | github.com/spatie/laravel-honeypot | [OK] | Approved |
| danharrin/livewire-rate-limiting | Packagist | ~5 yrs (v0.1.0 2021) | github.com/danharrin/livewire-rate-limiting | [OK] | Approved (auteur Filament) |
| dompdf/dompdf | Packagist | ~14 yrs | github.com/dompdf/dompdf | [OK] | Approved |
| pestphp/pest | Packagist | ~5 yrs (v1.0.0 2021) | github.com/pestphp/pest | [OK] | Approved |
| pestphp/pest-plugin-laravel | Packagist | ~5 yrs | github.com/pestphp/pest-plugin-laravel | [OK] | Approved |

**Packages removed due to slopcheck [SLOP] verdict:** none
**Packages flagged as suspicious [SUS]:** none

**Forbidden packages (from CLAUDE.md — DO NOT install in Phase 1):**
- `edujugon/laradoo` — abandonné (Phase 3 only via obuchmann/odoo-jsonrpc)
- `obuchmann/laravel-odoo-api` — abandonné par l'auteur
- `ripcord` / XML-RPC PHP — déprécié Odoo 17+
- `barryvdh/laravel-dompdf` direct — utiliser `spatie/laravel-pdf` Phase 3
- Browsershot / `spatie/browsershot` — incompatible Laravel Cloud serverless
- `rahaug/laravel-magic-link` — abandonné depuis 2021
- `maize-tech/laravel-magic-login` — pas Phase 1 (Phase 2 utilise `cesargb/laravel-magiclink`)
- PHPUnit directement — utiliser Pest

---

## Architecture Patterns

### System Architecture Diagram (Phase 1 scope)

```
                          ┌─────────────────────┐
                          │   Visitor (browser) │
                          └──────────┬──────────┘
                                     │ HTTPS GET / POST
                                     ▼
            ┌────────────────────────────────────────────────┐
            │  Laravel Cloud (EU/Frankfurt, scale-to-zero)    │
            │  ┌──────────────────────────────────────────┐  │
            │  │  HTTP Router (routes/web.php)             │  │
            │  └────────────┬─────────────────────────────┘  │
            │               │                                 │
            │  ┌────────────┼─────────────────────────────┐   │
            │  │            ▼                              │   │
            │  │  ┌──────────────────┐ ┌────────────────┐ │   │
            │  │  │ Public routes    │ │ Auth routes    │ │   │
            │  │  │ /, /services,    │ │ /login         │ │   │
            │  │  │ /realisations,   │ │ (Fortify)      │ │   │
            │  │  │ /blog/{slug},    │ └────────┬───────┘ │   │
            │  │  │ /contact,        │          │         │   │
            │  │  │ /sitemap.xml     │          ▼ session │   │
            │  │  └────────┬─────────┘ ┌────────────────┐ │   │
            │  │           │           │ Admin routes   │ │   │
            │  │           │           │ /admin/*       │ │   │
            │  │           │           │ (auth middleware)│   │
            │  │           │           └────────┬───────┘ │   │
            │  │           │                    │         │   │
            │  │           ▼                    ▼         │   │
            │  │  ┌──────────────────────────────────┐    │   │
            │  │  │ Blade Views + Livewire Components │   │   │
            │  │  │  - layouts/app.blade.php (public) │   │   │
            │  │  │  - layouts/admin.blade.php        │   │   │
            │  │  │  - <x-markdown> (blog)            │   │   │
            │  │  │  - ContactForm (Livewire)         │   │   │
            │  │  │  - DashboardStub (Livewire)       │   │   │
            │  │  └────────┬─────────────────────────┘    │   │
            │  └───────────┼──────────────────────────────┘   │
            │              │                                   │
            │  ┌───────────▼───────────────────────────────┐   │
            │  │  Mail (Mailgun)         PostgreSQL (Neon) │   │
            │  │  contact form submit    users (1 seed)    │   │
            │  │                          all other tables  │   │
            │  │                          empty (D-07/D-08) │   │
            │  └───────────────────────────────────────────┘   │
            └────────────────────────┬─────────────────────────┘
                                     │ FILESYSTEM_DISK=s3 (config wired,
                                     │  no uploads Phase 1)
                                     ▼
                          ┌─────────────────────┐
                          │ Scaleway Object     │
                          │ Storage (Paris)     │
                          │ s3.fr-par.scw.cloud │
                          └─────────────────────┘

Static assets:
   resources/css/app.css      → Vite build → public/build/assets/*.css
   resources/js/app.js        → Vite build → public/build/assets/*.js
   resources/content/blog/*.md → read at request, parsed via spatie/laravel-markdown
   public/assets/brand/photos/*.jpg → served directly by Laravel Cloud
```

### Recommended Project Structure

```
dloazur/
├── .planning/                       ← preserved, not touched by composer create-project
├── mockups/v1/                      ← preserved, reference for UI transposition
├── PRODUCT.md, DESIGN.md            ← preserved
├── CLAUDE.md                        ← preserved
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── BlogController.php   ← /blog index + /blog/{slug}
│   │   ├── Middleware/              ← (Laravel 12 bootstrap pattern)
│   │   └── Requests/
│   ├── Livewire/
│   │   ├── ContactForm.php          ← SITE-05 honeypot + rate limit
│   │   ├── DashboardStub.php        ← AUTH-01 admin shell stub (D-19)
│   │   └── Auth/                    ← (optional Livewire login wrapper)
│   ├── Mail/
│   │   └── ContactMessage.php       ← Mailable for D-13
│   ├── Models/
│   │   ├── User.php                 ← Pierre, seeded
│   │   ├── Client.php               ← table créée, vide Phase 1
│   │   ├── Piscine.php
│   │   ├── Passage.php
│   │   ├── Produit.php
│   │   ├── Contrat.php
│   │   ├── Facture.php
│   │   ├── Signature.php
│   │   ├── PhotoMeta.php
│   │   └── Diagnostic.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php   ← URL::forceHttps() en prod
│   │   ├── FortifyServiceProvider.php ← loginView() → Blade view
│   │   └── RouteServiceProvider.php ← (Laravel 12 inlined dans bootstrap/app.php)
│   └── Support/
│       └── BlogRepository.php       ← parse markdown front matter + body
│
├── bootstrap/
│   └── app.php                      ← routing, middleware, exceptions (L11/12 pattern)
│
├── config/
│   ├── app.php                      ← timezone='America/Martinique' (UTC-4)
│   ├── auth.php                     ← (par défaut)
│   ├── filesystems.php              ← disk 's3' Scaleway wired
│   ├── fortify.php                  ← features minimum (Phase 1)
│   └── mail.php                     ← driver mailgun
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php          ← Pierre demo
│   │   └── ClientFactory.php        ← 3 clients démo
│   ├── migrations/
│   │   ├── 0001_..._create_users_table.php
│   │   ├── 0002_..._create_clients_table.php          ← client_uuid UUID v4 unique
│   │   ├── 0003_..._create_piscines_table.php
│   │   ├── 0004_..._create_passages_table.php        ← client_uuid + signature_path nullable
│   │   ├── 0005_..._create_photos_meta_table.php
│   │   ├── 0006_..._create_produits_table.php
│   │   ├── 0007_..._create_contrats_table.php
│   │   ├── 0008_..._create_factures_table.php        ← odoo_id nullable
│   │   ├── 0009_..._create_signatures_table.php
│   │   └── 0010_..._create_diagnostics_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php       ← env-gated: dev only
│       └── PierreSeeder.php         ← prod-safe: creates Pierre user idempotent
│
├── public/
│   ├── assets/brand/photos/         ← Pierre fournit (cf UI-SPEC §Photo Treatment)
│   ├── assets/brand/logo.svg
│   ├── favicon.ico                  ← from logo-icon.png
│   └── robots.txt                   ← Disallow: /admin
│
├── resources/
│   ├── css/
│   │   └── app.css                  ← @import "tailwindcss"; @theme {...} OKLCH tokens
│   ├── js/
│   │   └── app.js                   ← Alpine init, Livewire init (already loaded by livewire)
│   ├── content/
│   │   └── blog/
│   │       ├── 2026-05-bienvenue-dlo-azur.md
│   │       └── (futurs articles)
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php        ← vitrine layout (sand-50 bg, nav fixe, footer)
│       │   ├── admin.blade.php      ← admin shell (sidebar navy + topbar + slot)
│       │   ├── auth.blade.php       ← layout split brand panel + form panel
│       │   └── blog.blade.php       ← layout prose lg + sidebar minimal
│       ├── vitrine/
│       │   ├── home.blade.php       ← maps mockups/v1/vitrine.html 1:1
│       │   ├── services.blade.php
│       │   ├── realisations.blade.php
│       │   ├── contact.blade.php    ← contains <livewire:contact-form />
│       │   ├── mentions-legales.blade.php
│       │   ├── cgv.blade.php
│       │   └── confidentialite.blade.php
│       ├── blog/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── auth/
│       │   ├── login.blade.php      ← Fortify view, maps mockups/v1/auth.html
│       │   └── forgot-password.blade.php
│       ├── admin/
│       │   └── dashboard.blade.php  ← maps mockups/v1/dashboard.html stub
│       ├── components/
│       │   ├── icon/
│       │   │   ├── whatsapp.blade.php
│       │   │   ├── drop.blade.php
│       │   │   └── ... (extracted from vitrine.html SVGs)
│       │   └── button.blade.php     ← <x-button variant="primary|secondary|whatsapp">
│       └── livewire/
│           ├── contact-form.blade.php
│           └── dashboard-stub.blade.php
│
├── routes/
│   ├── web.php                      ← public + auth + admin
│   ├── console.php
│   └── (api.php not needed Phase 1)
│
├── tests/
│   ├── Feature/
│   │   ├── HomePageTest.php         ← SITE-01
│   │   ├── BlogTest.php             ← SITE-04 (index + show)
│   │   ├── ContactFormTest.php      ← SITE-05 (submit, honeypot, rate limit)
│   │   ├── SeoTest.php              ← SITE-07 (sitemap.xml, LocalBusiness JSON-LD, OG tags)
│   │   ├── AuthLoginTest.php        ← AUTH-01
│   │   └── MigrationsTest.php       ← D-07 D-08 (schema exists, columns present)
│   ├── Pest.php
│   └── TestCase.php
│
├── vite.config.js                   ← Laravel Vite + @tailwindcss/vite
├── composer.json
├── package.json
└── .github/workflows/
    └── tests.yml                    ← composer install + npm ci + npm build + pest --ci
```

### Pattern 1: Tailwind v4 CSS-First Tokens (CORRECTION D-05)

**What:** Tailwind v4 ne lit plus `tailwind.config.js`. Les tokens design vont directement dans `resources/css/app.css` via la directive `@theme`. Le mockup `mockups/v1/theme.js` est un format JS Tailwind v3 ; il faut **transposer** son contenu en CSS `@theme`.

**When to use:** Tout custom token (couleur, spacing, font, shadow, radius, breakpoint) doit aller dans `@theme`.

**Example:**
```css
/* resources/css/app.css */
@import "tailwindcss";
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Inter:wght@400;600&display=swap');

@source "../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php";
@source "../../storage/framework/views/*.php";
@source "../views/**/*.blade.php";
@source "../**/*.js";

@theme {
  /* Fonts — only 2 weights per family per UI-SPEC */
  --font-display: "Fredoka", system-ui, sans-serif;
  --font-sans: "Inter", system-ui, sans-serif;

  /* Spacing — custom tokens from UI-SPEC */
  --spacing-13: 3.25rem;  /* button height */
  --spacing-15: 3.75rem;  /* nav bar height */
  --spacing-18: 4.5rem;   /* section vertical padding */

  /* Screens */
  --breakpoint-xs: 400px;

  /* Colors — OKLCH from mockups/v1/theme.js */
  --color-azure-50:  oklch(0.965 0.022 256);
  --color-azure-100: oklch(0.930 0.045 256);
  --color-azure-500: oklch(0.615 0.211 256);   /* #0080ff brand */
  --color-azure-600: oklch(0.545 0.205 257);
  /* ... full palette transposed from theme.js ... */

  --color-navy-800: oklch(0.288 0.066 250);
  --color-navy-900: oklch(0.232 0.052 251);
  --color-navy-950: oklch(0.182 0.040 252);
  --color-sand-50:  oklch(0.987 0.005 85);
  --color-ink-700:  oklch(0.445 0.030 250);
  --color-success:  oklch(0.700 0.150 155);
  --color-danger:   oklch(0.620 0.210 25);

  /* Shadows */
  --shadow-xs: 0 1px 2px oklch(0.29 0.07 250 / 0.06);
  --shadow-sm: 0 1px 2px oklch(0.29 0.07 250 / 0.05), 0 4px 12px -6px oklch(0.29 0.07 250 / 0.10);
  /* ... */

  /* Border radius overrides */
  --radius-xl: 0.875rem;
  --radius-2xl: 1.25rem;
  --radius-3xl: 1.75rem;

  /* Max widths */
  --container-content: 75rem;

  /* Easing */
  --ease-out-quint: cubic-bezier(0.22, 1, 0.36, 1);
}

/* Custom utilities from mockups/v1/app.css */
@layer base {
  body { background: var(--color-sand-50); color: var(--color-ink-700); }
  :focus-visible { outline: 2px solid var(--color-azure-500); outline-offset: 2px; border-radius: 6px; }
  ::selection { background: var(--color-lagon-300); color: var(--color-navy-900); }
  .photo-grade { filter: saturate(1.05) contrast(1.02); }
  /* ... */
}

@layer components {
  .ripple { /* radial-gradient + repeating-linear-gradient — copy from app.css */ }
}
```

`[VERIFIED: tailwindcss.com/docs/guides/laravel, tailwindcss.com/blog/tailwindcss-v4 — @theme directive is the v4-native way to extend tokens]`

**Note pour D-05:** Le decision D-05 dit « transposer theme.js et app.css dans `tailwind.config.js` ». Le **résultat fonctionnel** est identique mais le **fichier cible change** : `resources/css/app.css` avec `@theme` au lieu de `tailwind.config.js`. À acter avec l'utilisateur ou simplement noter dans le plan.

### Pattern 2: Fortify Headless avec vues Blade custom

**What:** Fortify ne fournit aucune vue ; on doit explicitement bind chaque vue à sa route dans `FortifyServiceProvider`.

**When to use:** AUTH-01 — login pro avec design system custom.

**Example:**
```php
// app/Providers/FortifyServiceProvider.php
namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = mb_strtolower($request->input('email')).'|'.$request->ip();
            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
```
`[CITED: laravel.com/docs/13.x/fortify#authentication, github.com/livewire/livewire/discussions/9634]`

```php
// config/fortify.php — Phase 1 minimum features
'features' => [
    Features::resetPasswords(),
    // PAS de registerUsers — Pierre seeded
    // PAS de emailVerification — Pierre seeded
    // PAS de twoFactorAuthentication — Phase >1
],

'home' => '/admin',  // redirige après login
```

```blade
{{-- resources/views/auth/login.blade.php — transposes mockups/v1/auth.html --}}
<x-layouts.auth>
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <input type="email" name="email" required ... />
        <input type="password" name="password" required ... />
        <button type="submit" class="h-12 w-full rounded-xl bg-azure-500 text-white font-semibold">
            Se connecter
        </button>
    </form>
</x-layouts.auth>
```

### Pattern 3: Livewire Contact Form avec honeypot + rate limit

**What:** Form Livewire qui combine `spatie/laravel-honeypot` (anti-bot, timing) + `danharrin/livewire-rate-limiting` (IP-based, 5/min D-14).

**Example:**
```php
// app/Livewire/ContactForm.php
namespace App\Livewire;

use App\Mail\ContactMessage;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class ContactForm extends Component
{
    use WithRateLimiting;
    use UsesSpamProtection;

    public HoneypotData $extraFields;

    #[Validate('required|string|max:80')]
    public string $name = '';

    #[Validate('required|email|max:160')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $message = '';

    public bool $sent = false;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();
    }

    public function submit(): void
    {
        try {
            $this->rateLimit(5, 60);  // 5 submissions per 60s per IP
        } catch (\DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException $e) {
            $this->addError('throttle', __('Trop d\'essais. Attendez quelques minutes puis réessayez.'));
            return;
        }

        $this->protectAgainstSpam();  // throws if honeypot tripped
        $this->validate();

        Mail::to(config('contact.recipient'))
            ->send(new ContactMessage(
                name: $this->name,
                email: $this->email,
                phone: $this->phone,
                message: $this->message,
            ));

        $this->sent = true;
        $this->reset(['name', 'email', 'phone', 'message']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```

```blade
{{-- resources/views/livewire/contact-form.blade.php --}}
<div>
    @if ($sent)
        <div class="rounded-2xl bg-success/10 ring-1 ring-success/30 p-6">
            <h3 class="font-display text-xl text-ink-950">Message envoyé.</h3>
            <p class="text-sm text-ink-700 mt-1">Pierre vous répondra rapidement. ...</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            <x-honeypot livewire-model="extraFields" />
            {{-- name, email, phone, message inputs --}}
            <button type="submit" wire:loading.attr="disabled" class="h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold">
                <span wire:loading.remove>Envoyer mon message</span>
                <span wire:loading>Envoi en cours…</span>
            </button>
        </form>
    @endif
</div>
```
`[CITED: github.com/spatie/laravel-honeypot, github.com/danharrin/livewire-rate-limiting]`

### Pattern 4: Blog Markdown Repository

**What:** Articles `.md` lus du filesystem, parsés une fois, mis en cache.

**Example:**
```php
// app/Support/BlogRepository.php
namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class BlogRepository
{
    public function all(): Collection
    {
        return Cache::remember('blog.index', 60 * 60, function () {
            return collect(File::files(resource_path('content/blog')))
                ->map(fn ($file) => $this->parse($file->getPathname()))
                ->sortByDesc('date')
                ->values();
        });
    }

    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    private function parse(string $path): array
    {
        $doc = YamlFrontMatter::parseFile($path);
        return [
            'title'   => $doc->matter('title'),
            'slug'    => $doc->matter('slug') ?? basename($path, '.md'),
            'date'    => \Illuminate\Support\Carbon::parse($doc->matter('date')),
            'excerpt' => $doc->matter('excerpt'),
            'body'    => $doc->body(),  // raw markdown — rendu côté view
        ];
    }
}
```

```blade
{{-- resources/views/blog/show.blade.php --}}
<x-layouts.blog :title="$post['title']">
    <article class="prose prose-lg prose-azure max-w-2xl mx-auto">
        <h1>{{ $post['title'] }}</h1>
        <p class="text-sm text-ink-500">{{ $post['date']->isoFormat('LL') }}</p>
        <x-markdown>{!! $post['body'] !!}</x-markdown>
    </article>
</x-layouts.blog>
```
`[CITED: spatie.be/docs/laravel-markdown, github.com/spatie/yaml-front-matter]`

### Pattern 5: LocalBusiness JSON-LD via spatie/schema-org

**What:** Génère du JSON-LD `Plumber` (sous-type de LocalBusiness/HomeAndConstructionBusiness — recommandé Google pour les service pros).

**Example:**
```php
// resources/views/vitrine/home.blade.php (in <head>)
@php
    $schema = \Spatie\SchemaOrg\Schema::plumber()
        ->name('Dlo Azur Piscines')
        ->image(asset('assets/brand/photos/hero-pierre-piscine.jpg'))
        ->url(url('/'))
        ->telephone('+596696940054')
        ->priceRange('€€')
        ->address(\Spatie\SchemaOrg\Schema::postalAddress()
            ->addressCountry('FR')
            ->addressRegion('Martinique')
            ->addressLocality('Fort-de-France')
        )
        ->geo(\Spatie\SchemaOrg\Schema::geoCoordinates()
            ->latitude(14.6037)
            ->longitude(-61.0594)
        )
        ->areaServed([
            \Spatie\SchemaOrg\Schema::city()->name('Fort-de-France'),
            \Spatie\SchemaOrg\Schema::city()->name('Le Lamentin'),
            \Spatie\SchemaOrg\Schema::city()->name('Schoelcher'),
            \Spatie\SchemaOrg\Schema::city()->name('Les Trois-Îlets'),
            \Spatie\SchemaOrg\Schema::administrativeArea()->name('Martinique'),
        ])
        ->openingHoursSpecification([
            \Spatie\SchemaOrg\Schema::openingHoursSpecification()
                ->dayOfWeek(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                ->opens('08:00')->closes('17:00'),
            \Spatie\SchemaOrg\Schema::openingHoursSpecification()
                ->dayOfWeek('Saturday')
                ->opens('09:00')->closes('12:00'),
        ]);
@endphp
{!! $schema->toScript() !!}
```
`[CITED: github.com/spatie/schema-org, jsonld.com/local-business, schema.org/Plumber]`

### Pattern 6: Sitemap dynamique

**What:** Route `/sitemap.xml` qui génère à la volée + cache.

**Example:**
```php
// routes/web.php
use App\Support\BlogRepository;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Route::get('/sitemap.xml', function (BlogRepository $blog) {
    $sitemap = Sitemap::create()
        ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('weekly'))
        ->add(Url::create('/services')->setPriority(0.8))
        ->add(Url::create('/realisations')->setPriority(0.8))
        ->add(Url::create('/contact')->setPriority(0.7))
        ->add(Url::create('/blog')->setPriority(0.6));

    foreach ($blog->all() as $post) {
        $sitemap->add(Url::create("/blog/{$post['slug']}")
            ->setLastModificationDate($post['date'])
            ->setPriority(0.5));
    }

    return response($sitemap->render(), 200, ['Content-Type' => 'application/xml']);
});
```
`[CITED: github.com/spatie/laravel-sitemap]`

### Pattern 7: Bootstrap Laravel sur repo non-vide (greenfield avec planning artifacts)

**What:** `composer create-project laravel/laravel .` échoue dans un répertoire non-vide. Workflow correct :

**Example:**
```bash
# 1. Stash le contenu existant
cd /tmp && composer create-project laravel/laravel:^12.0 dloazur-skeleton

# 2. Copier artefacts Laravel dans le repo (sans écraser le planning)
rsync -av --ignore-existing /tmp/dloazur-skeleton/ /Users/amnesia/dev/dloazur/
# OR — manuel : copier .editorconfig .gitattributes .gitignore artisan bootstrap/ config/
# app/ database/ public/ resources/ routes/ storage/ tests/ vite.config.js
# package.json composer.json composer.lock README.md (renommer si conflit)
# Ne PAS copier : public/index.html (conflit avec /index.html existant)

# 3. Vérifier git status — planning intact
cd /Users/amnesia/dev/dloazur && git status

# 4. Compléter le .gitignore Laravel (vendor, node_modules, .env)
# 5. composer install ; npm install
```

**Alternative plus propre:** Initialiser via Laravel Cloud's git template ou Laravel Installer, lequel pose les questions à la création.

`[ASSUMED — workflow propre à exposer à l'utilisateur pour validation]`

### Anti-Patterns to Avoid

- **Mettre les tokens design dans `tailwind.config.js`** (Tailwind v4 ne le lit plus — utiliser `@theme` dans `app.css`).
- **Utiliser `composer create-project` directement dans le repo non-vide** (échec garanti — passer par `/tmp` ou copier manuellement).
- **Browsershot pour PDF** sur Laravel Cloud (incompatible serverless — DOM PDF Phase 3, pas avant).
- **Captcha sur le contact form** (D-14 : honeypot + rate-limit, pas de friction).
- **Stocker les soumissions contact en DB Phase 1** (D-13 : Mail::to uniquement, pas de table).
- **Activer registerUsers ou emailVerification dans Fortify** (Pierre est seul, seeded — D-09 + Phase 1 out-of-scope).
- **Service Worker / vite-plugin-pwa Phase 1** (Phase 2 only — confirmed CONTEXT.md scope).
- **Background Sync API Phase 1** (Phase 2 only).
- **Livewire pour saisie offline** (jamais — Phase 2 utilisera Alpine pur sur la saisie passage).
- **Numéroter les factures avec l'ID auto-increment** (Phase 3, mais le **schéma** doit être prêt — colonne `numero` séparée de `id`).
- **Suppression de migrations** (rétrocompatibilité difficile en prod — append-only).
- **Hardcoder `dloazurpiscines.com` dans le code** (utiliser `config('app.url')` + env).

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Auth (login, password reset, sessions) | Routes + controllers + form requests + email reset custom | `laravel/fortify` | 100+ heures de dev + edge cases (rate limit, 2FA-ready, password reset tokens, throttling) déjà résolus |
| Markdown rendering | `commonmark-php` direct + custom syntax highlighter | `spatie/laravel-markdown` | Shiki PHP code highlighting bundled, Blade `<x-markdown>` composant, cache config |
| YAML front matter parsing | regex + `yaml_parse` | `spatie/yaml-front-matter` | Robuste, bien testé, format identique à Jekyll/Hugo |
| Sitemap XML generation | View Blade XML + collection manuelle | `spatie/laravel-sitemap` | Conventions `<priority>`, `<changefreq>`, `<lastmod>`, crawler optionnel |
| JSON-LD generation | String concat manual | `spatie/schema-org` | Typesafe, valide schema.org, support de tous les sous-types (Plumber, Article, BreadcrumbList...) |
| Form spam protection | Custom hidden field + timestamp logic | `spatie/laravel-honeypot` | Timing check + invisible field + Livewire integration native (trait + composant Blade) |
| Rate limiting Livewire actions | Manual session counter | `danharrin/livewire-rate-limiting` | Trait `WithRateLimiting`, intégré aux exceptions Livewire, IP+key based |
| PDF rendering | Manual HTML→PDF via shell command | `dompdf/dompdf` Phase 1 (puis `spatie/laravel-pdf` Phase 3) | DomPDF zéro-binaire, fonctionne serverless. Mais c'est **Phase 3**, juste installer Phase 1 |
| Test framework | PHPUnit directement | Pest 4 | DX supérieure, default Laravel 11+, browser tests intégrés |
| Image processing (Phase 2 conversions) | GD/Imagick manuel | `spatie/laravel-medialibrary` (Phase 2) | Collections, conversions automatiques, S3 disk wiring, déjà décidé |

**Key insight:** L'écosystème Spatie + Laravel a une couverture quasi-exhaustive pour ce domaine. Toute solution custom Phase 1 ajoute de la dette technique pour zéro gain — les packages sont battle-tested par 100k+ projets Laravel.

---

## Runtime State Inventory

> N/A — Phase 1 est greenfield. Aucune migration de données, aucun rename, aucun refactor d'état existant. La seule donnée pré-existante est dans le repo (fichiers `.md` du planning + maquettes), pas en runtime.

Si la phase concerne la migration Zyro → Laravel (D-21..D-25), seule **la table d'URLs indexées** (consultation Google Search Console) est de l'inventaire — et c'est un acte opérationnel D-24 délégué à Pierre, pas du code.

---

## Common Pitfalls

### Pitfall 1: Laravel 11 est officiellement EOL (mars 2026 — déjà passé)

**What goes wrong:** Démarrer un nouveau projet greenfield sur Laravel 11 le 28 mai 2026 = livrer une app sans support sécurité dès le J+1.

**Why it happens:** CLAUDE.md et CONTEXT.md D-01 disent « Laravel 11 ». Cette décision a été prise quand Laravel 11 était courant ; le calendrier a avancé.

**Lifecycle source `[CITED: laravel.com/docs/13.x/releases]`:**
| Version | Bug fixes until | Security fixes until |
|---------|-----------------|---------------------|
| Laravel 11 | 2025-09-03 (passé) | **2026-03-12 (passé)** |
| Laravel 12 | 2026-08-13 | 2027-02-24 |
| Laravel 13 | Q3 2027 | 2028-03-17 |

**How to avoid:**
- **Recommandation:** Démarrer sur **Laravel 12** (release stable 2025-02, security jusqu'à 2027-02 — couvre largement la vie utile du MVP + Phases 2-5). Tous les packages cibles supportent 11/12/13 indifféremment (vérifié Packagist).
- **Alternative:** Laravel 13 (release 2026-03-17, security 2028-03-17). Plus moderne mais require PHP 8.3+ (OK : on cible 8.3 D-29).
- Présenter ce choix à l'utilisateur avant de figer.

**Warning signs:** Aucun, c'est silencieux — il faut juste vérifier le calendrier Laravel chaque démarrage de projet.

**Phase to address:** Phase 1 Wave 0 — choix de version avant `composer create-project`.

---

### Pitfall 2: Tailwind v4 ne lit plus `tailwind.config.js`

**What goes wrong:** Suivre littéralement D-05 (« transposer dans `tailwind.config.js` ») = créer un fichier que Tailwind v4 ignore. Tokens invisibles, classes inexistantes, design system non appliqué.

**Why it happens:** Tailwind v4 (release janvier 2025) a opéré une bascule **CSS-first** : tout va dans le CSS via `@theme`. Le `tailwind.config.js` n'est plus lu par défaut. La documentation D-05 a été écrite avant cette bascule.

**How to avoid:**
- Mettre tous les tokens dans `resources/css/app.css` via `@theme {...}` (voir Pattern 1).
- Le **résultat fonctionnel est identique** : couleurs Tailwind, utilities générées. Seul le **fichier cible** change.
- Si l'utilisateur veut absolument Tailwind v3 (config JS), le préciser — mais v4 est le standard 2026 et l'utilisateur lock dit « Tailwind 4 ».

**Warning signs:** `bg-azure-500` ne fonctionne pas alors que tu as bien défini `azure-500` dans un fichier JS.

**Phase to address:** Phase 1 Wave 1 setup CSS.

---

### Pitfall 3: vite-plugin-pwa scope mismatch avec `/public/build/`

**What goes wrong:** Phase 1 **n'installe PAS vite-plugin-pwa** (Phase 2). Mais le piège vaut la peine d'être consigné maintenant pour la Phase 2 : Laravel met les assets buildés dans `/public/build/`, donc par défaut le scope du SW est `/public/build/`. Le SW ne peut intercepter que des URLs sous ce préfixe — inutile pour une app dont les routes sont à `/`.

**Why it happens:** Convention Laravel + Vite ≠ convention vite-plugin-pwa.

**How to avoid (Phase 2):** Configurer `buildBase: '/build/'` + `scope: '/'` + `base: '/'` dans vite-plugin-pwa, ET ajouter le header `Service-Worker-Allowed: /` sur Laravel Cloud (configurable via le routing layer ou middleware).

**Warning signs:** Erreur console : *"The path of the provided scope ('/') is not under the max scope allowed ('/build/')"*.

**Phase to address:** Phase 2 (mentionné pour traçabilité, pas implémentation Phase 1).

`[CITED: github.com/sfreytag/laravel-vite-pwa, vite-pwa-org.netlify.app]`

---

### Pitfall 4: TVA Martinique 8,5 % (Phase 3, mais le **modèle de données** est en Phase 1)

**What goes wrong:** Phase 1 crée la migration `factures` avec une colonne `tva_rate decimal(4,2)`. Phase 3 ajoute une **factory** qui par défaut populate à 20 (taux continental). Bug fiscal en cascade.

**How to avoid (Phase 1):**
- Dans la migration `factures`: `decimal('tva_rate', 4, 2)->default(8.50)` — le défaut au niveau colonne reflète le contexte Martinique.
- Aucune factory de facture Phase 1 (D-09 : seuls user + client demo).

**Warning signs:** Test factory qui crée une facture sans préciser le taux → vérifier que la valeur est 8.50.

**Phase to address:** Phase 1 migration `factures` (anticipation correcte).

`[CITED: PITFALLS.md §Pitfall 6 — TVA DOM 8,5%]`

---

### Pitfall 5: Numérotation facture (Phase 3, mais colonne en Phase 1)

**What goes wrong:** Phase 1 crée la migration `factures` avec uniquement `id` (auto-increment) comme identifiant utilisateur. Phase 3 réalise qu'il faut une numérotation séquentielle CGI sans trou, et doit ajouter une colonne en post-coup.

**How to avoid (Phase 1):**
- Migration `factures` doit inclure : `string('numero')->nullable()->unique()` — séparé de `id`, nullable car attribué au moment du `posted` state (jamais en `draft`).

**Phase to address:** Phase 1 migration `factures`.

`[CITED: PITFALLS.md §Pitfall 7 — Numérotation séquentielle CGI 242 nonies A]`

---

### Pitfall 6: Composer create-project sur repo non-vide

**What goes wrong:** `composer create-project laravel/laravel .` refuse si le répertoire courant n'est pas vide. Or le repo a déjà `.planning/`, `mockups/`, `PRODUCT.md`, `DESIGN.md`, `CLAUDE.md`, `index.html` (redirect mockups), `assets/`, `docs/`.

**How to avoid:** Voir Pattern 7. Stratégie : créer le skeleton dans `/tmp`, copier manuellement les artefacts Laravel dans le repo, conserver les artefacts planning, résoudre conflits (`README.md` Laravel → garder ; `index.html` racine → décider si on supprime ou redirige `/mockups/` vers `/realisations`).

**Phase to address:** Phase 1 Wave 0 — première tâche.

---

### Pitfall 7: index.html racine conflit avec Laravel `/`

**What goes wrong:** Le repo a actuellement `index.html` qui redirige vers `/mockups/`. Quand Laravel sert `public/index.html` (route Laravel `/`), un fichier `index.html` à la racine du repo n'est pas servi par Laravel — il est servi par le webserver direct, court-circuitant Laravel. En dev (`php artisan serve`) ça peut marcher différemment.

**How to avoid:**
- **Supprimer** `index.html` racine (ou le déplacer en `mockups/index.html` qui existe déjà).
- Le repo a déjà `mockups/index.html` (galerie) — l'`index.html` racine est redondant.

**Phase to address:** Phase 1 Wave 0 — étape bootstrap.

---

### Pitfall 8: Fortify FORTIFY_GUARD = web (default) + admin redirect

**What goes wrong:** Fortify utilise par défaut le guard `web` ; sa redirection après login est définie par `Fortify::redirects(...)` ou `config/fortify.php`'s `home`. Si on laisse `/dashboard` par défaut sans créer la route, 404.

**How to avoid:**
- `config/fortify.php` → `'home' => '/admin',`
- Définir `Route::get('/admin', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard')`.

**Phase to address:** Phase 1 setup Fortify.

---

### Pitfall 9: Timezone PHP/PostgreSQL mismatch (Martinique = UTC-4 sans DST)

**What goes wrong:** L'opérateur enregistre un passage à 14h. Laravel store en UTC (18h). Le portail client affiche 18h. Confusion.

**How to avoid:**
- `config/app.php` → `'timezone' => 'America/Martinique'` (Atlantic Standard Time, UTC-4 sans heure d'été)
- Toutes les `datetime`/`timestamps` Eloquent sont rendues dans cette tz par défaut.
- En DB, stocker en UTC reste la norme (PostgreSQL `timestamptz`).
- Acter en Phase 1 pour que toutes les colonnes timestamp soient cohérentes.

**Phase to address:** Phase 1 config app.

`[ASSUMED — Martinique = UTC-4 sans DST est connu mais à confirmer en validation]`

---

### Pitfall 10: Laravel Cloud cold-start scale-to-zero + Lighthouse CI

**What goes wrong:** Lighthouse audit sur la page d'accueil après hibernation montre un TTFB > 2s (cold-wake), le score Performance chute, le critère D-22 « Lighthouse mobile ≥ 90 perf » n'est pas atteint.

**How to avoid:**
- Faire un **warmup HTTP** avant l'audit Lighthouse (curl la home, attendre 200, puis lancer Lighthouse).
- Configurer Laravel Cloud pour **ne pas hiberner en production** (uniquement staging) — D-22 = staging, donc accepter le cold-start sur staging et préchauffer.
- Score « 90 perf » est désormais difficile sur cold-wake ; cible réaliste = 85 sur staging cold, 95 sur prod chauffé.

**Phase to address:** Phase 1 — décision config compute Laravel Cloud + protocole audit.

---

### Pitfall 11: Cache full-page sur vitrine (ROI vs scale-to-zero)

**Open question from CONTEXT « Claude's Discretion »:** est-ce que `spatie/laravel-responsecache` apporte du ROI sur la vitrine Laravel Cloud ?

**Analysis:**
- **Scale-to-zero** = quand personne ne visite, l'app dort. La 1re visite paie un cold-wake (~500ms-2s). Cache full-page **n'aide pas** ce cas (la 1re visite après hibernation reconstruit le cache et paie quand même le cold).
- **Cache full-page** = quand l'app est chaude, sert depuis Redis/file et évite Livewire/Blade render. Économies réelles : ~50-200ms par requête, *si l'app reste chaude*.
- **Trafic vitrine pisciniste solo** = quelques visiteurs/jour. L'app passe son temps hibernée. **Le cache full-page n'est pratiquement jamais hit.**

**Recommandation:** **Ne pas installer `spatie/laravel-responsecache` Phase 1.** Le HTTP cache (header `Cache-Control: public, max-age=300`) sur les routes publiques suffit largement et est gratuit. Si trafic monte (> 100 visites/jour) → ré-évaluer.

**Implémentation Phase 1 minimaliste:**
```php
// In a middleware or route group
->middleware('cache.headers:public;max_age=300;etag')  // Laravel built-in
```

`[CITED: spatie.be/courses/laravel-package-training/laravel-responsecache, github.com/spatie/laravel-responsecache]`

---

## Code Examples (additional patterns)

### Migration: passages with client_uuid + signature_path

```php
// database/migrations/0004_..._create_passages_table.php
public function up(): void
{
    Schema::create('passages', function (Blueprint $table) {
        $table->id();
        $table->uuid('client_uuid')->unique();  // D-08, idempotence Phase 2
        $table->foreignId('piscine_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
        $table->dateTime('visited_at')->nullable();
        $table->string('status', 16)->default('draft');  // draft|synced|archived
        // Mesures eau (nullable Phase 1, fills Phase 2)
        $table->decimal('ph_avant', 4, 2)->nullable();
        $table->decimal('ph_apres', 4, 2)->nullable();
        $table->decimal('chlore_libre', 5, 2)->nullable();
        $table->decimal('chlore_total', 5, 2)->nullable();
        $table->decimal('tac', 6, 2)->nullable();
        $table->decimal('th', 6, 2)->nullable();
        $table->decimal('sel_g_l', 5, 2)->nullable();
        $table->json('actions')->nullable();
        $table->text('notes')->nullable();
        $table->string('pdf_path')->nullable();
        $table->string('signature_path')->nullable();  // D-08, Phase 3
        $table->timestamp('synced_at')->nullable();
        $table->timestamps();

        $table->index(['client_id', 'visited_at']);
    });
}
```

### Migration: factures with odoo_id + numero

```php
// database/migrations/0008_..._create_factures_table.php
public function up(): void
{
    Schema::create('factures', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('numero')->nullable()->unique();  // CGI séquentiel — populated at 'posted'
        $table->foreignId('client_id')->constrained()->cascadeOnDelete();
        $table->foreignId('contrat_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('passage_id')->nullable()->constrained()->nullOnDelete();
        $table->json('lignes')->nullable();
        $table->decimal('total_ht', 10, 2)->default(0);
        $table->decimal('tva', 10, 2)->default(0);
        $table->decimal('total_ttc', 10, 2)->default(0);
        $table->decimal('tva_rate', 4, 2)->default(8.50);  // D-Pitfall 4 TVA Martinique
        $table->string('statut', 16)->default('brouillon');
        $table->unsignedBigInteger('odoo_id')->nullable();  // D-08, Phase 3
        $table->timestamp('odoo_synced_at')->nullable();
        $table->text('odoo_sync_error')->nullable();
        $table->date('date_echeance')->nullable();
        $table->timestamps();
    });
}
```

### Migration: clients with client_uuid (UUID primary alongside id)

```php
// database/migrations/0002_..._create_clients_table.php
public function up(): void
{
    Schema::create('clients', function (Blueprint $table) {
        $table->id();  // BIGINT auto-incr — internal FKs
        $table->uuid('uuid')->unique();  // public-facing identifier
        $table->string('name');
        $table->string('email')->nullable()->index();
        $table->string('phone', 30)->nullable();
        $table->string('address')->nullable();
        $table->text('notes')->nullable();
        $table->string('magic_link_token')->nullable();        // Phase 2
        $table->timestamp('magic_link_expires_at')->nullable(); // Phase 2
        $table->timestamps();
    });
}
```

### Fortify minimal config + PierreSeeder

```php
// database/seeders/PierreSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PierreSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'pierre@dloazurpiscines.com'],  // idempotent
            [
                'name'              => 'Pierre ADAM',
                'password'          => Hash::make(env('OPERATOR_INITIAL_PASSWORD', 'change-me-now')),
                'email_verified_at' => now(),  // skip verification per Phase 1 scope
            ]
        );
    }
}
```

Call in production with `php artisan db:seed --class=PierreSeeder --force` (D-09 dev-only `DatabaseSeeder` separate).

### GitHub Actions workflow

```yaml
# .github/workflows/tests.yml
name: tests

on:
  push: { branches: [main] }
  pull_request: { branches: [main] }

jobs:
  pest:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: testing
          POSTGRES_USER: testing
          POSTGRES_PASSWORD: testing
        ports: ['5432:5432']
        options: >-
          --health-cmd="pg_isready -U testing"
          --health-interval=10s --health-timeout=5s --health-retries=5
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo_pgsql, bcmath, gd, intl
          coverage: none
      - uses: actions/setup-node@v4
        with: { node-version: '22' }
      - name: Composer install
        run: composer install --no-progress --prefer-dist --optimize-autoloader
      - name: Copy env
        run: cp .env.example .env && php artisan key:generate
      - name: NPM
        run: npm ci && npm run build
      - name: Pest
        run: ./vendor/bin/pest --ci
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: testing
          DB_USERNAME: testing
          DB_PASSWORD: testing
```

`[CITED: github.com/pestphp/pest CI examples, laravel.com/docs/13.x/testing]`

### Laravel Cloud build / deploy commands

```bash
# Build commands (run during build phase — config:cache safe here)
composer install --optimize-autoloader --no-dev
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Deploy commands (run just before deployment goes live — DB writes allowed)
php artisan migrate --force
php artisan db:seed --class=PierreSeeder --force  # idempotent updateOrCreate
```

`[CITED: cloud.laravel.com/docs/deployments]`

---

## Mail Driver Choice (D-15 — Claude's Discretion resolved)

**Recommendation: Mailgun, EU region.**

| Criterion | Mailgun | Postmark | Amazon SES |
|-----------|---------|----------|------------|
| EU data residency | ✅ Explicit option (EU + US) | ❌ US-only documented | ✅ eu-west-1, eu-central-1 |
| Pricing low-volume (~50 emails/mo) | Flex plan $1/1000, no monthly minimum | $15/mo minimum (10k mails) | $0.10/1000 — cheapest at scale |
| Free tier | 100/day forever (Flex) | 100 mails/mo free trial | 62k/mo via EC2/Lambda only |
| Setup complexity Laravel | `MAIL_MAILER=mailgun`, env vars only | `MAIL_MAILER=postmark` | Manual SPF/DKIM in Route53 or via console |
| Support | 24/7 live (paid plans), dev-friendly | Top deliverability rep, US business hours | No support without enterprise |
| GDPR friction | Low (EU residency) | High (US data, requires SCC) | Low |
| Lock-in | Mailgun-specific webhook formats | Postmark-specific | AWS account integrated |

**Why Mailgun over alternatives:**
- D-15 explicitly mentions "EU delivery good" and "cheapest" → **Mailgun's Flex plan = $1/1000 emails with no minimum** is cheaper than Postmark (which requires $15/mo minimum) for the ~50 emails/month volume of a contact form. SES is cheaper at scale but support is non-existent — bad for a solo operator who doesn't speak AWS.
- Mailgun has explicit EU data residency (toggleable per domain) — Postmark does not.
- Setup is `composer require symfony/mailgun-mailer symfony/http-client` (Symfony Mailer transport, no extra Laravel package). `MAIL_MAILER=mailgun`, `MAILGUN_DOMAIN=mg.dloazurpiscines.com`, `MAILGUN_SECRET=...`, `MAILGUN_ENDPOINT=api.eu.mailgun.net` (critical — `api.mailgun.net` is US).

**Setup blocking item:** Pierre must verify DNS for `mg.dloazurpiscines.com` (SPF, DKIM, MX records as Mailgun dictates) before Phase 1 can send mail. To include in handover checklist.

`[CITED: mailgun.com/compare/postmark-alternatives, mailgun.com/compare/amazon-ses-vs-mailgun, deliciousbrains.com/ses-vs-mailgun-vs-sendgrid]`

---

## PostgreSQL Naming Conventions (D-discretion resolved)

**Recommendation:** Laravel defaults (snake_case, plural table names) + explicit conventions for edge cases.

| Case | Convention | Example |
|------|-----------|---------|
| Tables | `snake_case` plural | `clients`, `passages`, `photos_meta` |
| Columns | `snake_case` singular | `client_uuid`, `visited_at`, `total_ttc` |
| Primary key | `id` bigint auto-increment | `$table->id()` |
| Public-facing UUID | `uuid` column distinct from `id` | `$table->uuid('uuid')->unique()` |
| Foreign keys | `<table_singular>_id` | `client_id`, `piscine_id` |
| Timestamps | `created_at`, `updated_at` | Laravel default |
| Soft delete (rare) | `deleted_at` nullable | Only on `factures` and `passages` — never enable on `users` |
| Pivot tables | `<plural>_<plural>` alphabetical | `client_user` would be wrong — use named pivot like `contrat_passages` |
| Polymorphic | `<name>able_type`, `<name>able_id` | Phase 2 may use for photos (`mediable_*`), Phase 1 N/A |
| JSON columns | `snake_case` | `actions`, `lignes`, `equipements` |
| Boolean flags | `is_<flag>` or `<flag>` | `actif` (acceptable French) or `is_active` — pick one and stick |

**French vs English column names:** The architecture research uses French names (`piscines`, `passages`, `factures`, `lignes`, `produits`). Maintaining French in DB/code is acceptable for a single-tenant solo operator project — it matches the language of business operations. Tradeoff: ORM tooling expects English by default in some packages. Stick to French for business entities (clients, passages, factures, signatures, diagnostics, contrats, produits, piscines), English for technical/auth (`users`, `password_reset_tokens`, `sessions`, `failed_jobs`).

`[ASSUMED — convention par défaut Laravel, validation utilisateur recommandée]`

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Laravel 11 (CLAUDE.md lock) | Laravel 12 or 13 | Laravel 11 EOL 2026-03-12 | Use Laravel 12 for max compat; tous packages cibles supportent les 3 versions |
| `tailwind.config.js` for tokens | `@theme {}` in CSS file | Tailwind 4 (jan 2025) | All v4 docs use CSS-first; legacy JS config still works behind flag mais déprécié |
| `barryvdh/laravel-dompdf` | `spatie/laravel-pdf` (DomPDF driver) | Spatie released v2 in 2024 | Multi-driver architecture, future-proof for Browsershot/Gotenberg migration |
| PHPUnit Laravel default | Pest 4 Laravel default | Laravel 11 release (2024-03) | Pest 4 includes browser tests via Playwright; PHPUnit still available |
| `Schema::create` in Laravel 10 `kernel.php` | `bootstrap/app.php` only | Laravel 11+ | Streamlined skeleton, less boilerplate, fewer providers |
| `app/Console/Kernel.php` scheduler | `routes/console.php` | Laravel 11+ | Scheduling closures directly in routes |
| `app/Exceptions/Handler.php` | `bootstrap/app.php` `->withExceptions()` | Laravel 11+ | Inline exception customization |
| `App\Providers\RouteServiceProvider` | `bootstrap/app.php` `->withRouting()` | Laravel 11+ | Routes registered inline, but `RouteServiceProvider` can still exist for custom logic |
| Manual JSON-LD strings | `spatie/schema-org` | Spatie ecosystem matured | Typesafe + sub-type accuracy (Plumber > LocalBusiness) |
| Google reCAPTCHA on contact | Honeypot + rate-limit (D-14) | Friction-reduction trend 2023+ | 98% spam reduction without UX cost (per industry research) |

**Deprecated/outdated:**
- `tailwind.config.js` for new projects — use `@theme` in CSS.
- `composer.json` PHP version constraint `^8.2` for new projects — bump to `^8.3` to align with Laravel 13 + Cloud's PHP 8.3 sweet spot.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Pest 4 (v4.7.0) + pest-plugin-laravel (v4.1.0) |
| Config file | `tests/Pest.php` (auto-generated by Laravel skeleton) |
| Quick run command | `./vendor/bin/pest --filter=<TestName>` or `./vendor/bin/pest tests/Feature/<File>.php` |
| Full suite command | `./vendor/bin/pest --ci --parallel` |
| Phase gate | Full suite green + Lighthouse mobile ≥ 85 staging (D-22 baseline relaxed for cold-start) |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| SITE-01 | Home page renders with hero, services, CTA WhatsApp | feature (HTTP) | `pest tests/Feature/HomePageTest.php::test_home_renders_required_blocks` | ❌ Wave 0 |
| SITE-02 | `/services` page returns 200 + meta description present | feature | `pest tests/Feature/HomePageTest.php::test_services_page` | ❌ Wave 0 |
| SITE-03 | `/realisations` page returns 200 + 8 photos visible | feature | `pest tests/Feature/HomePageTest.php::test_realisations_page` | ❌ Wave 0 |
| SITE-04 | `/blog` index lists posts ; `/blog/{slug}` renders markdown | feature | `pest tests/Feature/BlogTest.php` | ❌ Wave 0 |
| SITE-05 | Contact form submits, sends email, honeypot blocks bots, rate-limit throttles | feature (Livewire) | `pest tests/Feature/ContactFormTest.php` | ❌ Wave 0 |
| SITE-06 | WhatsApp CTA links present on home + footer + FAB | feature | `pest tests/Feature/HomePageTest.php::test_whatsapp_ctas_present` | ❌ Wave 0 |
| SITE-07 | `/sitemap.xml` valid XML ; LocalBusiness JSON-LD valid in home | feature | `pest tests/Feature/SeoTest.php` | ❌ Wave 0 |
| AUTH-01 | Pierre logs in with seeded credentials, lands on /admin | feature (HTTP) | `pest tests/Feature/AuthLoginTest.php` | ❌ Wave 0 |
| D-07 schema | All migrations run clean, all tables exist with key columns | feature (DB) | `pest tests/Feature/MigrationsTest.php::test_all_tables_exist_with_critical_columns` | ❌ Wave 0 |

### Sampling Rate
- **Per task commit:** `./vendor/bin/pest --filter=<TestNamePattern>` (sous 5s)
- **Per wave merge:** `./vendor/bin/pest --ci` (sous 30s pour Phase 1)
- **Phase gate:** Full Pest suite green + `npm run build` green + manual Lighthouse against staging deployment

### Wave 0 Gaps
- [ ] `tests/Pest.php` — generated by skeleton, verify `uses(Tests\TestCase::class)->in('Feature')` includes Feature dir
- [ ] `tests/Feature/HomePageTest.php` — covers SITE-01, SITE-02, SITE-03, SITE-06
- [ ] `tests/Feature/BlogTest.php` — covers SITE-04 (with one fixture markdown file `tests/fixtures/blog/test-post.md`)
- [ ] `tests/Feature/ContactFormTest.php` — covers SITE-05 (mock Mail facade, test honeypot trigger, test rate limit exceeded)
- [ ] `tests/Feature/SeoTest.php` — covers SITE-07 (assert sitemap.xml structure, assert JSON-LD valid via parse + schema check)
- [ ] `tests/Feature/AuthLoginTest.php` — covers AUTH-01 (use PierreSeeder + Fortify login route)
- [ ] `tests/Feature/MigrationsTest.php` — covers D-07/D-08 (use `Schema::hasColumn('passages', 'client_uuid')` etc.)
- [ ] Framework install: `composer require pestphp/pest pestphp/pest-plugin-laravel --dev` + `./vendor/bin/pest --init`

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | yes | `laravel/fortify` (bcrypt password hashing, throttling, password reset tokens hashed) |
| V3 Session Management | yes | Laravel built-in (HTTP-only secure cookies in prod, `SESSION_DRIVER=database` or `cookie`, session regeneration on login) |
| V4 Access Control | yes (light) | Phase 1: `auth` middleware on `/admin/*`. Phase 2+ : Policies + Gates on per-resource |
| V5 Input Validation | yes | Livewire `#[Validate]` attributes, Form Requests for Fortify if extended, never trust user input |
| V6 Cryptography | partial | bcrypt via Fortify, HTTPS-only (Laravel Cloud terminates TLS) — no hand-rolled crypto Phase 1 |
| V7 Error Handling | yes | `bootstrap/app.php` `->withExceptions()` — never leak stack traces in prod, log via `LOG_CHANNEL=stderr` for Laravel Cloud |
| V8 Data Protection | yes (RGPD) | EU hosting confirmed (Frankfurt + Paris), `.env` secrets via Laravel Cloud secrets UI, no PII Phase 1 except Pierre |
| V9 Communication | yes | TLS 1.2+ enforced by Laravel Cloud, HSTS header recommended, `URL::forceHttps()` in `AppServiceProvider` for prod |
| V10 Malicious Code | yes (supply chain) | Slopcheck on installs (done in research), Dependabot enabled on GitHub repo, `composer audit` in CI |
| V11 Business Logic | partial | Contact rate-limit (D-14), honeypot (D-14) — anti-abuse Phase 1 only on contact form |
| V12 Files & Resources | N/A Phase 1 | No file uploads Phase 1 (Phase 2) |
| V13 API & Web Services | N/A Phase 1 | No API endpoints Phase 1 (Phase 2 adds `/api/v1/passages`) |
| V14 Configuration | yes | `.env` never committed, `APP_DEBUG=false` in prod (Laravel Cloud), security headers via middleware |

### Known Threat Patterns for Laravel + Livewire + Postgres + Mail

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| SQL injection | Tampering | Eloquent ORM + parameter binding (default Laravel), never `DB::raw()` with user input unsanitized |
| XSS (stored or reflected) | Tampering | Blade `{{ $var }}` auto-escapes ; `{!! $var !!}` only for trusted markdown (spatie/laravel-markdown sanitizes by default) |
| CSRF | Tampering | Laravel default `@csrf` directive, automatic in Livewire forms |
| Session fixation | Spoofing | Laravel regenerates session ID on login (default) |
| Password brute-force | DoS | Fortify throttling per `RateLimiter::for('login', ...)` — defined in `FortifyServiceProvider` |
| Contact form spam | DoS/abuse | Honeypot timing field + rate-limit 5/min/IP (D-14) |
| Email injection (header) | Tampering | Mailable construction via class, never raw concatenation of user input into headers — Laravel Mail abstracts |
| Open redirect | Tampering | Fortify uses signed/named routes for redirects — no user-controlled redirect URLs Phase 1 |
| Mass assignment | Tampering | `$fillable` whitelist on User model ; Eloquent default behavior |
| Sensitive data exposure | Information Disclosure | `User` model `$hidden = ['password', 'remember_token']` — default Laravel |
| Insecure HTTP | Info disclosure | `URL::forceHttps()` in prod, Laravel Cloud terminates TLS |
| Path traversal | Tampering | Blog markdown files read via `File::files(resource_path('content/blog'))` — fixed path, no user input |
| RGPD violation (data location) | Compliance | Frankfurt + Paris hosting, Mailgun EU region, no US transfers |

**Phase 1 specific risk:** The contact form is the **only** user-facing input. Honeypot + rate-limit + Livewire validation = sufficient. No DB persistence of submissions (D-13) reduces attack surface.

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP CLI | Local dev, CI | ✓ | 8.5.6 (local), 8.3 target CI | — |
| Composer | Package install | ✓ | 2.9.8 | — |
| Node | Vite build | ✓ | v26.0.0 | — |
| npm | Vite build | ✓ | 11.14.1 | — |
| Git | All workflows | ✓ | 2.50.1 | — |
| gh CLI | GitHub PR/Actions ops | ✓ | 2.90.0 | — |
| Python 3 | (none required Phase 1) | ✓ | 3.14.5 | — |
| slopcheck | Package legitimacy gate | ✓ (installed during research) | 0.6.1 | All recommended packages [OK] |
| Laravel Cloud CLI | Optional convenience | ❌ | — | Use Laravel Cloud dashboard UI (web-based) |
| Laravel installer | Optional convenience | ❌ | — | `composer create-project laravel/laravel:^12.0 .` works directly |
| psql client | Optional DB inspect | ❌ | — | Use Laravel Cloud DB UI or Tinker (`php artisan tinker`) for inspection |
| Docker | (none required Phase 1) | unknown | — | Not needed — Laravel Cloud handles container/build internally |
| Scaleway account + bucket | S3 disk wiring | ❌ (assumed not yet provisioned) | — | **BLOCKER for any photo flow** ; Phase 1 wiring is config-only so this can wait, but bucket must exist before Phase 2 |
| Mailgun account + EU domain verified | Contact form send | ❌ (assumed not yet provisioned) | — | **BLOCKER for SITE-05 send** — can mock/log mail during dev (`MAIL_MAILER=log`), gate Mailgun config to deploy time |
| Laravel Cloud account + EU project provisioned | Deploy | ❌ (assumed not yet provisioned) | — | **BLOCKER for D-25 staging** — Pierre/user must create account, link GitHub, choose EU Central region |
| GitHub Actions runner (PostgreSQL service) | CI tests | ✓ (via setup) | postgres:16 image | — |

**Missing dependencies with no fallback:**
- Laravel Cloud account (provisioning step in Wave 0)
- Scaleway account + bucket creation (provisioning step — can be Wave N with mock disk in dev)
- Mailgun account + DNS verification (provisioning step — Wave 0 or use log driver until verified)
- Real photos sourced from Pierre (per UI-SPEC §Photo Treatment — blocker for visual completeness, not for code; can ship with placeholders + warning)

**Missing dependencies with fallback:**
- Laravel Cloud CLI → use web dashboard
- psql client → use Tinker or DB UI

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | Laravel 12 is the right choice for greenfield in May 2026 (L11 EOL) | Stack, Pitfall 1 | Project starts on out-of-support version. **Mitigation:** present to user as a decision, not as a fact. |
| A2 | Tailwind 4 is the intended interpretation of D-05 (vs Tailwind 3 with config.js) | Stack, Pattern 1, Pitfall 2 | If user wants v3, all the `@theme` work is wrong file. **Mitigation:** D-01 says « Tailwind 4 » — A2 is supported but the file-target shift (no more `tailwind.config.js`) must be acknowledged. |
| A3 | Mailgun EU region (`api.eu.mailgun.net`) is the right driver for D-15 | Mail Driver Choice | If user prefers Postmark for deliverability, switch is trivial (env var) but Postmark = US data residency = RGPD friction. **Mitigation:** rationale documented. |
| A4 | Martinique timezone is `America/Martinique` (UTC-4, no DST) | Pitfall 9 | If wrong, all timestamps are skewed. **Mitigation:** verify with a French DOM admin if doubt. |
| A5 | `composer create-project` on non-empty repo workflow (Pattern 7) | Pattern 7 | If user has stronger preference (e.g., manual files), workflow changes. **Mitigation:** present approach in plan. |
| A6 | Blog markdown directory is `resources/content/blog/` (D-10 specifies extension `.md` and front matter but not path) | Stack §spatie/laravel-markdown | If user wants `content/posts/` or another path, easily changed in BlogRepository. **Mitigation:** convention chosen; flag in plan. |
| A7 | Geographic coordinates for LocalBusiness JSON-LD = Fort-de-France center (14.6037, -61.0594) | Pattern 5 | Approximate; SEO accepts approximate. **Mitigation:** Pierre's actual operating area may need refinement. |
| A8 | French column names (`clients`, `passages`, `factures`) align with project conventions | Postgres naming | If user prefers English, large rewrite. **Mitigation:** check before migrations Wave; ARCHITECTURE.md already uses French. |
| A9 | Phase 1 cache strategy = HTTP `Cache-Control` headers only, no `spatie/laravel-responsecache` | Pitfall 11 | If trafic is much higher than expected, full-page cache might be worth it. **Mitigation:** rationale documented; defer to v2 if needed. |
| A10 | Pierre's initial password is set via `OPERATOR_INITIAL_PASSWORD` env var | PierreSeeder | If env var leaks, Pierre's account is exposed pre-first-login. **Mitigation:** force password reset at first login OR communicate password out-of-band. |
| A11 | OG image and hero photo are at `assets/brand/photos/hero-pierre-piscine.jpg` (UI-SPEC reference) | Mockup transposition | Photos must be sourced from Pierre. **Mitigation:** flag as blocking item in UI-SPEC §Photo Treatment (already done). |
| A12 | `index.html` racine (redirect vers `/mockups/`) doit être supprimé | Pitfall 7 | Si conservé, conflit avec Laravel `public/index.html`. **Mitigation:** validation utilisateur ; alternative = redirect Blade depuis Laravel. |

---

## Open Questions

1. **Laravel 11 vs 12 vs 13 — quelle version au démarrage ?**
   - What we know: L11 EOL passé, L12 supporté jusqu'à 2027-02, L13 actuel.
   - What's unclear: D-01 dit « Laravel 11 » mais c'est la date qui rend obsolète, pas la décision. User n'a pas anticipé ça.
   - Recommendation: **Laravel 12**. Tous packages cibles compatibles. PHP 8.3 sweet spot. Présenter à user pour confirmation avant `composer create-project`.

2. **Migration `index.html` racine ?**
   - What we know: Fichier sert de redirect vers `/mockups/`.
   - What's unclear: Le supprime-t-on (et perd-on le shortcut local pour les maquettes) ou le déplace-t-on (et risque-t-on un conflit Laravel) ?
   - Recommendation: Supprimer. Les maquettes restent accessibles via `python3 -m http.server` + `/mockups/v1/`.

3. **Pierre's email + password seeding strategy ?**
   - What we know: Un seul opérateur, pas de signup public, pré-créé en seeder.
   - What's unclear: Comment transmettre le mot de passe initial à Pierre ? Email ? Out-of-band ? Force reset au login ?
   - Recommendation: Env var `OPERATOR_INITIAL_PASSWORD` set in Laravel Cloud dashboard (jamais commité), communiqué hors canal, Pierre force-reset à son premier login (Fortify password reset workflow).

4. **Scaleway bucket name + region details ?**
   - What we know: Scaleway Paris `fr-par`, endpoint `https://s3.fr-par.scw.cloud`.
   - What's unclear: Nom du bucket, structure des préfixes (`passages/photos/`, `pdfs/comptes-rendus/`), politique de visibilité (private avec pre-signed URLs).
   - Recommendation: Bucket `dlo-azur-piscines-prod` + `dlo-azur-piscines-staging`, prefixe `passages/{passage_uuid}/photos/{photo_uuid}.jpg`, visibility=private partout. Phase 1 ne crée aucune entrée mais configure le disk.

5. **Cloudflare ou Laravel Cloud DNS pour `dloazurpiscines.com` ?**
   - What we know: D-23 dit « bascule CNAME ». Hostinger gère actuellement le DNS via Zyro (per user memory).
   - What's unclear: Migration DNS vers Cloudflare avant le switch ? Ou rester chez Hostinger DNS ?
   - Recommendation: Rester chez Hostinger DNS pour la Phase 1 (minimiser les changements). Évaluer Cloudflare en Phase 2 ou v2 si DDOS/perf devient une préoccupation.

6. **Sentry / error tracking ?**
   - What we know: Laravel Cloud expose les logs via dashboard.
   - What's unclear: Faut-il Sentry/Bugsnag dès Phase 1 ?
   - Recommendation: **Pas en Phase 1.** Laravel Cloud logs suffisent pour un solo operator. Réévaluer si bugs en prod deviennent un problème de traçabilité.

7. **Analytics — Plausible/Umami ?**
   - What we know: D-28 rejette le widget Google Reviews (perf).
   - What's unclear: User veut-il connaître le trafic vitrine ?
   - Recommendation: Présenter à user comme optionnel — Plausible.io (EU, $9/mo) ou self-hosted Umami. Out of scope par défaut Phase 1.

---

## Sources

### Primary (HIGH confidence — VERIFIED)

- **Packagist registry** (live queries 2026-05-28) — versions for laravel/fortify v1.37.2, spatie/laravel-markdown v2.8.0, spatie/laravel-sitemap v8.1.0, spatie/schema-org v4.0.2, spatie/laravel-honeypot v4.7.1, danharrin/livewire-rate-limiting v2.2.0, dompdf/dompdf v3.1.5, pestphp/pest v4.7.0, pestphp/pest-plugin-laravel v4.1.0
- **Slopcheck 0.6.1** ran on 2026-05-28 — all 9 Phase 1 packages verified [OK]
- **laravel.com/docs/13.x/releases** — Laravel 11/12/13 lifecycle, PHP support matrix
- **laravel.com/docs/13.x/fortify** — Fortify headless integration, Livewire view binding
- **livewire.laravel.com/docs/4.x** — Livewire 3 components, layouts, attributes
- **tailwindcss.com/blog/tailwindcss-v4** — v4 CSS-first config, `@theme` directive, OKLCH defaults
- **tailwindcss.com/docs/guides/laravel** — Laravel + Vite setup for Tailwind 4
- **cloud.laravel.com/docs/deployments** — git push deploy, build/deploy commands
- **cloud.laravel.com/docs/environments** — env vars, hibernation, scale-to-zero
- **cloud.laravel.com/docs/resources/databases/postgres** — Neon serverless Postgres specs (autoscaling .25-4 compute, pgbouncer, cold-wake "few hundred ms")
- **cloud.laravel.com/pricing** — Frankfurt EU Central confirmed available
- **github.com/spatie/laravel-honeypot** — Livewire integration patterns (UsesSpamProtection trait, x-honeypot component)
- **github.com/danharrin/livewire-rate-limiting** — WithRateLimiting trait, exception handling
- **github.com/spatie/laravel-sitemap** — Sitemap::create() API
- **github.com/spatie/schema-org** — Schema::plumber()->...->toScript() typesafe API
- **schema.org/Plumber** — recommended sub-type for plumber/pisciniste
- **jsonld.com/local-business** — LocalBusiness JSON-LD examples + areaServed conventions
- **scaleway.com/en/docs (tutorials/object-storage-* + flysystem)** — fr-par endpoint, use_path_style_endpoint=true

### Secondary (MEDIUM confidence — single source verified)

- **mailgun.com/compare/postmark-alternatives, deliciousbrains.com/ses-vs-mailgun-vs-sendgrid** — pricing + EU residency comparison (Mailgun Flex $1/1k, Postmark $15/mo minimum, SES $0.10/1k)
- **github.com/sfreytag/laravel-vite-pwa** — vite-plugin-pwa + Laravel buildBase workaround (Phase 2 reference)
- **dev.to/hafiz619 — Laravel Cloud $5/month plan + instant scale-to-zero** — scale-to-zero wake details "10x faster"
- **danubedata.ro/blog/laravel-cloud-alternatives-europe-2026** — confirms EU Central (Frankfurt) region availability
- **spatie.be/docs/laravel-markdown** — Shiki PHP highlighting, x-markdown component

### Project-internal (HIGH confidence)

- `/Users/amnesia/dev/dloazur/CLAUDE.md` — Stack lock, forbidden packages, design context
- `/Users/amnesia/dev/dloazur/.planning/REQUIREMENTS.md` — SITE-01..07, AUTH-01 definitions
- `/Users/amnesia/dev/dloazur/.planning/ROADMAP.md` — Phase 1 success criteria
- `/Users/amnesia/dev/dloazur/.planning/research/STACK.md` — Carried-forward stack research
- `/Users/amnesia/dev/dloazur/.planning/research/ARCHITECTURE.md` — Schema design (clients, piscines, passages, photos, produits, contrats, factures, signatures, diagnostics)
- `/Users/amnesia/dev/dloazur/.planning/research/PITFALLS.md` — Pitfalls 1-10 (Background Sync, IDB eviction, doublons, TVA DOM, numérotation CGI, magic link, etc.)
- `/Users/amnesia/dev/dloazur/.planning/phases/01-vitrine-fondations/01-CONTEXT.md` — User decisions D-01..D-31
- `/Users/amnesia/dev/dloazur/.planning/phases/01-vitrine-fondations/01-UI-SPEC.md` — Design contract
- `/Users/amnesia/dev/dloazur/mockups/v1/{vitrine,auth,dashboard}.html` — Mockup source of truth
- `/Users/amnesia/dev/dloazur/mockups/v1/{theme.js,app.css}` — Design tokens to transpose

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — all packages verified via Packagist + slopcheck OK
- Architecture: HIGH — patterns are mainstream Laravel + Livewire idioms
- Pitfalls: HIGH — L11 EOL (calendrier officiel), Tailwind v4 (release notes), TVA/numérotation (PITFALLS.md prior research)
- Mail driver: MEDIUM — Mailgun rationale solid but Postmark/SES alternatives valid in different contexts
- Cache ROI analysis: MEDIUM — based on traffic assumption (low-volume solo operator)

**Research date:** 2026-05-28
**Valid until:** 2026-06-28 (30 days — fast-moving on Laravel version selection; revisit if delaying Phase 1 start beyond June)
