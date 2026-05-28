---
phase: 01-vitrine-fondations
plan: 01
status: complete
completed_at: 2026-05-28T13:30:00Z
commits: 8
requirements:
  - SITE-01 (skeleton level — full home page lands in Plan 03)
  - AUTH-01 (auth shell scaffolded — login UX lands in Plan 05)
---

# Plan 01-01 — Walking Skeleton + Scaffold — SUMMARY

## What was built

A complete Laravel 13 + Tailwind v4 + Pest 4 vertical slice, deployed to Laravel Cloud staging at **https://dloazur-main-s8e8er.laravel.cloud** with `/up` returning `{"app":"ok","db":"ok"}` against a managed Postgres 17 cluster.

The smallest reachable shape of the production stack: visitor → DNS → Laravel Cloud edge (EU/Frankfurt) → router → Blade view + Vite-compiled OKLCH tokens → Postgres ping → 200 OK. Every later plan extends this without re-litigating any of the architectural decisions baked in here.

## Key deliverables

- **Laravel 13 scaffold** (`composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `bootstrap/app.php`, `artisan`, `config/`, full Phase 1 dependency set installed upfront so Wave 2 parallels never touch lockfiles)
- **Tailwind v4 `@theme` block** (`resources/css/app.css`) — every OKLCH token from `mockups/v1/theme.js` transposed verbatim (azure/navy/lagon/sun/sand/ink ramps + Fredoka/Inter + spacing-13/15/18 + breakpoint-xs)
- **Blade layout shells** — `layouts/app.blade.php` (vitrine), `layouts/admin.blade.php`, `layouts/auth.blade.php`, `layouts/blog.blade.php` + brand-locked components (`cta-whatsapp`, icon set, `seo/meta`)
- **Route partition wiring** — `routes/{web,vitrine,blog,admin}.php` registered via `bootstrap/app.php` so Plans 03/04/05 only fill stubs, never touch the bootstrap
- **Health-check controller** — `GET /up` → `{"app":"ok","db":"ok"|"fail"}` with HTTP 200/503 (T-1-07-aware: no driver/version leak)
- **CI workflow** — `.github/workflows/tests.yml` (Pest 4, Postgres 17 service, PHP 8.4, Node 22)
- **Tests** — `SkeletonSmokeTest`, `HealthCheckTest`, `TailwindTokensTest` (18 assertions, all green locally)
- **Cloud provisioning** — Laravel Cloud project, Neon-managed Postgres 17 attached, app live at the vanity URL; `CLOUD-PROVISIONING.md` documents every dashboard + API step
- **Doc alignment** — `CLAUDE.md` + `.planning/PROJECT.md` + `.planning/phases/01-vitrine-fondations/01-CONTEXT.md` updated to reflect the Laravel 13 + Tailwind v4 + Postgres 17 + PHP 8.4 reality

## Commits (8 total)

| Commit | Type | Scope |
|--------|------|-------|
| `c77e449` | feat | Bootstrap Laravel 13 scaffold + Phase 1 Composer + npm packages |
| `1208b47` | test | RED — failing tests for Tailwind v4 @theme tokens |
| `1ca9246` | feat | GREEN — Tailwind v4 @theme tokens + Vite config (Fredoka/Inter via Bunny) |
| `5a5bf6e` | test | RED — failing smoke + health-check tests |
| `c619e36` | feat | GREEN — Blade layouts + components + routes + HealthController |
| `7f94714` | docs | CI workflow + env templates + CLAUDE.md/PROJECT.md Laravel 13 alignment + CLOUD-PROVISIONING.md |
| `379efe2` | chore | STATE.md = executing phase 01; drop stale top-level `branching_strategy` key |
| `bada2fb` | chore | Harden `.gitignore`: `.env*` with bang-overrides for `.env.example` + `.env.testing` |

(+ a CI-fix + Postgres 17 alignment commit lands with this SUMMARY — see below.)

## Deviations from plan

| # | Type | Plan said | Reality | Resolution |
|---|------|-----------|---------|------------|
| 1 | Pest install | `composer require pestphp/pest:^4.7` | Conflict with phpunit 12.5.28 (Pest 4.7 needs ≤12.5.24) | `composer require --dev --with-all-dependencies pestphp/pest:^4.7 ... phpunit/phpunit:^12.5` — installs both in lockstep |
| 2 | Pest install | `php artisan pest:install` | Command does not exist in Pest 4 | Manually authored `tests/Pest.php`; switched `RefreshDatabase` from suite-wide to opt-in (sqlite `:memory:` nested-tx error otherwise) |
| 3 | Vite fonts | Default `bunny('Instrument Sans')` | UI-SPEC mandates Fredoka + Inter | `bunny('Fredoka', [600, 700])` + `bunny('Inter', [400, 600])` — UI-SPEC 2-weights-per-family lock |
| 4 | Test assertion | `assertSee('resources/css/app.css')` | `@vite` rewrites to `/build/assets/app-XXXX.css` | Regex assertion for the Vite-emitted build path — matches plan intent |
| 5 | Postgres version | `PostgreSQL 16` (D-02 lock + CI + Plan task language) | Laravel Cloud Neon offers 17 or 18 only | **Postgres 17** chosen (mature, EOL 2029-11). CI image bumped `postgres:16` → `postgres:17`; CLAUDE.md + PROJECT.md + CLOUD-PROVISIONING.md amended. Older planning artifacts (RESEARCH/CONTEXT/SKELETON/this PLAN.md) left as planning-time snapshots — divergence is recorded here, not retroactively rewritten. |
| 6 | PHP version | `8.3` (Plan task + CI workflow) | composer.lock pins Symfony v8 + Spatie packages at PHP 8.4 floor | CI bumped `php-version: 8.3` → `8.4`. Laravel Cloud runtime is PHP 8.5 (further ahead, safe). |
| 7 | Postgres attach | Dashboard wizard auto-attaches resource to env on creation | Resource was created but never linked to env → only `APP_KEY` was injected; runtime fell back to `sqlite` default → `/up` returned `db:fail` while migrations had run against an ephemeral build-container sqlite | Diagnosed via Laravel Cloud REST API (`/api/databases` returned the cluster but `/api/environments/{env}/databases` returned `null`). Set all 13 env vars via `POST /api/environments/{env}/variables` (`method: append`); triggered a deploy via `/stop` + `/start`. `/up` flipped to `{"app":"ok","db":"ok"}` on the next deploy. |

## Verification (must_haves.truths)

| Truth | Status | Evidence |
|-------|--------|----------|
| Laravel Cloud staging URL serves home page over HTTPS with brand styling visible | ✅ | `curl https://dloazur-main-s8e8er.laravel.cloud/` returns 200 with `oklch(…)` inline styles, `azure-500` brand class, `wa.me/596696940054` CTA, `theme-color #0080ff`, Fredoka loaded |
| `/up` returns `{app: ok, db: ok}` on staging — proves Postgres alive end-to-end | ✅ | `curl https://dloazur-main-s8e8er.laravel.cloud/up` → HTTP 200, JSON `{"app":"ok","db":"ok"}` (verified post-deploy `depl-a1e3429a-d737-4545-90c5-e71828212d3d`) |
| CLAUDE.md + PROJECT.md + 01-CONTEXT.md reflect Laravel 13.x | ✅ | `grep -q 'Laravel 13' CLAUDE.md && grep -q 'Laravel 13' .planning/PROJECT.md && grep -q 'Laravel 13' .planning/phases/01-vitrine-fondations/01-CONTEXT.md` — all pass |
| CI workflow tests.yml runs on push and turns green within 5 minutes | ⏳ pending re-run | First two CI runs failed on PHP 8.3 / composer.lock PHP 8.4 floor (see Deviation #6). Workflow fixed in this commit; CI re-runs on push. |

## Post-deploy TODOs (carried into Plan 01-06 cutover gate)

| # | TODO | Why | Trigger |
|---|------|-----|---------|
| 1 | **Rotate Postgres password** (Laravel Cloud → Database `dloazur` → Reset password) | Password was surfaced in this conversation's API response inspection — Anthropic retains transcripts. Laravel Cloud auto-re-injects the new password into env vars on rotate. | Before any real client data lands (latest: end of Phase 2 cutover) |
| 2 | **Rotate Laravel Cloud API token** | Same risk surface — second token (`3001\|WvGD…`) also briefly exposed via shell-pipe parsing error before parser was hardened. | Immediate (recommended) |
| 3 | **Set R2_*, BREVO_*, OPERATOR_*, CONTACT_RECIPIENT env vars** | Currently only `APP_*` + `DB_*` + `LOG_CHANNEL` + `MAIL_MAILER=log` are set. R2 + Brevo provisioning is still required (CLOUD-PROVISIONING.md §2 + §3) before Plan 04 contact form can actually send mail and before Phase 2 photo uploads can land. | Plan 04 startup (Brevo) + Phase 2 startup (R2) |
| 4 | **Flip `MAIL_MAILER` from `log` to `brevo`** | Currently emails write to the Laravel Cloud log channel (safe default while Brevo DNS is pending Active). | Once Brevo DKIM+SPF+DMARC verified Active on `dloazurpiscines.com` |
| 5 | **Upgrade Postgres Dev → Prod tier + 7d PITR** | Dev tier has no backups + hibernates after 300s. Acceptable for build phase; must upgrade before D-25 DNS cutover. | Plan 01-06 (cutover gate) — before flipping DNS |
| 6 | **Validate amendment hygiene of older planning artifacts** | This SUMMARY is the canonical record of Postgres 16→17 + PHP 8.3→8.4 divergence. RESEARCH.md, CONTEXT.md, SKELETON.md still mention 16/8.3 verbatim — left as planning-time snapshots. | Phase 1 verifier (Plan 06) — confirm divergence is OK or backfill amendments |

## Self-Check

- [x] Walking Skeleton live on production-grade URL with brand identity intact
- [x] `/up` returns DB-alive signal — no fake passes, evidence captured above
- [x] Every plan-declared `files_modified` exists on disk (verified via `git log` + `git ls-files`)
- [x] Project-canonical docs (CLAUDE.md, PROJECT.md, CLOUD-PROVISIONING.md) reflect actual stack
- [x] All 8 plan commits atomic + green-on-green TDD cycle for Tasks 3a/3b
- [ ] CI green — pending re-run on the PHP 8.4 / Postgres 17 fix (commit lands with this SUMMARY)
- [x] Post-deploy TODOs surfaced for Plan 01-06 cutover gate (table above)

## Next

Wave 2 unblocked: Plans **01-02** (schema + models), **01-03** (vitrine pages), **01-04** (blog + contact + Google Reviews) can run in parallel — no `files_modified` overlap (verified via orchestrator pre-flight check).
