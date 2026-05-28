---
phase: 1
slug: vitrine-fondations
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-05-28
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

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| (à remplir par le planner depuis PLAN.md) | | | | | | | | | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*
*Note : la matrice complète sera générée par le planner — un row par task `<acceptance_criteria>` automatisable.*

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
| Email contact reçu (Mailgun EU) | SITE-05 | Délivrabilité réelle | Submit form, vérifier réception sur `contact@dloazurpiscines.com` |
| Laravel Cloud deploy + migration auto-run | SITE-07 | Plateforme externe | `git push origin main` + observer logs Laravel Cloud dashboard, vérifier migrations exécutées |
| DNS cutover Zyro → Laravel Cloud | SITE-07 | Acte opérationnel manuel par Pierre | TTL DNS baissé 24h avant, switch CNAME, validation curl post-switch (déclenché par Pierre, voir D-25) |
| Login Fortify réel | AUTH-01 | UX flow complet à observer | Login `pierre@dloazurpiscines.com` → redirect dashboard, vérifier session, vérifier logout |

---

## Validation Sign-Off

- [ ] All tasks have `<acceptance_criteria>` automatisable OR référencent une Wave 0 dependency
- [ ] Sampling continuity: pas 3 tasks consécutives sans automated verify
- [ ] Wave 0 couvre tous les MISSING references (Laravel scaffold + Pest + CI)
- [ ] Pas de watch-mode flags dans les commandes
- [ ] Feedback latency < 90s (quick) confirmé après Wave 0
- [ ] `nyquist_compliant: true` set in frontmatter (par le planner après remplissage matrice)

**Approval:** pending
