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
- [ ] **Phase 5: Diagnostic Commercialisable** - Diagnostic gratuit (symptôme + « déjà tenté ? » + doses serveur), escalade WhatsApp contextualisée → leads qualifiés (hybride ; Stripe + carnet différés V2)
- [x] **Phase 6: Blog admin CRUD** - Autonomie de publication pour Pierre (`/admin/blog`) — modèle Post + DB, CRUD, éditeur Markdown, migration des 3 articles, préservation SEO 999.1 (completed 2026-05-30)

**Retours Pierre (post-démo 2026-06-03) — 4 phases par layer, indépendantes** (réf `.planning/feedback/pierre-2026-06-03-reponses.md`) :

- [ ] **Phase 7: Espace admin — agenda, récap chimie & fix notes internes** - FIX bug `notes_privees` perdu à la synchro (Task 1) + agenda du jour + pivot `passage_produit` + récap mensuel par client *(priorisée : contient le bug de perte de donnée)*
- [ ] **Phase 8: Vitrine — corrections retours Pierre** - Géo honnête + voix marque 3e personne + dé-duplication ; avant/après & page Dépannage (décisions Antoine)
- [ ] **Phase 9: Espace client — finitions retours Pierre** - Section « Mes documents » teaser cohérent Phase 3, test de régression historique dépliable, nits a11y/perf
- [ ] **Phase 10: Diagnostic — fidélité au proto** - Retirer l'écran « mode » initial, entrer direct dans l'arbre symptôme (garder `$mode` serveur, adapter les tests)

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

