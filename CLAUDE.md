<!-- GSD:project-start source:PROJECT.md -->

## Project

**Dlo Azur Piscines — Plateforme métier & vitrine**

Application web unifiée (Laravel) pour **Dlo Azur Piscines**, pisciniste d'entretien en Martinique : une **vitrine marketing refondue** + un **outil métier de suivi des passages** d'entretien avec **portail client**, et (à terme) un **outil de diagnostic piscine commercialisable**. Elle remplace l'actuel site Zyro qui n'a aucune fonctionnalité métier. Utilisateurs : l'opérateur (le pro, qui travaille seul) et ses clients d'entretien.

**Core Value:** **L'opérateur enregistre chaque passage d'entretien sur le terrain de façon fiable — même sans réseau — et le client consulte l'historique de ses interventions.** Si tout le reste échoue, ça doit marcher.

### Constraints

- **Tech stack**: Laravel 11 + Livewire + Alpine.js + Tailwind + PostgreSQL — fluence PHP du dev, maintenance solo durable, profil CRUD/portail/SEO
- **Offline**: saisie d'un passage offline-first (IndexedDB + Service Worker + Alpine ; **pas Livewire**, qui exige le réseau)
- **Hébergement**: Laravel Cloud région **EU/Francfort** (scale-to-zero, ~4-7 €/mois, Postgres managé) ; photos sur **Scaleway Object Storage (Paris)**
- **RGPD**: données clients hébergées en EU ; AWS + SCCs acceptable car données peu sensibles
- **Odoo**: API externe réservée au plan **Custom** (29,90 €/user/mois, vérifié doc officielle) — sinon **pont CSV** ; **POC Odoo** en tout début de phase facturation
- **Budget**: petite entreprise — managé, simple, pas cher

<!-- GSD:project-end -->

<!-- GSD:stack-start source:research/STACK.md -->

## Technology Stack

## Recommended Stack

### Core Technologies (verrouillés)

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Laravel | 11.x | Backend framework | Stack decision — fluence PHP, CRUD/portal/SEO |
| Livewire | 3.x | Server-rendered reactive UI | Stacks with Laravel, zéro JS pour les vues pro |
| Alpine.js | 3.x | Micro-interactivité + PWA offline controller | Seul framework JS pour la saisie offline (Livewire exige le réseau) |
| Tailwind CSS | 4.x | Utility CSS | Cohérence avec Livewire ecosystem |
| PostgreSQL | 16 | Base de données | Managé par Laravel Cloud, JSONB pour données offline queue |
| Laravel Cloud | EU (Frankfurt) | Hébergement | Scale-to-zero ~4-7€/mois, Postgres managé, RGPD |
| Scaleway Object Storage | Paris | Médias photos | S3-compatible, EU, pas cher (~0,01€/Go) |

## Supporting Libraries — par domaine

### Auth

| Library | Version | Purpose | Confidence |
|---------|---------|---------|------------|
| `laravel/fortify` | 1.x | Auth backend pro (email+password, email verification, 2FA optionnel) | HIGH — official Laravel package |
| `cesargb/laravel-magiclink` | ^2.27 | Magic link clients (portail lecture seule) | HIGH — v2.27.1 vérifié Packagist 2026-04-20, supporte Laravel 11/12/13 |

- Fortify = backend headless pur, branché sur les vues Livewire existantes. Pas de Breeze (trop couplé à Blade scaffold), pas de Jetstream (overkill).
- `cesargb/laravel-magiclink` est le seul package magic-link activement maintenu avec support Laravel 11. Supporte guards personnalisés (`.guard('clients')`), limite de visites, HMAC-signed. L'alternative `maize-tech/laravel-magic-login` existe mais est moins utilisée (462 stars vs 28 laradoo).
- **Ne pas utiliser** `rahaug/laravel-magic-link` — dernière release 2021, abandonné.

### PWA / Offline

| Library | Version | Purpose | Confidence |
|---------|---------|---------|------------|
| `vite-plugin-pwa` (npm) | ^1.3 | Service Worker + Web App Manifest via Workbox | HIGH — v1.3.0 npm, mai 2026 |
| Workbox (bundled) | via vite-plugin-pwa | Précaching assets, NetworkFirst strategy | HIGH |
| `idb` (npm) | ^8.x | IndexedDB wrapper typé pour file d'upload offline | MEDIUM — bibliothèque standard Jake Archibald |

