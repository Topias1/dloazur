---
phase: 1
slug: vitrine-fondations
status: draft
nyquist_compliant: true
wave_0_complete: false
created: 2026-05-28
updated: 2026-05-28
---

# Phase 1 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest 4 (PHP) |
| **Config file** | `phpunit.xml` (généré par Laravel 13 scaffold) |
| **Quick run command** | `./vendor/bin/pest --filter={feature}` |
| **Full suite command** | `./vendor/bin/pest --ci` |
| **Estimated runtime** | ~30 seconds (Phase 1 footprint) |

---

## Sampling Rate

- **After every task commit:** Run `./vendor/bin/pest --filter={feature}`
- **After every plan wave:** Run `./vendor/bin/pest --ci`
- **Before `/gsd:verify-work`:** Full suite must be green + Lighthouse mobile ≥ 90 (perf/SEO/a11y) on staging
- **Max feedback latency:** 30 seconds (quick) / 90 seconds (full + Vite build)

---

## Per-Task Verification Map

> One row per automatable acceptance criterion across all 6 plans.
> Status starts at ⬜ pending; updated by executor to ✅ green / ❌ red / ⚠️ flaky as plans run.

### Plan 01-01 — Walking Skeleton + scaffold

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-01-T1 | 01-01 | 1 | SITE-01, AUTH-01 | — | Operator confirms Laravel 13 + Tailwind 4 override (informational) | manual | — (checkpoint:decision) | ❌ W0 | ⬜ pending |
| 01-01-T2 | 01-01 | 1 | SITE-01 | T-1-05 (supply chain) | Laravel 13 scaffold + all Phase 1 packages installed without slop | unit | `test -d vendor && test -f composer.lock && grep -q '"laravel/framework"' composer.json && php artisan --version \| grep -q 'Laravel Framework 13'` | ❌ W0 | ⬜ pending |
| 01-01-T3a | 01-01 | 1 | SITE-01, SITE-06 | T-1-07 | CSS @theme + Vite + tokens compile without errors | feature | `npm run build && grep -q '@theme' resources/css/app.css` | ❌ W0 | ⬜ pending |
| 01-01-T3b | 01-01 | 1 | SITE-01, AUTH-01 | T-1-01, T-1-07 | Skeleton smoke + health check return styled 200 + db:ok JSON | feature | `./vendor/bin/pest --filter='SkeletonSmokeTest\|HealthCheckTest'` | ❌ W0 | ⬜ pending |
| 01-01-T4 | 01-01 | 1 | SITE-01 | T-1-02, T-1-03 | CI workflow exists + env templates ready + CLAUDE/PROJECT/CONTEXT updated to Laravel 13 | unit | `test -f .github/workflows/tests.yml && grep -q "'8.3'" .github/workflows/tests.yml && grep -q 'Laravel 13' CLAUDE.md && grep -q 'Laravel 13' .planning/PROJECT.md && grep -q 'Laravel 13' .planning/phases/01-vitrine-fondations/01-CONTEXT.md` | ❌ W0 | ⬜ pending |
| 01-01-T5 | 01-01 | 1 | SITE-01, SITE-07 | T-1-03 | Laravel Cloud staging + Cloudflare R2 + Brevo sender provisioned (human-only) | manual | — (checkpoint:human-verify) | ❌ W0 | ⬜ pending |

### Plan 01-02 — Business schema (D-07/D-08/D-09)

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-02-T1 | 01-02 | 2 | SITE-01, SITE-04, AUTH-01 | T-2-04 | RED: MigrationsTest exists with 12 failing assertions covering D-07/D-08 | feature | `./vendor/bin/pest tests/Feature/MigrationsTest.php 2>&1; test $? -ne 0` | ❌ W0 | ⬜ pending |
| 01-02-T2 | 01-02 | 2 | SITE-01, SITE-04, AUTH-01 | T-2-01, T-2-04, T-2-06 | GREEN: 9 migrations + 9 models + env-gated DatabaseSeeder pass all 12 schema tests | feature | `./vendor/bin/pest tests/Feature/MigrationsTest.php --ci` | ❌ W0 | ⬜ pending |

### Plan 01-03 — Vitrine 1:1 + SEO + Sitemap

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-03-T1 | 01-03 | 2 | SITE-01, SITE-02, SITE-03, SITE-06, SITE-07 | T-3-01 | RED: HomePageTest+SeoTest+StaticPagesTest cover 24 assertions per UI-SPEC | feature | `./vendor/bin/pest tests/Feature/HomePageTest.php tests/Feature/SeoTest.php tests/Feature/StaticPagesTest.php 2>&1 \| grep -qE 'Tests:.+(failed\|errors)'` | ❌ W0 | ⬜ pending |
| 01-03-T2 | 01-03 | 2 | SITE-01, SITE-02, SITE-03, SITE-06, SITE-07 | T-3-01, T-3-03, T-3-07 | GREEN: vitrine 1:1 + LocalBusiness JSON-LD + sitemap.xml + meta/OG/canonical | feature | `./vendor/bin/pest tests/Feature/HomePageTest.php tests/Feature/SeoTest.php tests/Feature/StaticPagesTest.php tests/Feature/SkeletonSmokeTest.php --ci` | ❌ W0 | ⬜ pending |

