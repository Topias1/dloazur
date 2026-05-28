# Walking Skeleton — Dlo Azur Piscines

**Phase:** 1 (vitrine-fondations)
**Generated:** 2026-05-28
**Mode:** MVP + WALKING_SKELETON

## Capability Proven End-to-End

> The smallest user-visible capability that exercises the full stack:

**A visitor browsing `https://preprod.dloazurpiscines.com/` sees a Blade-rendered home page styled with the Dlo Azur design tokens (OKLCH azure/navy/sand, Fredoka + Inter), served by Laravel 13 running on Laravel Cloud EU/Frankfurt, with a PostgreSQL connection alive (`/up` health check returns DB ping OK) and the full schema migrated (all 10 business tables created, even if empty).**

This proves: scaffold ✓, routing ✓, view rendering ✓, design tokens compiled by Vite ✓, DB connection ✓, migrations applied ✓, deployment pipeline ✓.

## Architectural Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Framework | **Laravel 13.x** (stack override from CLAUDE.md's Laravel 11.x) | Laravel 11 EOL 2026-03-12 (passé) ; Laravel 13 security until 2028-03-17. All locked packages (Fortify, Livewire 3, spatie/medialibrary, Pest 4) support 11/12/13 indifferently. PHP 8.3 sweet spot. Decision must update CLAUDE.md + PROJECT.md. |
| Reactive UI | **Livewire 3.x** | D-01 lock. Server-rendered, no JS framework. NOT used for Phase 2 offline-first capture (Alpine pure). |
| Micro-interactivity | **Alpine.js 3.x** | D-01 lock. Tab toggle on /login, mobile nav toggle. Phase 2 will use Alpine for PWA IndexedDB controller. |
| CSS | **Tailwind 4.x CSS-first** (`@theme` in `resources/css/app.css`, NOT `tailwind.config.js`) | Tailwind v4 (Jan 2025) deprecated JS config. RESEARCH.md Pitfall #2 documents this override of D-05's wording. Result is functionally identical. |
| Database | **PostgreSQL 16** (Laravel Cloud Neon-managed serverless, .25-4 compute units, pgbouncer included) | D-02 lock. Neon scale-to-zero, cold-wake "few hundred ms". |
| Auth (pro) | **laravel/fortify ^1.37** (headless, custom Blade views) | D-03 lock. No registerUsers, no emailVerification, no 2FA in Phase 1 (Pierre is sole seeded user). Forgot password active. |
| Auth (client magic link) | Deferred to **Phase 2** | AUTH-02 is Phase 2 scope per REQUIREMENTS.md traceability. `cesargb/laravel-magiclink` installs in Phase 2. |
| Test runner | **Pest 4.x** + `pestphp/pest-plugin-laravel ^4.1` | D-04 lock. Browser tests available for Phase 2. |
| Hébergement | **Laravel Cloud EU Central (Frankfurt)** with auto-deploy on push main, build commands cache config/route/view, deploy commands run `php artisan migrate --force` + `db:seed --class=PierreSeeder --force` | D-02, D-30. Scale-to-zero accepted on staging; production should keep min instance warm to satisfy D-22 Lighthouse ≥ 90. |
| Object storage | **Scaleway Object Storage fr-par (Paris)**, S3-compatible disk, `use_path_style_endpoint=true`, endpoint `https://s3.fr-par.scw.cloud` | D-02 lock. **Wired in Phase 1, no uploads until Phase 2.** Buckets `dlo-azur-piscines-prod` + `dlo-azur-piscines-staging`, prefix convention `passages/{passage_uuid}/photos/`. |
| Mail driver | **Brevo (EU region)**, ``, `MAIL_MAILER=brevo` | D-15 + RESEARCH §Mail Driver Choice. Cheapest EU-compliant option for low volume contact form (~50/mo). DNS verify for `dloazurpiscines.com` blocks first send — handled in Plan 06 cutover. |
| Routing partition | `routes/web.php` (root + redirects), `routes/vitrine.php` (public pages), `routes/blog.php` (blog), `routes/admin.php` (auth-protected) | Avoids merge conflicts when later phases extend specific surfaces. Registered via `bootstrap/app.php` `->withRouting(web: ...)` |
| Directory layout | Standard Laravel 13 + `app/Support/` for utilities (`BlogRepository`, `SchemaOrg` builders), `app/Livewire/` for components, `resources/content/blog/` for markdown articles | Mirrors RESEARCH §Recommended Project Structure. Models in French (`Passage`, `Facture`, `Piscine`) per RESEARCH §Postgres Naming. |
| Timezone | `America/Martinique` (UTC-4, no DST) in `config/app.php` | RESEARCH Pitfall #9 |
| CI | **GitHub Actions** `.github/workflows/tests.yml` PHP 8.3 + Node 22 + Postgres 16 service + Pest --ci + `npm run build` | D-29 |
| Branch strategy | `feature/*` → PR `main` → merge auto-deploys Laravel Cloud staging | D-31 |

