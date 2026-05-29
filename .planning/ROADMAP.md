# Roadmap: Dlo Azur Piscines

## Overview

Cinq phases dérivent naturellement des requirements : une fondation vitrine + infra, le coeur de valeur offline-first, la facturation avec gate POC Odoo, les notifications, et le diagnostic commercialisable. L'ordre est imposé par les dépendances techniques — l'infra avant tout, le coeur de valeur avant la facturation, le diagnostic presque indépendant en dernier.

## Phases

**Phase Numbering:**

- Integer phases (1, 2, 3) : travail planifié du milestone
- Decimal phases (2.1, 2.2) : insertions urgentes (marquées INSERTED)

- [x] **Phase 1: Vitrine & Fondations** - Scaffold Laravel Cloud, auth pro, migrations complètes, vitrine SEO remplaçant Zyro (completed 2026-05-28)
- [x] **Phase 2: MVP Suivi Offline-First** - Saisie passage offline + photos, clients/piscines, portail client — coeur de valeur (completed 2026-05-28)
- [ ] **Phase 3: Facturation & Odoo** - POC Odoo, catalogue, contrats, factures en franchise de TVA (art. 293 B CGI), PDF, signature
- [ ] **Phase 4: Notifications** - Email compte-rendu + rappel J-1, option WhatsApp
- [ ] **Phase 5: Diagnostic Commercialisable** - Wizard eau verte, doses serveur, disclaimer, Stripe A+B

## Phase Details

### Phase 1: Vitrine & Fondations

**Goal**: **As a** visiteur ou opérateur Dlo Azur, **I want to** consulter le site public en ligne sur Laravel Cloud (vitrine SEO + blog + contact) et permettre à l'opérateur de se connecter à un back-office stub, **so that** la vitrine Zyro est remplacée et l'infrastructure (auth, migrations métier complètes, schéma forward-compat) est posée pour les Phases 2-5.
**Mode:** mvp
**Depends on**: Nothing (first phase)
**Requirements**: SITE-01, SITE-02, SITE-03, SITE-04, SITE-05, SITE-06, SITE-07, AUTH-01
**Success Criteria** (what must be TRUE):

  1. Un visiteur voit la page d'accueil (hero, services, réalisations, CTA WhatsApp) sur mobile et desktop
  2. Un visiteur consulte les pages services, galerie, blog et contact sans erreur
  3. Les balises meta, sitemap XML et données structurées LocalBusiness sont présents et valides
  4. L'opérateur se connecte avec email + mot de passe et accède au back-office
  5. Le schéma de base de données complet est déployé (inclut `client_uuid`, `odoo_id`, `signature_path`)

**Plans:** 9/6 plans complete
Plans:
**Wave 1**

- [x] 01-01-PLAN.md — Walking Skeleton: Laravel 13 scaffold + Tailwind 4 @theme + Pest 4 + CI + base layouts + route partitions + Laravel Cloud staging + CLAUDE.md/PROJECT.md Laravel 13 override

**Wave 2** *(blocked on Wave 1 completion)*

- [x] 01-02-PLAN.md — Business schema: 9 migrations (clients, piscines, produits, contrats, passages, photos_meta, factures, signatures, diagnostics) + Eloquent models + factories + env-gated DevDataSeeder (D-07, D-08, D-09)
- [x] 01-03-PLAN.md — Vitrine pages + SEO: home/services/realisations/contact-shell/legal pages transposed 1:1 from mockups + LocalBusiness JSON-LD + sitemap.xml + OG/meta (SITE-01, SITE-02, SITE-03, SITE-06, SITE-07)
- [x] 01-04-PLAN.md — Blog markdown-in-repo + Contact Livewire form: routes /blog, /blog/{slug}, /contact + honeypot + rate-limit 5/min + Mailgun EU (SITE-04, SITE-05)
- [x] 01-05-PLAN.md — Auth Fortify + admin shell: /login transposed from mockups/v1/auth.html + /admin dashboard stub from dashboard.html + PierreSeeder + greyed Phase 2/3 nav (AUTH-01, D-17..D-20)

**Wave 3** *(blocked on Wave 2 completion)*

- [x] 01-06-PLAN.md — Cutover gate: CacheHeaders middleware + Lighthouse/Schema.org/OG/Mailgun/login validation + CUTOVER.md playbook + Zyro URL inventory + DNS switch handoff (SITE-07 final gate)

