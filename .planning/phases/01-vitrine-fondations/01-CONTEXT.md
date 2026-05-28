# Phase 1: Vitrine & Fondations - Context

**Gathered:** 2026-05-28
**Status:** Ready for planning

<domain>
## Phase Boundary

Mettre en ligne le site public sur Laravel Cloud (remplaçant Zyro), permettre à l'opérateur de se connecter, et déployer **le schéma de base de données complet** — y compris les tables qui ne seront pas encore utilisées en Phase 1 (`client_uuid`, `odoo_id`, `signature_path`).

**In scope :**
- Scaffold initial `laravel/laravel` (greenfield — pas de `composer.json` au démarrage)
- Configuration Laravel Cloud EU/Francfort + PostgreSQL managé + Cloudflare R2 (gratuit, zero-egress) wiring
- Auth pro via `laravel/fortify` (email + mot de passe)
- Migrations complètes : clients, piscines, passages, photos_meta, produits, contrats, factures, signatures, diagnostics — avec `client_uuid` (UUID v4), `odoo_id` (nullable), `signature_path` (nullable)
- Vitrine SEO Martinique : home, services, hospitalité, réalisations, blog, contact (mapping 1:1 depuis `mockups/v1/vitrine.html`)
- SEO local : meta tags, sitemap.xml, données structurées LocalBusiness JSON-LD, OG tags
- CTA WhatsApp en évidence sur toutes les pages publiques
- Shell admin pré-câblé post-login (layout, sidebar, modules grisés pour Phase 2)
- CI GitHub Actions (Pest 4)
- Mentions légales / RGPD (obligatoires pour SIRET DOM)

**Out of scope (déféré à Phase 2+) :**
- Toute la logique métier (CRUD clients/piscines, saisie passage, photos)
- PWA / Service Worker / IndexedDB
- Magic link client (AUTH-02 → Phase 2)
- Facturation, signatures électroniques, diagnostic
- Blog admin DB-backed (markdown-in-repo suffit pour v1)

</domain>

<decisions>
## Implementation Decisions

### Stack & infra (carried forward, locked)
- **D-01:** Stack Laravel 13 + Livewire 3 + Alpine.js 3 + Tailwind 4 + PostgreSQL 16 (PROJECT.md Key Decisions, CLAUDE.md)
  - Override 2026-05-28 : Laravel 11 EOL atteinte (sécurité 2026-03-12) — voir RESEARCH §Pitfall #1. Laravel 13 supporté jusqu'en 2028-03-17. Tous les packages Phase 1 ciblés (Fortify, Livewire 3, Pest 4, Spatie suite) supportent Laravel 11/12/13 indifféremment.
