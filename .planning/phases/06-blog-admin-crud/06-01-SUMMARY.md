---
phase: 06-blog-admin-crud
plan: 01
subsystem: blog-data-layer
tags: [eloquent, migration, seeder, medialibrary, tdd]
requires: []
provides:
  - "App\\Models\\Post (HasMedia, scopePublished, slug auto-gen)"
  - "posts table (string(16) status, compound index status+date)"
  - "public BlogRepository::parse()"
  - "PostMigrationSeeder (idempotent, 3 canonical articles)"
affects:
  - "06-02+ public blog DB cutover"
  - "06-03/06-04 admin CRUD + editor"
tech-stack:
  added: []
  patterns:
    - "string(16) status column (NOT enum) — project D-03 convention"
    - "standalone seeder, NOT registered in env-gated DatabaseSeeder (AdminSeeder analog)"
    - "updateOrCreate keyed on slug for idempotent content migration"
    - "medialibrary cover collection: singleFile + s3 disk + nonQueued thumbnail conversion"
key-files:
  created:
    - app/Models/Post.php
    - database/migrations/2026_05_30_000001_create_posts_table.php
    - database/seeders/PostMigrationSeeder.php
    - tests/Feature/PostModelTest.php
    - tests/Feature/PostMigrationSeederTest.php
  modified:
    - app/Support/BlogRepository.php
    - phpunit.xml
decisions:
  - "parse() made public (was private) so the seeder reuses the exact same front-matter parsing — byte-for-byte fidelity, no duplicate logic"
  - "phpunit BLOG_SOURCE=files lock so the existing file-backed BlogTest stays green after later waves default the app to DB"
  - "cover stays null for the 3 articles — og-default.jpg fallback in buildArticleSchema() covers SEO"
metrics:
  duration: ~continuation (socket-recovered)
  completed: 2026-05-30
  tasks: 3
  files: 7
---

# Phase 6 Plan 01: Blog Data Layer Foundation Summary

DB-backed `Post` model + `posts` migration + idempotent `PostMigrationSeeder` that migrates the 3 canonical `.md` articles via a now-public `BlogRepository::parse()`, with the test env locked to `BLOG_SOURCE=files` so the existing file-backed blog stays green.

## What Was Built

- **`App\Models\Post`** — `implements HasMedia`, uses `InteractsWithMedia` + `HasFactory`. `$fillable` (title, slug, body, excerpt, status, author, date, show_date), `$casts` (date→date, show_date→boolean). `scopePublished()` → `where('status','published')`. `booted()` `creating` hook sets `slug = Str::slug(title)` when empty. `registerMediaCollections()` declares a `cover` single-file collection on the `s3` disk with a nested `thumbnail` conversion (1200×630, `nonQueued()` — no queue dependency on serverless). No `getRouteKeyName` (admin uses id binding; public `blog.show` resolves slug via BlogRepository).
- **`posts` migration** (`2026_05_30_000001`) — id; string title; string slug unique; text body; text excerpt nullable; `string('status', 16)` default 'draft' (project convention, **not** `->enum()`); string author default 'Pierre ADAM'; date nullable; boolean show_date default true; timestamps; compound index `['status','date']`.
- **`PostMigrationSeeder`** — standalone (NOT registered in the env-gated `DatabaseSeeder`, mirroring `AdminSeeder`). Iterates the 3 canonical `resource_path('content/blog')` files, calls public `parse()`, and `Post::updateOrCreate(['slug' => ...], [...])` — idempotent, production-safe via `--force`. Maps title/body/excerpt/author/date(`->toDateString()`)/show_date, forces `status='published'`, leaves cover null.
- **`BlogRepository::parse()`** — visibility `private` → `public` (body unchanged, return shape unchanged).
- **`phpunit.xml`** — added `<env name="BLOG_SOURCE" value="files"/>`.
- **Two Pest test files** — `PostModelTest` (slug auto-gen, scopePublished, cover URL callable) and `PostMigrationSeederTest` (3 canonical slugs, idempotency run-twice→3, status=published, title/author/date/show_date preservation).

## Tasks & Commits

| Task | Name | Commit | Files |
| ---- | ---- | ------ | ----- |
| 0 | RED scaffolds + posts migration + public parse() + phpunit BLOG_SOURCE lock | `4d99a01` | phpunit.xml, migration, BlogRepository.php, PostModelTest, PostMigrationSeederTest |
| 1 | Post model — HasMedia, scopePublished, slug auto-gen | `582707b` | app/Models/Post.php |
| 2 | Idempotent PostMigrationSeeder | `8c1d222` | database/seeders/PostMigrationSeeder.php |

(Tasks 0–1 were committed in the pre-socket-error turn; Task 2 was finished and committed in the recovery turn.)

## Verification

- `PostModelTest` + `PostMigrationSeederTest`: **10 passed / 17 assertions** (run from worktree with explicit `uses(\Tests\TestCase::class)`).
- Migration gates: `grep -c 'enum('` matches only the `NOT ->enum()` comment — no real `->enum()` DDL; `string('status', 16)` present; compound index `['status','date']` present.
- Seeder gates: `updateOrCreate` keyed on `['slug' => ...]`; `grep -c 'PostMigrationSeeder' database/seeders/DatabaseSeeder.php` = 0 (not registered).
- `parse()` is `public`; `phpunit.xml` has `BLOG_SOURCE=files`.

## Deviations from Plan

None — plan executed as written. (parse() public, phpunit env lock, and BLOG_SOURCE=files were all explicit plan actions, not deviations.)

## Deferred Issues

**Pre-existing worktree Pest namespace-binding quirk (out of scope).** When the full plan test gate is run from inside the git worktree, every pre-existing Feature test lacking an explicit `uses(\Tests\TestCase::class)` (e.g. `BlogTest`, `HomePageTest`, all others) fails with `claude\worktrees\...::get()` undefined / container errors. Root cause: `vendor/` is symlinked to the main repo, so Pest's relative `->in('Feature')` resolves against the main repo root and mis-namespaces worktree test files. This affects ALL pre-existing Feature tests identically and is independent of any 06-01 change — not a regression. The new 06-01 test files work around it with an explicit `uses()` (marked "Strip on merge"). Post-merge, the full suite (including BlogTest) must be run from the main repo, where the harness binds correctly. Logged to `deferred-items.md`. Do NOT patch `tests/Pest.php` from a worktree.

## Known Stubs

None.

## Threat Flags

None — this plan adds no new package and no new network/auth/file surface beyond the in-repo `.md` reads already covered by the plan's threat register (T-06-01 slug unique index applied; T-06-02 idempotent-seeder accepted; T-06-SC no install).

## Self-Check: PASSED

- FOUND: app/Models/Post.php
- FOUND: database/migrations/2026_05_30_000001_create_posts_table.php
- FOUND: database/seeders/PostMigrationSeeder.php
- FOUND: tests/Feature/PostModelTest.php
- FOUND: tests/Feature/PostMigrationSeederTest.php
- FOUND commit: 4d99a01 (test)
- FOUND commit: 582707b (feat — Post model)
- FOUND commit: 8c1d222 (feat — PostMigrationSeeder)

## TDD Gate Compliance

Gate sequence satisfied: `test(06-01)` (4d99a01, RED scaffolds) precedes `feat(06-01)` GREEN commits (582707b model, 8c1d222 seeder). No refactor commit needed.