**Goal**: Un visiteur (anonyme ou client) au persona « compétent mais bloqué » lance un diagnostic **gratuit** (parcours symptôme + « qu'as-tu déjà essayé ? » + wizard chimie à doses serveur), reçoit un plan d'action **sûr**, et — quand le cas dépasse le DIY ou échoue au re-test — est redirigé **en un geste vers Pierre (WhatsApp à contexte riche)**. **Objectif n°1 : générer des leads qualifiés** (hybride A+B, arbitré par l'expert). Stripe et l'historique multi-mesures différés en V2.
**Mode:** mvp
**Scope:** hybride (voir `phases/05-.../05-EXPERT-ARBITRATION.md`) — briques 1-7 incl. **carnet local-only** (front, 0 infra) ; **V2 différé** = push, carnet **synchronisé**/courbes, multi-bassins, espace Pierre, natif. Contrainte directrice : **infra mini × whaou maxi**.
**Depends on**: Phase 2
**Requirements**: DIAG-01, DIAG-02, DIAG-03, DIAG-06, DIAG-07  *(DIAG-04 Stripe + DIAG-05 carnet synchronisé/courbes → différés V2)*
**Success Criteria** (what must be TRUE):

  1. Le disclaimer légal s'affiche avant tout conseil de dosage ; acceptation explicite requise (DIAG-03)
  2. Le plan produit des doses calculées **côté serveur** (jamais en JS exposé), prudentes/plafonnées, selon le volume (DIAG-02)
  3. Le diagnostic est **conscient des actions déjà tentées** (un geste raté n'est jamais re-proposé ; oriente vers chlore-lock/métaux/calcaire) + affiche un **indice de confiance**
  4. L'**escalade contextualisée en un geste** vers Pierre (WhatsApp pré-rempli : symptôme, mesures, actions tentées, diagnostic, photo) se déclenche au pic d'intention (hors-DIY préemptif / re-test échoué réactif)
  5. **Route `/diagnostic` publique indexée** (vitrine + `/services/eau-verte-urgence` + pages communes) + capture de lead + PDF téléchargeable
  6. **Carnet local-only** (DIAG-07) : l'historique des diagnostics/mesures est conservé **sur l'appareil** (0 serveur/0 sync), consultable, et permet de reprendre/re-tester

**Plans:** 6/6 plans executed *(code livré, tests verts ; lancement gaté sur sign-off chimie/légal de Pierre — Task 3 du 05-06)*

Plans:

**Wave 1** *(parallel — no shared files)*

- [x] 05-01-PLAN.md — Brick 1: public indexed `/diagnostic` route + brand landing (S1) + symptom decision-tree config (floculant filter-type sub-branch, green-1 stabilisant leaves, action-aware leaves) + symptom flow (S2) + inline disclaimer gate (S4) + Wave-0 Pest stubs + DecisionTreeTest (DIAG-01, DIAG-03, Req9)
- [x] 05-02-PLAN.md — Brick chemistry brain (TDD): server-side `DoseEngine` (pure) + versioned `diagnostic-formulas` config + DoseEngineTest, expert-audited P0/P1 chemistry, formulas never in client JS (DIAG-02)

**Wave 2** *(blocked on Wave 1)*

- [x] 05-03-PLAN.md — Brick 3: 2-step chemistry wizard (S3) + action-aware « qu'as-tu déjà essayé ? » + lead columns migration + computeAndPersist (DoseEngine call + server disclaimer enforcement + persistence) + lead capture (S7) + DiagnosticLead mailer + basic WhatsApp hand-off (DIAG-01, DIAG-02, DIAG-03, DIAG-06)

**Wave 3** *(parallel — no shared files; blocked on Wave 2)*

- [x] 05-04-PLAN.md — Bricks 4+5: contextualized escalation engine (S6, preemptive+reactive) + rich-context WhatsApp builder + confidence index (S5) + over-escalation guard + guarded PDF download link (DIAG-06)
- [x] 05-05-PLAN.md — Brick 2 delivery: session-gated synchronous DomPDF report (S8) + `/diagnostic/{id}/pdf` route + DiagnosticPdfTest + DiagnosticRouteTest (Req8, Req9, D-06 enumeration gate)

**Wave 4** *(blocked on Wave 3)*

- [x] 05-06-PLAN.md — Bricks 6+7: light in-session re-test loop (feeds reactive escalation, no push) + carnet local-only on-device store (S9, IndexedDB/localStorage, 0 server/0 sync) + « Mes diagnostics passés » list + CarnetLocalTest (DIAG-07, DIAG-06) *(Task 3 launch-gate Pierre = pending)*

### Phase 6: Blog admin CRUD — autonomie de publication

**Goal:** Permettre à Pierre (non-dev) de créer/éditer/dépublier des articles de blog depuis `/admin/blog`, sans toucher au code ni à git. Aujourd'hui le blog est fichiers-Markdown (`resources/content/blog/*.md`) → publier exige commit+push. Cette phase introduit un modèle `Post` + migration Postgres, un CRUD admin (liste, créer, éditer, dépublier), un éditeur Markdown, et bascule `BlogRepository` de fichiers→DB en migrant les 3 articles existants. Doit préserver les acquis SEO de la phase 999.1 : `og:type=article`, Article JSON-LD, dates réelles, et entrées sitemap.
**Mode:** mvp
**Depends on:** Phase 999.1 (blog SEO : Article schema, og:type, sitemap, dates)
**Requirements**: SITE-07, CONTENT-01 *(dérivé au planning — autonomie de publication blog)*
**Success Criteria** (what must be TRUE):

  1. Pierre crée, édite et dépublie un article depuis `/admin/blog` sans toucher au code ni à git
  2. Le corps d'article est édité en Markdown (éditeur EasyMDE, stockage raw Markdown) ; cover image uploadée vers Scaleway S3 via medialibrary
  3. Les 3 articles existants sont migrés (slugs canoniques, dates réelles, `show_date`, auteur) sans régression SEO ; `BlogRepository` lit depuis la DB derrière un flag de cutover
  4. Un article publié est visible publiquement ; un brouillon ne l'est pas ; une URL dépubliée précédemment indexée renvoie 410, un slug jamais publié 404
  5. Les acquis SEO 999.1 sont préservés : `og:type=article`, Article JSON-LD, dates réelles, entrées sitemap (published only)

**Plans:** 4/4 plans complete

Plans:
- [x] 06-01-PLAN.md — Foundation: Post model + posts migration + scopePublished + slug auto-gen + idempotent PostMigrationSeeder + public parse() + phpunit BLOG_SOURCE lock [wave 1]
- [x] 06-02-PLAN.md — Public cutover: config/blog.php source flag + BlogRepository DB read path (cache-safe) + 410-vs-404 in BlogController + sitemap published-only; SEO 999.1 preserved [wave 2]
- [x] 06-03-PLAN.md — Admin shell: /admin/blog routes + PostController + PostIndex list (all statuses) + status-badge component + active sidebar nav + thin views [wave 2]
- [x] 06-04-PLAN.md — Editor: PostForm + EasyMDE (gated install) + cover→Scaleway S3 + slug-lock-on-publish + status toggle + inline-confirm unpublish + cache flush [wave 3]

### Phase 7: Espace admin — agenda, récap chimie & fix notes internes

**Goal:** Pierre dispose d'un **agenda du jour** qui le mène en un clic à la saisie d'un passage, ses **notes internes ne sont plus perdues**, et il sort un **récap mensuel par client** (passages + chimie consommée) — son cahier de facturation numérisé.
**Depends on:** Phase 2 (saisie passage offline) — indépendant des phases 8/9/10
**Requirements**: dérivé retours Pierre — admin-1, admin-2, admin-5
**Success Criteria** (what must be TRUE):

  1. **[Task 1 — BUG]** `notes_privees` **persiste à la synchro** (migration + `$fillable` + upsert) ET reste **invisible côté client** (test d'invariant vie privée). Aujourd'hui la note est saisie/envoyée/validée puis silencieusement effacée
  2. **Agenda du jour** admin : liste les piscines/clients à passer — **dérivé d'une fréquence/jour de passage** sur la piscine (zéro saisie de RDV pour un solo) → lien direct vers la saisie pré-remplie
  3. **Consommation chimie modélisée** : pivot `passage_produit` (produit, quantité, prix snapshot) + mini-sélecteur « produits utilisés » dans la saisie (**offline Alpine/IndexedDB, jamais Livewire**)
  4. Page **« Récap mensuel par client »** : nb passages + chimie consommée sur la période, prête à alimenter la facturation (Phase 3)

**Hors périmètre / notes :** notif notes internes = **pas de promesse de push** (iOS/Safari non fiable, device Pierre inconnu) → rappel à recadrer Phase 4. Lier les factures = Phase 3.
**Réf:** `.planning/feedback/pierre-2026-06-03-reponses.md` (admin-1, admin-2, admin-5)
**UI hint**: yes

Plans:
- [x] 07-01-PLAN.md — [admin-2] FIX bug notes_privees perdu à la synchro (migration + $fillable + upsert) + test invariant vie privée [wave 1]
- [ ] 07-02-PLAN.md — [admin-1] Agenda du jour dérivé de frequence_jour + flags « à revoir » → liens saisie pré-remplie [wave 2]
- [ ] 07-03-PLAN.md — [admin-5] Pivot passage_produit + sélecteur produits offline (Alpine/IndexedDB) + sync chimie [wave 2]
- [ ] 07-04-PLAN.md — [admin-5] Page récap mensuel par client (passages + chimie) + bouton facture inerte (teaser Phase 3) [wave 3]

### Phase 8: Vitrine — corrections retours Pierre

**Goal:** La vitrine raconte une géographie **honnête** et parle d'une **seule voix de marque** (3e personne), sans sur-répéter l'argument défensif « pas de call-center » — l'over-claim que Pierre dénonce disparaît.
**Depends on:** Phase 1 (vitrine) + branche `claude/pierre-feedback-website-app-A59QE` (épuration déjà validée) — indépendant des phases 7/9/10
**Requirements**: dérivé retours Pierre — V1, V5, V7, V12, V14, V6
**Success Criteria** (what must be TRUE):

  1. Le hero est en 3e personne (plus de « ma tournée ») et décrit la **vraie couverture** (Lorrain↔Vauclin côté Atlantique ; Schoelcher↔Rivière-Salée côté caraïbe via Lamentin) en invitant à appeler pour filtrer
  2. « toute la Martinique » est purgé des pages service (`entretien-recurrent`, `analyse-eau`)
  3. L'argument « pas de call-center » est réduit à ≤2 occurrences tournées positif, et « interlocuteur unique » est dé-dupliqué entre philosophie/engagements/pierre
  4. **[V5 — décision Antoine]** Carte Dépannage : page détail dédiée `/services/depannage` créée (cohérence + SEO local)
  5. **[V7 — décision Antoine]** Avant/après : **laissé tel quel** (pas de 2 photos avant/après dispo) — le vrai slider drag est différé jusqu'à ce que Pierre fournisse deux vraies photos. Hors scope Phase 8.

**Réf:** `.planning/feedback/pierre-2026-06-03-reponses.md` (V1, V5, V7, V12, V14, V6 + §Décisions discuss)
**UI hint**: yes

Plans:
- [ ] TBD (run /gsd-plan-phase 8 to break down)

### Phase 9: Espace client — finitions retours Pierre

**Goal:** Le portail client est cohérent et fiable : la section « Mes documents » assume clairement son statut de teaser branché à la facturation (Phase 3), l'historique dépliable est couvert par un test de régression, et les nits a11y/perf identifiés sont traités.
**Depends on:** Phase 2 (portail client) — dépendance fonctionnelle Phase 3 tracée pour « Mes documents » ; indépendant des phases 7/8/10
**Requirements**: dérivé retours Pierre — client-2, client-3 (+ nits client-1/client-4)
**Success Criteria** (what must be TRUE):

  1. **[client-3 — décision Antoine]** « Mes documents » garde le **teaser « Bientôt »** avec mention claire « branché à la facturation (Phase 3) » ; la dépendance Phase 3 est tracée
  2. **[client-2]** L'historique dépliable a un **test de régression automatisé** (aujourd'hui non couvert) + nits a11y (aria-controls/id reliant bouton et panneau)
  3. Nits perf optionnels du bandeau photo (client-4) : `<picture>` webp/avif + retirer `loading="lazy"` sur le hero au-dessus de la ligne de flottaison

**Réf:** `.planning/feedback/pierre-2026-06-03-reponses.md` (client-1..4 + §Décisions discuss)
**UI hint**: yes

Plans:
- [ ] TBD (run /gsd-plan-phase 9 to break down)

### Phase 10: Diagnostic — fidélité au proto

**Goal:** Le diagnostic entre **directement dans l'arbre symptôme** comme le proto de Pierre, sans l'écran « mode » initial (la fourche « Trouver mon problème » vs « Analyser mon eau ») qui contredisait « pas de choix au départ ».
**Depends on:** Phase 5 (diagnostic) — indépendant des phases 7/8/9
**Requirements**: dérivé retours Pierre — diag-1, diag-2
**Success Criteria** (what must be TRUE):

  1. **[diag-1/diag-2]** L'écran « mode » du wizard est retiré ; l'app entre direct sur l'arbre symptôme (`step:'tree'`, `nodeId:'start'`), « Analyser mon eau » relégué en action secondaire dans le parcours
  2. La **logique serveur `$mode`** (payload WhatsApp / `created_via`) est **conservée** — seul l'écran de choix disparaît
  3. Les tests Pest browser dépendant des `data-mode-*` sont adaptés avant suppression (la suite reste verte)

**Hors périmètre / notes :** monétisation = **diag gratuit lead magnet** (pas de paiement du rapport) — défaut acté, Stripe différé V2.
**Réf:** `.planning/feedback/pierre-2026-06-03-reponses.md` (diag-1, diag-2, diag-3 + §Décisions discuss)
**UI hint**: yes

Plans:
- [ ] TBD (run /gsd-plan-phase 10 to break down)

## Progress

**Execution Order:**
Les phases s'exécutent dans l'ordre numérique : 1 → 2 → 3 → 4 → 5 (Phase 5 dépend uniquement de Phase 2 et peut démarrer en parallèle de 3-4 si besoin).

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Vitrine & Fondations | 9/6 | Complete   | 2026-05-28 |
| 2. MVP Suivi Offline-First | 7/7 | Complete   | 2026-05-28 |
| 3. Facturation & Odoo | 0/? | Not started | - |
| 4. Notifications | 0/? | Not started | - |
| 5. Diagnostic Commercialisable | 6/6 | Code complet · lancement gaté Pierre |  |
| 6. Blog admin CRUD | 4/4 | Complete   | 2026-05-30 |

## Backlog

### Phase 999.1: SEO launch-readiness & post-cutover optimization (BACKLOG)

**Goal:** Make the vitrine actually rank once it cuts over to `dloazurpiscines.com`. Source: full `/seo audit` of staging `dloazur-main-s8e8er.laravel.cloud` (2026-05-29). Staging is `noindex` today, so these are launch-gated, not live. See memory `seo-cutover-gotchas`.
**Requirements:** SITE-07 (deepened beyond Phase 1 baseline — multi-type schema, content depth, service/city pages, image perf, BreadcrumbList, sitemap lastmod, llms.txt)
**Scope note:** Post-cutover GROWTH only. The 5 CRITICAL cutover-blockers moved to Phase 1's cutover checklist (D-07).
**Plans:** 6/6 plans complete

Plans:
- [x] 999.1-01-PLAN.md — Schema: rewrite LocalBusinessSchema to MultiTypedEntity (LocalBusiness+HomeAndConstructionBusiness) + founder + email; no rating/street (D-01..D-05) [wave 1]
- [x] 999.1-02-PLAN.md — Zyro content harvest: read-only crawl for body copy, services (incl. spa), testimonials, NAP, URL inventory + Pierre-fact gap list (D-08, D-09) [wave 1]
- [x] 999.1-05-PLAN.md — Image optimization: images:optimize Artisan command (spatie/image) + <x-picture> source-set component + generated .webp/.avif siblings [wave 1]
- [x] 999.1-03-PLAN.md — Service pages: BreadcrumbSchema + layout wiring + 3 new service pages (entretien-recurrent, analyse-eau, spa) + eau-verte expansion to 450w+ FAQ (D-10, D-11, D-13) [wave 2]
- [x] 999.1-04-PLAN.md — City hubs: 4 hand-written, fact-gated commune pages (Fort-de-France, Le Lamentin, Schoelcher, Les Trois-Îlets) linking city→service (D-12) [wave 3]
- [x] 999.1-06-PLAN.md — Discoverability: llms.txt + blog og:type/Article schema + real dates + sitemap lastmod/new URLs + realisations case studies + nav devis CTA [wave 4]

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