- **D-02:** Hébergement Laravel Cloud EU/Francfort + Postgres managé ; photos sur **Cloudflare R2** (S3-compatible, 10 GB gratuits + zero egress fees indéfiniment, DPA RGPD signable — accepté par PROJECT.md « AWS+SCCs acceptable car données peu sensibles »). **Amendé 2026-05-28** — Scaleway Object Storage retiré (pas de free tier, ~5€/mois pour rien à Phase 1).
- **D-03:** Auth pro via `laravel/fortify` (headless, branché sur vues Livewire) — pas Breeze, pas Jetstream
- **D-04:** Pest 4 comme test runner (default Laravel 11 + meilleure DX)
- **D-05:** Tailwind 4 — transposer `mockups/v1/theme.js` (tokens OKLCH) et `mockups/v1/app.css` dans `@theme directive in resources/css/app.css` (Tailwind v4 CSS-first — voir RESEARCH §Pitfall #2) + classes utilitaires custom
- **D-06:** Mapping 1:1 du design depuis `mockups/v1/vitrine.html` (mockup verrouillé, design system impeccable)

### Schéma complet dès Phase 1 (roadmap success criterion #5)
- **D-07:** Toutes les migrations métier déployées en Phase 1, même celles consommées plus tard (clients, piscines, passages, photos_meta, produits, contrats, factures, signatures, diagnostics)
- **D-08:** Colonnes critiques à présentes dès le 1er deploy : `client_uuid` UUID v4 unique (idempotence offline Phase 2), `odoo_id` nullable bigint (Phase 3), `signature_path` nullable string (Phase 3)
- **D-09:** Pas de seed data métier en production. Seeds dev uniquement (factory utilisateur pro + ~3 clients démo) pour tests local/CI.

### Blog (SITE-04) — Markdown-in-repo
- **D-10:** Articles de blog en `.md` dans `resources/content/blog/` avec front matter YAML (title, date, excerpt, slug). Parser via `spatie/laravel-markdown`. Git push pour publier. Volume cible ~3-6 articles/an ne justifie pas un CRUD admin.
- **D-11:** Liste chronologique simple, pas de système de tags/catégories à v1 (déferrable si volume augmente)
- **D-12:** Routes : `/blog` (index) + `/blog/{slug}` (article). SEO : meta + OG + structured data Article JSON-LD par post.

### Contact (SITE-05) — Email + honeypot
- **D-13:** Form Livewire `/contact` → `Mail::to('contact@dloazurpiscines.com')` via Laravel Mail. Pas de persistance DB des soumissions à v1.
- **D-14:** Anti-spam : honeypot field caché + rate-limit Laravel `RateLimiter` (5/min par IP). **Pas de captcha** (friction UX inutile, perte de leads).
- **D-15:** Driver mail : **Brevo** (ex-Sendinblue, société française, siège Paris, RGPD natif). Plan gratuit 300 emails/jour → couvre largement le volume vitrine (~30/mois). Driver Symfony officiel `symfony/brevo-mailer`. Env : `MAIL_MAILER=brevo` + `BREVO_API_KEY=...`. Logo expéditeur `contact@dloazurpiscines.com`. Pas besoin de vérifier un sous-domaine `mg.*` (Brevo verifie le sender direct via DKIM/SPF sur `dloazurpiscines.com`). **Amendé 2026-05-28** — Mailgun retiré (plan Foundation $15/mois minimum requis, mauvais ROI pour ~30 emails/mois).
- **D-16:** Fallback WhatsApp visible sous le form (bouton « ou écrire sur WhatsApp »). WhatsApp reste le CTA principal partout ailleurs.

### Back-office post-login (AUTH-01) — Shell pré-câblé
- **D-17:** Route `/admin` (ou `/dashboard`) post-login. Layout admin réutilisable (sidebar gauche + topbar + slot main) construit en Blade + Livewire layouts.
- **D-18:** Sidebar nav : Tableau de bord (actif), Clients (grisé), Passages (grisé), Factures (grisé), Catalogue (grisé). Les items grisés portent une mention « bientôt » + sont non-cliquables. Phase 2 active les routes Clients/Passages.
- **D-19:** Page « Tableau de bord » stub : carte de bienvenue avec prénom de Pierre + 3 placeholders de stats (« N clients », « N passages cette semaine », « N factures en attente ») affichant `—` pour l'instant. Sert de canevas réutilisé par Phase 2.
- **D-20:** Logout via Fortify default route.

### Cutover Zyro → Laravel Cloud (SITE-07) — Staging + DNS switch
- **D-21:** Déploiement Phase 1 sur subdomain de staging d'abord. Préférence : `preprod.dloazurpiscines.com` (sous-domaine du domaine final) ou `dloazur.laravel.cloud` (URL fournie par Laravel Cloud).
- **D-22:** Validation avant cutover : Lighthouse mobile ≥ 90 perf/SEO/a11y, sitemap.xml accessible, structured data testé via Schema.org validator, OG preview vérifié (debugger Facebook), formulaire contact + WhatsApp testés en réel.
- **D-23:** TTL DNS baissé à 300s ~24h avant le switch. Switch = bascule CNAME `dloazurpiscines.com` → Laravel Cloud. Validation manuelle post-switch (curl + navigateur réel).
- **D-24:** Inventaire des URLs Zyro indexées avant le switch (Google Search Console + `site:dloazurpiscines.com` Google). Si > 3 URLs uniques avec trafic : redirect map 301 dans `routes/web.php` ou middleware Laravel. Si site Zyro plat (juste `/`) : aucun redirect nécessaire.
- **D-25:** Phase 1 livre la vitrine **validée sur staging**. Le DNS switch est un acte opérationnel déclenché par Pierre (avec assistance) quand il est prêt — la livraison technique ne dépend pas du switch.

### SEO local Martinique (SITE-07)
- **D-26:** Données structurées JSON-LD `LocalBusiness` (sous-type approprié : `Plumber`/`HomeAndConstructionBusiness`) avec : `name`, `image`, `address` (Martinique), `geo` (coordonnées approximatives), `telephone`, `priceRange`, `openingHoursSpecification`, `areaServed` (Martinique + communes principales : Fort-de-France, Le Lamentin, Schoelcher, Les Trois-Îlets…).
- **D-27:** Sitemap XML généré dynamiquement (route Laravel) couvrant pages publiques + tous les articles de blog.
- **D-28:** Avis Google **server-side via Google Places Details API** + cache DB (table `google_reviews`, refresh quotidien via scheduler), rendu HTML natif inline sur la home (section au-dessus du pli). **PAS de widget embed Google** (alourdit la perf, exige bannière consent RGPD pour les cookies tiers). Lien externe vers la fiche Google Business complète pour la liste exhaustive. Prérequis runtime : `GOOGLE_PLACES_API_KEY` + `GOOGLE_PLACE_ID` en env (Pierre fournit au deploy). Si l'API n'est pas configurée OU si la table est vide, la section s'auto-masque gracefully (pas de placeholder vide affiché).

### Différenciation marché local (post-benchmark concurrence 2026-05-28)
- **D-32:** **Tarif indicatif affiché** « À partir de XX€/passage » dans le hero + dans la section services. Valeur config-driven (`config/pricing.php` → `'passage_starting' => env('PRICING_PASSAGE_STARTING', 80)`) avec disclaimer « Devis personnalisé selon volume, accès et traitement ». Stratégie : aucun concurrent n'affiche de prix sur la Martinique → friction-killer + signal de transparence vs marché opaque. **Pierre confirme la valeur exacte à l'exécution.**
- **D-33:** **CTA secondaire « Diagnostic gratuit »** sur la home, à côté du WhatsApp principal. Promesse : 1 visite + analyse de l'eau + devis chiffré sous 48h, gratuit pour les nouveaux clients. Lien vers `/contact` avec un préset de message « Je souhaite un diagnostic gratuit ». WhatsApp reste le CTA principal (chat instant), Diagnostic gratuit = 2e niveau d'engagement (visite physique).
- **D-34:** **Service distinct « Traitement eau verte en urgence »** — section dédiée sur la home + une page `/services/eau-verte-urgence`. Photo avant/après. Promesse « Intervention sous 48h, eau claire en 5-7 jours ». Niche claim pertinent en Martinique humide (climat tropical → algues fréquentes). Adapté de Quality-Piscine qui l'a en service à part.
- **D-35:** **Bloc « Nos engagements »** avant le footer — 4 items courts (Rapport photo à chaque passage / WhatsApp réactif 7j/7 / Devis transparent sous 48h / Solo artisanal, pas un call-center). Réassurance + différenciation vs concurrence locale qui reste vague.

### CI/Deploy
- **D-29:** GitHub Actions `.github/workflows/tests.yml` : `composer install` + `npm ci` + `npm run build` + `./vendor/bin/pest --ci`. PHP 8.3.
- **D-30:** Déploiement Laravel Cloud : auto-deploy sur push `main` après CI vert. Migrations exécutées automatiquement au deploy (`php artisan migrate --force`).
- **D-31:** Branch strategy : work sur `feature/*` → PR vers `main` → merge déclenche deploy.

### Claude's Discretion
- ~~Choix précis du driver mail~~ → **Résolu en D-15 (amendé 2026-05-28 : Brevo)**.
- Structure exacte des Blade layouts (`layouts/app.blade.php` pour vitrine, `layouts/admin.blade.php` pour back-office) et des composants Livewire vs Blade components purs (sans état).
- Naming Postgres : convention Laravel par défaut (`snake_case`, pluriel pour tables).
- Mise en cache des pages publiques (full-page cache via Laravel response cache) ou pas — recherche-phase évaluera ROI vs scale-to-zero Laravel Cloud.

### Amendments (post-research)
- **2026-05-28** D-01 updated: Laravel 11 → Laravel 13 (L11 EOL 2026-03-12, RESEARCH §Pitfall #1).
- **2026-05-28** D-05 updated: tailwind.config.js → @theme directive in resources/css/app.css (Tailwind v4 CSS-first, RESEARCH §Pitfall #2).
- **2026-05-28** D-28 updated: « pas de Google Reviews widget » → « avis Google server-side via Places API + cache DB, rendu HTML natif sur home ». Décision révisée car D-28 sur-indexait sur la perf au détriment de la conversion. La nouvelle approche conserve l'avantage perf (zéro JS tiers, zéro cookie) tout en exposant le social proof. Implémentation : nouvelle table `google_reviews`, service `GoogleReviewsService`, scheduler `sync-google-reviews:daily`, composant Livewire ou Blade `<livewire:google-reviews>` sur la home. Voir plans 01-02 (migration), 01-03 (rendu home), 01-04 (service + scheduler + composant + tests).
- **2026-05-28** D-15 updated: « Mailgun ou Postmark » → « **Brevo** » (société française Paris, RGPD natif, 300 emails/jour gratuits couvre largement les ~30 emails/mois du formulaire contact). Mailgun retiré : plan Foundation $15/mois minimum requis = mauvais ROI. Env vars : `MAIL_MAILER=brevo`, `BREVO_API_KEY=...`. Driver Symfony officiel `symfony/brevo-mailer`. Voir plans 01-01 (composer require swap), 01-04 (config mail.php + threat model T-4-10 mis à jour), 01-06 (cutover gate — vérif sender Brevo au lieu de DNS Mailgun).
- **2026-05-28** D-02 updated: « Scaleway Object Storage (Paris) » → « **Cloudflare R2** » (10 GB gratuits + zero egress fees, S3-compatible, DPA RGPD signable). Scaleway retiré : pas de free tier (~5€/mois pour rien à Phase 1 et au début Phase 2 où le volume est <1 GB). R2 = gratuit jusqu'à ~10 000 photos (Phase 2-3-4 couvertes pour les premières années). Env vars : `R2_ACCOUNT_ID`, `R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY`, `R2_BUCKET` (4 vars, contre 5 pour Scaleway). Endpoint : `https://${R2_ACCOUNT_ID}.r2.cloudflarestorage.com`. PROJECT.md autorise déjà US+SCCs pour les données peu sensibles. Voir plans 01-01 (config/filesystems.php + CLOUD-PROVISIONING.md), 01-02 (photos_meta.disk default 'r2' au lieu de 'scaleway').
- **2026-05-28** Ajout D-32, D-33, D-34, D-35 (différenciation marché local post-benchmark) : tarif indicatif affiché, CTA Diagnostic gratuit, service Eau verte urgence, bloc Engagements. Benchmark des 3 concurrents Martinique (quality-piscine.fr, bluepiscineservices.fr, elitepiscinecaraibes.com) a confirmé que ces 4 leviers sont des trous communs au marché local — friction-killers à coût d'implémentation faible. Voir plan 01-03 (nouvelles partials + config/pricing.php) et plan 01-04 (préset message contact « Diagnostic gratuit »).

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Stratégie & cadrage produit
- `PRODUCT.md` — registre par défaut (`product`), avec **vitrine = brand** au cas par cas. Users, anti-references, 5 design principles, accessibilité WCAG AA. **MUST READ avant tout travail UI.**
- `.planning/PROJECT.md` — Core value, requirements active/validated, Key Decisions, Out of Scope explicit
- `.planning/REQUIREMENTS.md` — v1 requirements SITE-01..07 + AUTH-01 (Phase 1 scope) + traceability complète
- `.planning/ROADMAP.md` §"Phase 1: Vitrine & Fondations" — goal, success criteria, requirements mapping
- `.planning/STATE.md` — current position + accumulated context + blockers
- `docs/superpowers/specs/2026-05-27-dloazur-refonte-design.md` — note de cadrage détaillée (v2)
- `docs/superpowers/specs/2026-05-27-dloazur-design-system.md` — design system détaillé (palette eau/Caraïbes, Plus Jakarta Sans + Inter, prompts Claude Design écran saisie passage + accueil vitrine)

### Design system & maquettes (verrouillés)
- `DESIGN.md` — système de tokens OKLCH, typographie (Fredoka + Inter), élévation, composants, do's/don'ts. Format Stitch (frontmatter YAML + 6 sections).
- `.impeccable/design.json` — sidecar (rampes tonales, ombres, motion, breakpoints, primitives composants rendues)
- `mockups/v1/vitrine.html` — **maquette vitrine de référence** à transposer 1:1 en Blade
- `mockups/v1/theme.js` — tokens design (OKLCH, échelle typo, breakpoints) à transposer dans `tailwind.config.js`
- `mockups/v1/app.css` — utilities custom + classes design system à transposer
- `mockups/v1/auth.html` — référence visuelle login pro (Fortify views à styler en accord)
- `mockups/v1/dashboard.html` — référence shell admin (à transposer pour le tableau de bord stub)
- `mockups/v1/index.html` — galerie des maquettes pour vue d'ensemble
- `.claude/skills/impeccable/SKILL.md` — skill obligatoire avant tout travail UI

### Recherche stack & architecture
- `.planning/research/SUMMARY.md` — synthèse stack + features + architecture + pitfalls (HIGH confidence Phase V)
- `.planning/research/STACK.md` — versions vérifiées Packagist/npm, contraintes Laravel Cloud
- `.planning/research/ARCHITECTURE.md` §"Phase V — Fondations" + diagramme couche app + frontières internes
- `.planning/research/FEATURES.md` §MVP Phase V — vitrine + déploiement
- `.planning/research/PITFALLS.md` — Background Sync (Phase 2 not Phase 1), TVA DOM, numérotation CGI

### Mémoire utilisateur (background)
- `/Users/amnesia/.claude/projects/-Users-amnesia-dev-dloazur/memory/brand-identity.md` — palette extraite (azure #0080ff, marine, turquoise, Fredoka)
- `/Users/amnesia/.claude/projects/-Users-amnesia-dev-dloazur/memory/hostinger-access.md` — accès au site Zyro existant (utile pour inventaire URLs avant cutover D-24)

### Instructions globales
- `CLAUDE.md` — instructions projet : stack verrouillé, libraries recommandées (Fortify, spatie/medialibrary, spatie/laravel-pdf, Pest), packages explicitement interdits, design context (impeccable skill)

</canonical_refs>

<code_context>
## Existing Code Insights

### Greenfield Laravel
- Le repo n'a **pas encore de scaffold Laravel** (pas de `composer.json`, `artisan`, `app/`). Phase 1 commence par `composer create-project laravel/laravel .` (ou via Laravel Cloud's git template).
- Les seuls assets en place : `mockups/v1/*` (HTML statiques + tokens), `index.html` racine (redirect vers `/mockups/`), `PRODUCT.md`, `DESIGN.md`, `CLAUDE.md`, `.planning/`.

### Reusable Assets (mockups → Blade)
- `mockups/v1/vitrine.html` : structure HTML complète prête à découper en composants Blade — top bar fixe, hero, services, hospitalité B2B, réalisations grid, "Le pisciniste" bio, footer.
- `mockups/v1/theme.js` : ~30 design tokens (OKLCH palettes azure/navy/sand/turquoise, échelle typo, breakpoints) → mappage direct vers `tailwind.config.js` `theme.extend.colors`.
- `mockups/v1/app.css` : utilities custom (gradient overlays, focus rings, prose styles blog) → migrer vers `resources/css/app.css`.
- Icones inline SVG dans `vitrine.html` (WhatsApp, gouttes, sun…) → extraire en `<x-icon::*>` Blade components.

### Established Patterns
- **Brand colors verrouillés** : azure-500 (#0080ff alignement logo), navy (marine carte de visite), turquoise lagon, sand neutres. OKLCH partout, **jamais `#000`/`#fff`**.
- **Typo verrouillée** : Fredoka pour le display, Inter pour le corps. `font-display` et `font-sans` dans Tailwind.
- **Touch targets ≥ 44px** systématique (PRODUCT.md Accessibility) — pertinent dès le menu mobile vitrine.
- **CTA WhatsApp** : couleur officielle `#25D366`, icone inline SVG, lien `https://wa.me/596696940054`. Pattern repris partout.

### Integration Points
- **Fortify views** : Phase 1 publie les vues Fortify (`php artisan vendor:publish --tag=fortify-views`) et les style avec les tokens du design system (cohérence vitrine ↔ login).
- **`tailwind.config.js`** : créé en Phase 1, étendu par Phase 2+. Tokens centralisés ici.
- **`config/filesystems.php`** : disque `r2` configuré pour Cloudflare R2 (`endpoint=https://${R2_ACCOUNT_ID}.r2.cloudflarestorage.com`, `use_path_style_endpoint=false`) dès Phase 1, même si la 1re vraie upload arrive en Phase 2.
- **Routes structure** : `routes/web.php` pour public + auth, modulariser tôt (`routes/admin.php` inclus via `RouteServiceProvider`) pour faciliter l'extension Phase 2.
- **`AppServiceProvider::boot()`** : `URL::forceHttps()` en prod, `Vite::macro` pour PWA Phase 2 (anticipation).

</code_context>

<specifics>
## Specific Ideas

- **Mapping mockup → Blade obligatoire 1:1.** La maquette `mockups/v1/vitrine.html` est verrouillée — pas de redesign. Transposition directe en `resources/views/vitrine/*.blade.php` + composants. Tokens dans `tailwind.config.js`.
- **CTA WhatsApp** = bouton vert `#25D366`, présent dans le header + à la fin de chaque section pertinente + dans le footer. Lien direct `https://wa.me/596696940054`.
- **« Le pisciniste »** : bloc bio de Pierre sur la vitrine — photo réelle requise (PRODUCT.md anti-references rejette le stock tropical). À sourcer auprès de Pierre avant le launch.
- **Hospitalité B2B** : section dédiée pour conciergeries / locations saisonnières, ton plus pro (PRODUCT.md « deux publics, une seule voix »). Mockup l'a déjà.
- **OG image** : `assets/brand/photos/hero-pierre-piscine.jpg` (référencé dans vitrine.html). Vérifier que l'asset existe ou le sourcer.
- **Mentions légales / RGPD** : pages statiques (Blade simple), pas un blog post. Contenu à rédiger avec Pierre (SIRET, RCS, mention hébergeur Laravel Cloud, DPO/contact RGPD).

</specifics>

<deferred>
## Deferred Ideas

- **Tags/catégories blog** → si volume > 10 articles, ré-évaluer en milestone v2 (D-11)
- **DB-backed admin pour blog** → si Pierre veut éditer sans dev intervention plus tard (D-10 alternative non retenue)
- **Persistance DB des soumissions contact** → utile pour suivi B2B hospitalité (lead history), mais déférable à v2 — Pierre lit ses mails pour l'instant (D-13)
- **Google Reviews widget embed** → trop lourd pour la perf, lien externe à la place (D-28)
- **Préchargement / cache full-page vitrine** → à évaluer en recherche-phase ou en optimisation v2 selon trafic réel
- **2FA pour auth pro** → Fortify le supporte, mais Pierre est seul utilisateur — déférable jusqu'à scale
- **Email verification sur signup** → Pierre est pré-créé via seeder (un seul opérateur), pas de signup public — pas pertinent

</deferred>

---

*Phase: 1-Vitrine & Fondations*
*Context gathered: 2026-05-28*
*Decisions amended: 2026-05-28 (D-01, D-02, D-05, D-15, D-28 amended; D-32, D-33, D-34, D-35 added — see Amendments block above)*