**UI hint**: yes
**Walking Skeleton**: yes (greenfield + MVP — SKELETON.md committed alongside PLAN files)

### Phase 2: MVP Suivi Offline-First

**Goal**: L'opérateur saisit un passage sur le terrain sans réseau, les données se synchronisent à la reconnexion sans doublon, et le client consulte son historique via magic link.
**Mode:** mvp
**Depends on**: Phase 1
**Requirements**: AUTH-02, AUTH-03, AUTH-04, CLI-01, CLI-02, CLI-03, PASS-01, PASS-02, PASS-03, PASS-04, PASS-05, PASS-06, PORT-01, PORT-02
**Success Criteria** (what must be TRUE):

  1. Le pro crée/modifie une fiche client avec piscine et la retrouve via recherche/filtre
  2. Le pro saisit mesures + actions + notes + photos sur iPhone sans réseau et voit le badge « N passages en attente »
  3. À la reconnexion, les passages se synchronisent sans doublon (idempotence `client_uuid`) et les photos passent via file résiliente
  4. Le client reçoit un magic link, se connecte et consulte l'historique de ses passages avec mesures et photos en lecture seule
  5. La PWA est installable en Home Screen avec `storage.persist()` actif (résistance à l'éviction iOS)

**Plans**: TBD
**UI hint**: yes

### Phase 3: Facturation & Odoo

**Goal**: L'opérateur génère des factures conformes (franchise en base de TVA — mention « TVA non applicable, art. 293 B du CGI », numérotation séquentielle CGI), les pousse vers Odoo ou en CSV, et le client signe le passage sur le téléphone du pro.
**Mode:** mvp
**Depends on**: Phase 2
**Requirements**: FACT-01, FACT-02, FACT-03, FACT-04, FACT-05, FACT-06, FACT-07
**Success Criteria** (what must be TRUE):

  1. Le POC Odoo est terminé : le mode API ou CSV est déterminé et configuré via `ODOO_MODE`
  2. Le pro gère un catalogue produits/services et des contrats ponctuel/forfait
  3. Le pro génère une facture en franchise de TVA (mention « TVA non applicable, art. 293 B du CGI », pas de colonne TVA) avec numérotation séquentielle sans trou (conforme CGI art. 242 nonies A)
  4. La facture est poussée vers Odoo (XML-RPC) ou exportée en CSV selon le plan ; le statut de paiement s'affiche côté client
  5. Un PDF de compte-rendu est généré et le client peut signer électroniquement sur le téléphone du pro

**Plans**: TBD

### Phase 4: Notifications

**Goal**: L'opérateur et le client reçoivent les communications automatisées (compte-rendu après passage, rappel J-1) par email, avec option WhatsApp.
**Mode:** mvp
**Depends on**: Phase 3
**Requirements**: NOTIF-01, NOTIF-02, NOTIF-03
**Success Criteria** (what must be TRUE):

  1. Le client reçoit le compte-rendu PDF par email après la clôture d'un passage
  2. Le pro et le client reçoivent un rappel email la veille d'un passage planifié
  3. L'opération peut activer l'envoi du compte-rendu et du rappel via WhatsApp (template Business approuvé)

**Plans**: TBD

### Phase 5: Diagnostic Commercialisable

**Goal**: Un visiteur ou client utilise le wizard « eau verte » pour obtenir un plan d'action chiffré avec doses calculées côté serveur, après acceptation d'un disclaimer légal, et peut accéder aux fonctionnalités premium via Stripe.
**Mode:** mvp
**Depends on**: Phase 2
**Requirements**: DIAG-01, DIAG-02, DIAG-03, DIAG-04, DIAG-05
**Success Criteria** (what must be TRUE):

  1. Le disclaimer légal s'affiche sur le 1er écran du wizard avant tout conseil de dosage ; l'utilisateur doit l'accepter explicitement
  2. Le wizard produit un plan d'action avec doses calculées côté serveur (jamais en JS exposé) selon le volume du bassin
  3. Un abonné Stripe (piste A) ou un client premium (piste B) accède aux fonctionnalités avancées du diagnostic
  4. L'utilisateur peut consigner plusieurs mesures dans le temps et consulter leur évolution

**Plans**: TBD

## Progress

**Execution Order:**
Les phases s'exécutent dans l'ordre numérique : 1 → 2 → 3 → 4 → 5 (Phase 5 dépend uniquement de Phase 2 et peut démarrer en parallèle de 3-4 si besoin).

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Vitrine & Fondations | 9/6 | Complete   | 2026-05-28 |
| 2. MVP Suivi Offline-First | 7/7 | Complete   | 2026-05-28 |
| 3. Facturation & Odoo | 0/? | Not started | - |
| 4. Notifications | 0/? | Not started | - |
| 5. Diagnostic Commercialisable | 0/? | Not started | - |

## Backlog

### Phase 999.1: SEO launch-readiness & post-cutover optimization (BACKLOG)

**Goal:** Make the vitrine actually rank once it cuts over to `dloazurpiscines.com`. Source: full `/seo audit` of staging `dloazur-main-s8e8er.laravel.cloud` (2026-05-29). Staging is `noindex` today, so these are launch-gated, not live. See memory `seo-cutover-gotchas`.
**Requirements:** TBD
**Plans:** 0 plans

Plans:
- [ ] TBD (promote with /gsd:review-backlog when ready)

**Findings (by severity):**

CRITICAL — cutover blockers (overlap with Phase 1 cutover carry-overs):
- [ ] Production env must emit NO `x-robots-tag: noindex` (staging noindex is env-driven; if prod inherits it the live site is invisible). Verify `curl -I https://dloazurpiscines.com`.
- [ ] `public/robots.txt` has literal `Sitemap: ${APP_URL}/sitemap.xml` (uninterpolated) — fix via route/`url()` or hardcode.
- [ ] `/mentions-legales` SIRET/RCS show `[À compléter par Pierre ADAM avant lancement]` — Pierre supplies 14-digit SIRET.
- [ ] Canonical + og:url + schema `url`/`@id`/`image` hardcoded to staging host — drive from `config('app.url')`, set `APP_URL=https://dloazurpiscines.com` in prod.
- [ ] No Google Business Profile (`sameAs: []`) — create GBP (SAB/service-area, hide address, categories Pool cleaning service + Swimming pool contractor), link in `sameAs`, seed 5 reviews. Highest local ROI, zero-dev.

HIGH:
- [ ] Schema `@type: Plumber` wrong → `["LocalBusiness","HomeAndConstructionBusiness"]`; add email, streetAddress, postalCode, founder (Pierre), hasOfferCatalog, AggregateRating.
- [ ] No customer reviews anywhere — solicit 5–10 Google reviews, display with rating schema.
- [ ] `/realisations` thin (~108–145 words) — add 2–3 written case studies (commune, problem, protocol, before/after params).
- [ ] `/services/eau-verte-urgence` only 222 words vs SERP norm 1500–3000 — expand to real guide (causes, DIY checklist, when to call pro, 5-step protocol, FAQ).
- [ ] Page-type mismatch: single-URL vitrine for transactional + urgency + recurring intents — add dedicated service/city pages.
- [ ] Missing security headers (HSTS, X-Content-Type-Options, X-Frame-Options/CSP frame-ancestors, Permissions-Policy).

MEDIUM:
- [ ] Add FAQ content to Services + eau-verte (PAA questions) — copy value over rich-result value (commercial site, post-Aug-2023).
- [ ] Add 3–4 city-slug pages (Fort-de-France, Le Lamentin, Schoelcher, Les Trois-Îlets), 400+ unique words each. Quality gate: keep to a handful, no thin doorway pages.
- [ ] Serve images as WebP/AVIF (14/15 are .jpg; hero is also og:image).
- [ ] Add price-range signal ("à partir de X€ / devis selon volume").
- [ ] Blog posts dated placeholder `2025-01-01` — set real dates; add `lastmod` to static sitemap URLs.
- [ ] Add `BreadcrumbList` on sub-pages; fix `og:type: website` → `article` on blog articles.
- [ ] "Espace client" in primary nav reads as member-gate to first-timers — demote; promote "Demander un devis" CTA.

LOW:
- [ ] Add `llms.txt` (currently 404; AI crawlers are allowed).
- [ ] Add `twitter:card`, `og:image:width/height` (WhatsApp share previews = conversion channel).
- [ ] Replace generic AI-filler paragraph with Martinique-specific copy.
- [ ] Cite operator credentials/years of experience.
- [ ] Add before/after video to eau-verte page.

**Health score at audit:** 61/100 (cutover-readiness; live = 0 while noindex). Perf excellent warm (TTFB ~100ms).