## Stack Touched in Phase 1

- [x] **Project scaffold** — `composer create-project laravel/laravel:^13.0` in `/tmp` then rsync into the planning-bearing repo (Pattern 7), `composer install`, `npm install`
- [x] **Routing** — `/` (home Blade), `/sitemap.xml` (dynamic), `/login` (Fortify), `/admin` (auth middleware)
- [x] **Database** — All 10 business migrations applied (users, clients, piscines, passages, photos_meta, produits, contrats, factures, signatures, diagnostics) + `users` seeded with Pierre (one row, idempotent via `updateOrCreate`)
- [x] **UI** — Vitrine home renders with OKLCH design tokens, Fredoka + Inter loaded, WhatsApp CTA functional (links to `wa.me/596696940054`), Livewire contact form submits to mail driver
- [x] **Deployment** — Laravel Cloud EU Central project provisioned, auto-deploy on push `main`, environment URL accessible (e.g., `dloazur-staging.laravel.cloud` or `preprod.dloazurpiscines.com`)

## Out of Scope (Deferred to Later Slices)

> Anything that is *not* in the skeleton. Explicit list prevents future phases re-litigating Phase 1's minimalism.

**Phase 2 — MVP Offline-First (deferred):**
- PWA / Service Worker / `vite-plugin-pwa` / IndexedDB / Background Sync
- Magic link client auth (`cesargb/laravel-magiclink`)
- Client CRUD UI, Pool CRUD UI
- Passage capture form (offline Alpine controller)
- Photo upload queue, `spatie/laravel-medialibrary` actual usage
- Portail client UI
- All `/api/v1/*` endpoints

**Phase 3 — Facturation (deferred):**
- POC Odoo / `obuchmann/odoo-jsonrpc`
- `spatie/laravel-pdf` runtime usage (only `dompdf/dompdf` installed in Phase 1 to validate stack)
- Stripe / `laravel/cashier`
- Signature electronic capture
- Invoice numbering CGI sequence

**Phase 4 — Notifications (deferred):**
- Scheduled jobs for J-1 reminders
- WhatsApp Business API templates

**Phase 5 — Diagnostic (deferred):**
- Wizard "ma piscine est verte"
- Server-side dose calculations
- Stripe subscription gating

**Explicitly not Phase 1 (per CONTEXT.md `<deferred>`):**
- Blog tags/categories (D-11)
- DB-backed blog admin (D-10 alternative)
- Contact submissions persisted in DB (D-13)
- Google Reviews widget (D-28)
- Full-page response cache (RESEARCH Pitfall 11)
- 2FA for Pierre
- Email verification
- Public user signup (Fortify `registerUsers` feature disabled)

## Subsequent Slice Plan

Each later phase adds vertical slices on top of this skeleton without altering its architectural decisions:

- **Phase 2** — Vertical slice: pro logs in → creates client → adds piscine → captures passage offline on iPhone → photos sync queue → client receives magic link → views history. Adds PWA layer, Alpine offline controller, REST `/api/v1/passages` for resilient sync, Livewire CRUD for clients/passages backoffice. **No skeleton decision changes.**
- **Phase 3** — Vertical slice: pro generates facture from passage → numero séquentiel CGI → PDF compte-rendu → push Odoo (or CSV) → client signs on phone. Adds Cashier (for FACT-04 Stripe-adjacent if needed), `obuchmann/odoo-jsonrpc`, `spatie/laravel-pdf`. **No skeleton decision changes.**
- **Phase 4** — Vertical slice: passage closed → email compte-rendu sent → J-1 reminder scheduled → optional WhatsApp template. Adds Laravel scheduler, WhatsApp Business API SDK. **No skeleton decision changes.**
- **Phase 5** — Vertical slice: visitor opens diagnostic wizard → accepts disclaimer → answers questions → server computes doses → Stripe gate for premium plan. Adds Stripe webhooks, wizard Livewire flow. **No skeleton decision changes.**