- `vite-plugin-pwa` s'intègre nativement avec Vite (déjà dans Laravel 11). Mode `injectManifest` pour service worker custom — nécessaire pour la logique de synchro IndexedDB.
- La saisie d'un passage offline = Alpine.js écrit dans IndexedDB, service worker intercepte les requêtes de synchro au retour réseau.
- **Contrainte critique Laravel + vite-plugin-pwa** : Laravel met son build dans `/public/build/`, pas à la racine publique. Nécessite `buildBase` custom + header `Service-Worker-Allowed` sur le serveur (Laravel Cloud le supporte via `public/` headers).
- Background Sync API (Workbox) : supportée par Chrome/Edge/Android uniquement — pas Firefox, pas Safari. Fallback obligatoire : polling `online` event côté Alpine pour déclencher la synchro manuellement si SW Background Sync non disponible. Cette limitation affecte ~15% des utilisateurs mobile.
- **File d'upload photos** : IndexedDB stocke les blobs photos, service worker les relit et les POSTe vers `/api/passages/{id}/photos` au retour réseau. Utiliser `idb` pour simplifier l'API IndexedDB async.
- **Ne pas utiliser** Livewire pour la saisie offline — Livewire exige une connexion réseau active pour ses livewire requests.

### Stockage médias

| Library | Version | Purpose | Confidence |
|---------|---------|---------|------------|
| `spatie/laravel-medialibrary` | ^11.22 | Gestion uploads + collections + conversions images | HIGH — v11.22.1 Packagist 2026-05-04 |
| `league/flysystem-aws-s3-v3` | (bundled via Laravel) | Driver S3 pour Scaleway | HIGH |

- `spatie/laravel-medialibrary` préféré à Flysystem brut pour ce projet car : associations Eloquent (Media appartient à Passage), collections nommées (`photos`, `compte-rendu`), conversions automatiques (miniatures pour le portail client), URL temporaires S3.
- Scaleway Object Storage est S3-compatible : configurer le disk `s3` avec `endpoint` Scaleway (`https://s3.fr-par.scw.cloud`) dans `config/filesystems.php`. Fonctionne out-of-the-box avec le driver AWS.
- `->useDisk('s3')` sur la collection `photos` dans le modèle `Passage`.
- **Ne pas utiliser** Flysystem brut seul : vous perdez les collections, les conversions et l'association Eloquent — à réimplémenter manuellement.

### Intégration Odoo

| Library | Version | Purpose | Confidence |
|---------|---------|---------|------------|
| `obuchmann/odoo-jsonrpc` | ^1.9 | Client JSON-RPC Odoo avec service provider Laravel | MEDIUM — v1.9.1 (sept. 2025), 51K installs, 4 issues ouvertes |

