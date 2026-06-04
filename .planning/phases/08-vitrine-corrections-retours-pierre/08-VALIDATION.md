---
phase: 8
slug: vitrine-corrections-retours-pierre
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-06-04
---

# Phase 08 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest PHP 4.7 |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `./vendor/bin/pest --filter='HeroV1\|DepannageRoute\|CallCenterVoix\|ServicesPage' --stop-on-failure` |
| **Full suite command** | `./vendor/bin/pest --ci` |
| **Estimated runtime** | ~30 seconds |

---

## Sampling Rate

- **After every task commit:** Run `./vendor/bin/pest --filter='HeroV1|DepannageRoute|CallCenterVoix|ServicesPage' --stop-on-failure`
- **After every plan wave:** Run `./vendor/bin/pest --ci`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 30 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| hero-geo | 01 | 1 | V1 | — | N/A | Feature | `./vendor/bin/pest --filter='HeroV1' -x` | ❌ W0 | ⬜ pending |
| purge-martinique | 01 | 1 | V1 | — | N/A | Feature | `./vendor/bin/pest --filter='HeroV1' -x` | ❌ W0 | ⬜ pending |
| depannage-page | 02 | 1 | V5 | — | Route publique, groupe cache.headers:vitrine, pas d'auth | Feature HTTP | `./vendor/bin/pest --filter='DepannageRoute' -x` | ❌ W0 | ⬜ pending |
| services-grid-link | 02 | 1 | V5 | — | N/A | Feature HTML | inclus dans DepannageRoute | ❌ W0 | ⬜ pending |
| callcenter-voix | 03 | 2 | V12/V14 | — | N/A | Feature | `./vendor/bin/pest --filter='CallCenterVoix' -x` | ❌ W0 | ⬜ pending |
| notre-approche | 03 | 2 | V14 | — | N/A | Feature HTML | inclus dans CallCenterVoix | ❌ W0 | ⬜ pending |
| menage-orphelin | 04 | 2 | V6 | — | N/A | Feature HTTP | `./vendor/bin/pest --filter='ServicesPage' -x` | ⚠️ vérifier | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/Vitrine/HeroV1Test.php` — V1 : hero ne contient plus "ma tournée", "toute la Martinique" ; contient "zone d'intervention"
- [ ] `tests/Feature/Vitrine/DepannageRouteTest.php` — V5 : GET /services/depannage retourne 200, h1 "Dépannage", lien WhatsApp présent, breadcrumb JSON-LD, sitemap inclut l'URL
- [ ] `tests/Feature/Vitrine/CallCenterVoixTest.php` — V12/V14 : occurrences "call-center|standard|centre d'appel" ≤2 sur rendu home ; section "Notre approche" présente
- [ ] Vérifier `tests/Feature/Vitrine/ServicesPageTest.php` — si existant, couvre déjà GET /services 200 et détectera le 500 si @include orphelin mal retiré

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| urgence-eau-verte.blade.php supprimé du disque | V6 | Assertion filesystem hors scope Pest standard | `test ! -f resources/views/vitrine/partials/urgence-eau-verte.blade.php && echo OK` |
| Page /services/depannage — rendu visuel correct (hero, bullets, CTA WA) | V5 | Vérification visuelle qualitative | Ouvrir `http://localhost:8000/services/depannage`, valider mise en page |
| "Notre approche" — voix cohérente, pas de négation "pas de call-center" | V12/V14 | Qualité copy, non-testable automatiquement | Relire les sections pierre + final-cta + Notre approche |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 30s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