### Plan 01-04 — Blog markdown + Contact form

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-04-T1 | 01-04 | 2 | SITE-04, SITE-05 | T-4-01, T-4-02, T-4-04 | RED: BlogTest+ContactFormTest cover 16 assertions (honeypot, throttle, markdown safe_mode) | feature | `./vendor/bin/pest tests/Feature/BlogTest.php tests/Feature/ContactFormTest.php 2>&1 \| grep -qE 'Tests:.+(failed\|errors)'` | ❌ W0 | ⬜ pending |
| 01-04-T2 | 01-04 | 2 | SITE-04, SITE-05 | T-4-01..T-4-10 | GREEN: blog index/show + ContactForm Livewire + Brevo (Paris, FR) + sitemap blog seam | feature | `./vendor/bin/pest tests/Feature/BlogTest.php tests/Feature/ContactFormTest.php tests/Feature/SeoTest.php --ci` | ❌ W0 | ⬜ pending |

### Plan 01-05 — Fortify auth + admin shell

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-05-T1 | 01-05 | 2 | AUTH-01 | T-5-01, T-5-04, T-5-09, T-5-12 | RED: 18 failing tests (login + admin shell + PierreSeeder + throttle) | feature | `./vendor/bin/pest tests/Feature/AuthLoginTest.php tests/Feature/AdminShellTest.php tests/Feature/PierreSeederTest.php 2>&1 \| grep -qE 'Tests:.+(failed\|errors)'` | ❌ W0 | ⬜ pending |
| 01-05-T2 | 01-05 | 2 | AUTH-01 | T-5-01..T-5-12 | GREEN: Fortify headless + Pierre seed idempotent + admin shell + greyed Phase 2 nav | feature | `./vendor/bin/pest tests/Feature/AuthLoginTest.php tests/Feature/AdminShellTest.php tests/Feature/PierreSeederTest.php --ci` | ❌ W0 | ⬜ pending |

### Plan 01-06 — Cutover gate

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-06-T1 | 01-06 | 3 | SITE-07 | T-6-02, T-6-03, T-6-06 | CutoverReadiness+Accessibility tests + CacheHeaders middleware + CUTOVER playbook | feature | `./vendor/bin/pest tests/Feature/CutoverReadinessTest.php tests/Feature/AccessibilityTest.php --ci` | ❌ W0 | ⬜ pending |
| 01-06-T2 | 01-06 | 3 | SITE-07 | T-6-01, T-6-05 | Lighthouse + sitemap + Schema.org + OG + real mail + login (human-only) | manual | — (checkpoint:human-verify; resume-signal in CUTOVER.md) | ❌ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `composer create-project laravel/laravel:^13 .` (workaround repo non-vide — voir RESEARCH.md §Bootstrap)
- [ ] `composer require pestphp/pest pestphp/pest-plugin-laravel --dev` + `php artisan pest:install`
- [ ] `tests/Pest.php` configuré avec `uses(RefreshDatabase::class)->in('Feature')`
- [ ] `tests/Feature/` créé avec stubs pour chaque REQ-ID
- [ ] `.github/workflows/tests.yml` (Pest CI baseline, PHP 8.3, Node 22)
- [ ] PostgreSQL local + `.env.testing` configurés

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Lighthouse mobile ≥ 90 (perf/SEO/a11y) | SITE-06 | Outil externe Chrome DevTools | Ouvrir `preprod.dloazurpiscines.com`, Lighthouse mobile, vérifier scores ≥ 90 |
| Sitemap XML accessible + valide | SITE-06 | Validation Google Search Console | `curl https://preprod.dloazurpiscines.com/sitemap.xml` + tester sur https://www.xml-sitemaps.com/validate-xml-sitemap.html |
| Structured data LocalBusiness valide | SITE-06 | Validator externe Schema.org | Tester sur https://validator.schema.org/ et Google Rich Results Test |
| OG preview correct (Facebook + WhatsApp) | SITE-06 | Pas testable en local | Debugger Facebook (https://developers.facebook.com/tools/debug/) sur URL staging |
| WhatsApp CTA ouvre conversation réelle | SITE-01, SITE-05 | Nécessite mobile + WhatsApp | Tap `wa.me/596696940054` sur mobile, vérifier ouverture WhatsApp avec numéro pré-rempli |
| Email contact reçu (Brevo (Paris, FR)) | SITE-05 | Délivrabilité réelle | Submit form, vérifier réception sur `contact@dloazurpiscines.com` |
| Laravel Cloud deploy + migration auto-run | SITE-07 | Plateforme externe | `git push origin main` + observer logs Laravel Cloud dashboard, vérifier migrations exécutées |
| DNS cutover Zyro → Laravel Cloud | SITE-07 | Acte opérationnel manuel par Pierre | TTL DNS baissé 24h avant, switch CNAME, validation curl post-switch (déclenché par Pierre, voir D-25) |
| Login Fortify réel | AUTH-01 | UX flow complet à observer | Login `pierre@dloazurpiscines.com` → redirect dashboard, vérifier session, vérifier logout |

---

## Validation Sign-Off

- [x] All tasks have `<acceptance_criteria>` automatisable OR référencent une Wave 0 dependency
- [x] Sampling continuity: pas 3 tasks consécutives sans automated verify (every code-bearing task has a pest command)
- [ ] Wave 0 couvre tous les MISSING references (Laravel scaffold + Pest + CI) — runtime flip when Plan 01 Task 2 completes
- [x] Pas de watch-mode flags dans les commandes
- [ ] Feedback latency < 90s (quick) confirmé après Wave 0
- [x] `nyquist_compliant: true` set in frontmatter (matrice remplie)

**Approval:** matrix filled 2026-05-28; runtime `wave_0_complete` flip pending Plan 01 Task 2 success.