- `edujugon/laradoo` : abandonné, dernière release 2020, supporte uniquement Laravel 5-8. **Exclure.**
- `obuchmann/laravel-odoo-api` : abandonné explicitement par le mainteneur ("not maintained, successor is odoo-jsonrpc"). **Exclure.**
- `ripcord/ripcord` : XML-RPC pur, non maintenu. **Exclure.**
- `obuchmann/odoo-jsonrpc` est le successeur officiel recommandé par le même auteur. JSON-RPC (plus moderne qu'XML-RPC), service provider Laravel automatique, injection de dépendance, méthodes CRUD complètes (`search()`, `create()`, `updateById()`, `find()`).
- **Contrainte critique Odoo** : L'API externe Odoo est réservée au plan Custom (29,90€/user/mois — vérifié dans PROJECT.md). **POC obligatoire en début de phase facturation** avant d'implémenter l'intégration complète. Si plan inférieur → pont CSV (export Odoo → import Laravel) comme fallback.

### Facturation PDF

| Library | Version | Purpose | Confidence |
|---------|---------|---------|------------|
| `spatie/laravel-pdf` | ^2.11 | PDF compte-rendu passage + factures | HIGH — v2.11.0 Packagist 2026-05-27 |
| driver recommandé : **DomPDF** (bundled) | via `dompdf/dompdf` | Zéro dépendance binaire, fonctionne sur Laravel Cloud | HIGH |

- `spatie/laravel-pdf` v2 a une architecture multi-drivers (Browsershot, Gotenberg, Cloudflare, DomPDF, WeasyPrint). Cela permet de commencer avec DomPDF (zéro infra) et de migrer vers Gotenberg si les besoins CSS deviennent plus complexes.
- **DomPDF driver** = zéro dépendance binaire, fonctionne sur Laravel Cloud serverless. Limitation : CSS 2.1 uniquement, pas de Flexbox/Grid/Tailwind. Solution : utiliser des layouts PDF dédiés avec CSS table-based ou inline styles — acceptable pour des compte-rendus de passage et des factures.
- **Ne pas utiliser le driver Browsershot** sur Laravel Cloud : nécessite Node.js + Chrome installé sur le serveur — incompatible avec l'environnement serverless de Laravel Cloud.
- **Ne pas utiliser `barryvdh/laravel-dompdf` directement** : `spatie/laravel-pdf` l'encapsule avec une meilleure API Blade, plus l'option de migration de driver.
- Si les factures deviennent trop complexes (logo, Tailwind) → passer au driver **Cloudflare Browser Run** (API cloud, zéro binaire serveur, nécessite compte Cloudflare).

### Paiements Stripe

| Library | Version | Purpose | Confidence |
|---------|---------|---------|------------|
| `laravel/cashier` | ^16.5 | Subscriptions + paiements ponctuels Stripe | HIGH — v16.5.3 Packagist 2026-05-05 |

- Cashier 16.x = Stripe API version `2025-06-30.basil`, support Laravel 11/12/13, supporte les Stripe Billing Meters (nouveau API metered billing).
- Pour ce projet : deux cas d'usage — (1) **paiement ponctuel** pour le diagnostic piscine commercialisable, (2) **abonnement** pour les contrats forfait entretien (si activé côté client).
- Cashier gère les deux via `charge()` (ponctuel) et `newSubscription()` (abonnement récurrent).
- Comportement par défaut depuis Cashier 14 : `default_incomplete` (conforme SCA/3DS européen) — critique pour Martinique (DOM français, réglementation EU).
- **Ne pas utiliser `stripe/stripe-php` directement** : Cashier l'encapsule avec l'intégration Eloquent, webhooks, et gestion 3DS.

### Tests & CI

| Tool | Version | Purpose | Confidence |
|------|---------|---------|------------|
| Pest PHP | ^4.7 | Framework de tests (syntax expressive, coverage) | HIGH — v4.7.0 Packagist 2026-05-03 |
| GitHub Actions | — | CI pipeline | HIGH |
| `pestphp/pest-plugin-laravel` | ^3.x | Helpers Laravel dans Pest | HIGH |

- Pest v4 est le standard Laravel en 2026. Laravel 11 génère des tests Pest par défaut (`php artisan make:test --pest`). Syntax plus concise que PHPUnit pour des tests CRUD/portail.
- **Ne pas utiliser PHPUnit directement** : Pest l'encapsule et offre une meilleure DX. Pest v4 supporte les browser tests via Playwright intégré — utile pour tester le portail client et la PWA offline.
- CI GitHub Actions : `./vendor/bin/pest --ci` dans le workflow `.github/workflows/tests.yml`, PHP 8.2+.

## Installation

# Auth

# PWA (npm)

# Médias

# Odoo

# PDF

# Stripe

# Tests

## Alternatives considérées

| Recommandé | Alternative rejetée | Pourquoi rejetée |
|------------|---------------------|-----------------|
| `cesargb/laravel-magiclink` | `maize-tech/laravel-magic-login` | Moins de communauté, cas d'usage similaire |
| `cesargb/laravel-magiclink` | `rahaug/laravel-magic-link` | Abandonné depuis 2021 |
| `spatie/laravel-medialibrary` | Flysystem brut | Pas d'association Eloquent, pas de collections, tout à réimplémenter |
| `obuchmann/odoo-jsonrpc` | `edujugon/laradoo` | Abandonné, supporte uniquement Laravel 5-8 |
| `obuchmann/odoo-jsonrpc` | `tbondois/odoo-ripcord` | XML-RPC deprecated, not maintained |
| `spatie/laravel-pdf` (DomPDF driver) | `barryvdh/laravel-dompdf` direct | API inférieure, pas de migration de driver |
| `spatie/laravel-pdf` (DomPDF driver) | `spatie/laravel-pdf` (Browsershot driver) | Nécessite Node+Chrome, incompatible Laravel Cloud serverless |
| Pest v4 | PHPUnit directement | Pest l'encapsule + meilleure DX + défaut Laravel 11 |

## Ce qu'il NE FAUT PAS utiliser

| Eviter | Raison | Utiliser à la place |
|--------|--------|---------------------|
| `edujugon/laradoo` | Abandonné, Laravel 5-8 uniquement | `obuchmann/odoo-jsonrpc` |
| `obuchmann/laravel-odoo-api` | Abandonné officiellement par l'auteur | `obuchmann/odoo-jsonrpc` |
| `ripcord` / XML-RPC PHP | XML-RPC est déprécié dans Odoo 17+, non maintenu | JSON-RPC via `obuchmann/odoo-jsonrpc` |
| `barryvdh/laravel-dompdf` direct | API moins bonne, pas de multi-driver pour futur upgrade | `spatie/laravel-pdf` avec driver DomPDF |
| Browsershot / spatie/browsershot | Node.js + Chrome requis, incompatible Laravel Cloud serverless | DomPDF pour commencer, Cloudflare Browser Run si besoins avancés |
| Livewire pour saisie offline | Livewire nécessite connexion réseau active | Alpine.js + IndexedDB + Service Worker |
| Background Sync API seul | Pas supporté Firefox/Safari (~15% utilisateurs mobile) | Fallback Alpine `online` event listener en parallèle |
| PHPUnit directement | Laravel 11 default est Pest, cohérence écosystème | Pest v4 |

## Compatibilité des versions

| Package | Contrainte PHP | Laravel | Notes |
|---------|---------------|---------|-------|
| `laravel/fortify` ^1.x | PHP ^8.2 | 11, 12, 13 | — |
| `cesargb/laravel-magiclink` ^2.27 | PHP ^8.1 | 11, 12, 13 | illuminate/auth ^11 vérifié |
| `spatie/laravel-medialibrary` ^11.22 | PHP ^8.2 | 11, 12, 13 | — |
| `obuchmann/odoo-jsonrpc` ^1.9 | PHP ^8.0 | all (service provider auto) | v1.9.1 sept. 2025 |
| `spatie/laravel-pdf` ^2.11 | PHP ^8.2 | 11, 12, 13 | — |
| `laravel/cashier` ^16.5 | PHP ^8.2 | 11, 12, 13 | Stripe API 2025-06-30.basil |
| `pestphp/pest` ^4.7 | PHP ^8.2 | 11, 12, 13 | Laravel 11 default test runner |
| `vite-plugin-pwa` ^1.3 | — (npm) | Vite 5/6 | Laravel Vite plugin compatible |

## Sources

- Packagist `cesargb/laravel-magiclink` — v2.27.1 (2026-04-20), Laravel 11/12/13 vérifié
- Packagist `spatie/laravel-medialibrary` — v11.22.1 (2026-05-04)
- Packagist `obuchmann/odoo-jsonrpc` — v1.9.1 (2025-09-10), actif, 51K installs
- Packagist `obuchmann/laravel-odoo-api` — abandonné officiellement
- Packagist `edujugon/laradoo` — v3.1.0 (2020), non maintenu
- Packagist `spatie/laravel-pdf` — v2.11.0 (2026-05-27), multi-driver
- Packagist `barryvdh/laravel-dompdf` — v3.1.2 (2026-02-21)
- Packagist `laravel/cashier` — v16.5.3 (2026-05-05), Stripe API 2025-06-30.basil
- Packagist `pestphp/pest` — v4.7.0 (2026-05-03)
- npm `vite-plugin-pwa` — v1.3.0 (2026-05-05)
- Context7 `/laravel/fortify` — features array, passkeys, 2FA vérifié
- Context7 `/spatie/laravel-medialibrary` — `useDisk('s3')`, `toMediaCollectionOnCloudDisk()` vérifié
- Context7 `/vite-pwa/vite-plugin-pwa` — injectManifest, NetworkFirst strategy vérifié
- Context7 `/laravel/cashier-stripe` — `default_incomplete`, SCA/3DS, `allowPaymentFailures()` vérifié
- Spatie docs `laravel-pdf/v2/requirements` — drivers DomPDF (zéro binaire), Browsershot (Node+Chrome), Gotenberg (Docker), Cloudflare (API cloud) vérifié
- vite-pwa-org.netlify.app/frameworks/laravel — contrainte `buildBase` + header `Service-Worker-Allowed` vérifié
- Smashing Magazine 2025-04 — architecture IndexedDB + Background Sync offline photo upload
- GitHub `obuchmann/odoo-jsonrpc` — service provider Laravel, CRUD Odoo, JSON-RPC vérifié

<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->

## Conventions

Conventions not yet established. Will populate as patterns emerge during development.
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->

## Architecture

Architecture not yet mapped. Follow existing patterns found in the codebase.
<!-- GSD:architecture-end -->

<!-- GSD:skills-start source:skills/ -->

## Project Skills

| Skill | Description | Path |
|-------|-------------|------|
| impeccable | "Use when the user wants to design, redesign, shape, critique, audit, polish, clarify, distill, harden, optimize, adapt, animate, colorize, extract, or otherwise improve a frontend interface. Covers websites, landing pages, dashboards, product UI, app shells, components, forms, settings, onboarding, and empty states. Handles UX review, visual hierarchy, information architecture, cognitive load, accessibility, performance, responsive behavior, theming, anti-patterns, typography, fonts, spacing, layout, alignment, color, motion, micro-interactions, UX copy, error states, edge cases, i18n, and reusable design systems or tokens. Also use for bland designs that need to become bolder or more delightful, loud designs that should become quieter, live browser iteration on UI elements, or ambitious visual effects that should feel technically extraordinary. Not for backend-only or non-UI tasks." | `.agents/skills/impeccable/SKILL.md` |
<!-- GSD:skills-end -->

<!-- GSD:workflow-start source:GSD defaults -->

## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:

- `/gsd:quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd:debug` for investigation and bug fixing
- `/gsd:execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->

<!-- GSD:profile-start -->

## Developer Profile

> Profile not yet configured. Run `/gsd-profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
