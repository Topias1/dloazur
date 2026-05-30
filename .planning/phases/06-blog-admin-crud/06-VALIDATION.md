---
phase: 6
slug: blog-admin-crud
status: ready
nyquist_compliant: true
wave_0_complete: false
created: 2026-05-30
---

# Phase 6 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest 4.x (PHPUnit under the hood) |
| **Config file** | phpunit.xml (env BLOG_SOURCE=files locks file path for legacy BlogTest) |
| **Quick run command** | `./vendor/bin/pest --filter "Post\|Blog"` |
| **Full suite command** | `./vendor/bin/pest` |
| **Build (EasyMDE bundle)** | `npm run build` |
| **Estimated runtime** | ~25 seconds (full suite) |

---

## Sampling Rate

- **After every task commit:** Run `./vendor/bin/pest --filter "Post|Blog"`
- **After every plan wave:** Run `./vendor/bin/pest`
- **Before `/gsd:verify-work`:** Full suite + `npm run build` must be green
- **Max feedback latency:** 30 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 01-T0 | 06-01 | 0/1 | CONTENT-01 | T-06-01 | string(16) status; unique slug; BLOG_SOURCE locked | Feature | `./vendor/bin/pest tests/Feature/BlogTest.php` | ❌ (creates scaffolds) | ⬜ pending |
| 01-T1 | 06-01 | 1 | CONTENT-01 | T-06-01 | scopePublished + slug auto-gen | Feature | `./vendor/bin/pest tests/Feature/PostModelTest.php` | ❌ Wave 0 | ⬜ pending |
| 01-T2 | 06-01 | 1 | CONTENT-01 | T-06-02 | idempotent seeder (safe re-run, no prod auto-run) | Feature | `./vendor/bin/pest tests/Feature/PostMigrationSeederTest.php` | ❌ Wave 0 | ⬜ pending |
| 02-T1 | 06-02 | 2 | SITE-07 | T-06-04, T-06-05 | published-only + cache-safe scalar payload | Feature | `./vendor/bin/pest tests/Feature/BlogDbSourceTest.php` | ❌ Wave 0 | ⬜ pending |
| 02-T2 | 06-02 | 2 | SITE-07 | T-06-03, T-06-05, T-06-06 | 410-vs-404; sitemap excludes drafts; SEO preserved | Feature | `./vendor/bin/pest tests/Feature/BlogDbSourceTest.php` | ❌ Wave 0 | ⬜ pending |
| 03-T1 | 06-03 | 2 | CONTENT-01 | T-06-07 | admin auth gate (302 for guests) | Feature | `./vendor/bin/pest tests/Feature/PostAdminListTest.php` | ❌ Wave 0 | ⬜ pending |
| 03-T2 | 06-03 | 2 | CONTENT-01 | T-06-08 | list all statuses + parameterized search | Feature | `./vendor/bin/pest tests/Feature/PostAdminListTest.php` | ❌ Wave 0 | ⬜ pending |
| 04-CK1 | 06-04 | 3 | CONTENT-01 | T-06-SC | easymde legitimacy (npmjs.com verify) | Manual gate | (blocking-human checkpoint) | n/a | ⬜ pending |
| 04-T1 | 06-04 | 3 | CONTENT-01 | T-06-SC | EasyMDE bundle builds; preflight not disabled | Build | `npm run build` | n/a | ⬜ pending |
| 04-T2 | 06-04 | 3 | CONTENT-01 | T-06-09, T-06-10, T-06-12, T-06-13 | create/edit; slug-lock; cover mime-reject; cache flush | Feature | `./vendor/bin/pest tests/Feature/PostFormTest.php` | ❌ Wave 0 | ⬜ pending |
| 04-CK2 | 06-04 | 3 | CONTENT-01 | T-06-11 | editor/cover/slug-lock/unpublish 410 round-trip | Manual verify | (blocking human-verify checkpoint) | n/a | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

Created in Plan 06-01 Task 0 (and extended by later plans' Task 1s):

- [ ] `phpunit.xml` — add `BLOG_SOURCE=files` env so legacy file-backed `BlogTest` stays green after the DB default flip (RESEARCH Open Question 3).
- [ ] `tests/Feature/PostModelTest.php` — scopePublished + slug auto-gen (Plan 01).
- [ ] `tests/Feature/PostMigrationSeederTest.php` — idempotency (run twice → 3 rows) + field preservation (Plan 01).
- [ ] `tests/Feature/BlogDbSourceTest.php` — DB-source published-only, cache round-trip survives serializable_classes=false, 410-vs-404, sitemap excludes drafts (Plan 02).
- [ ] `tests/Feature/PostAdminListTest.php` — admin auth gate + list-all-statuses + badge labels + empty state (Plan 03).
- [ ] `tests/Feature/PostFormTest.php` — create/edit, slug-lock-on-publish, cover mime-reject, cache flush, validation (Plan 04).
- [ ] Regression: existing `tests/Feature/BlogTest.php` must stay green throughout (runs under BLOG_SOURCE=files).

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| easymde npm package legitimacy | CONTENT-01 / T-06-SC | slopcheck unavailable → [ASSUMED]; human must verify registry before install | Plan 04 Checkpoint 1: confirm npmjs.com/package/easymde maintainer Ionaru, no postinstall, canonical repo |
| EasyMDE editor renders, survives validation re-render, toolbar themed | CONTENT-01 | Visual/interactive; CodeMirror DOM + Tailwind scoping not assertable headlessly | Plan 04 Checkpoint 2 steps 3 |
| Cover dropzone uploads to Scaleway S3 (CORS), preview shows | CONTENT-01 / T-06-13 | Real browser PUT to S3 + bucket CORS; not exercised by Storage::fake | Plan 04 Checkpoint 2 step 4 |
| Slug locks visibly on publish; unpublish inline confirm → /blog 410 | CONTENT-01 / SITE-07 | End-to-end UI + public-route status round-trip in a real browser | Plan 04 Checkpoint 2 steps 5-6 |

---

## Validation Sign-Off

- [x] All tasks have `<automated>` verify or Wave 0 dependencies (checkpoints excepted)
- [x] Sampling continuity: no 3 consecutive tasks without automated verify
- [x] Wave 0 covers all MISSING references
- [x] No watch-mode flags
- [x] Feedback latency < 30s
- [x] `nyquist_compliant: true` set in frontmatter

**Approval:** ready
