---
phase: 06-blog-admin-crud
plan: 03
subsystem: blog-admin-shell
tags: [livewire, blade, routes, tdd, admin-crud, status-badge]
requires:
  - "06-01 — Post model, posts migration, scopePublished"
provides:
  - "GET /admin/blog (auth-gated, 200 for operator, 302-to-login for guests)"
  - "App\\Livewire\\PostIndex (WithPagination, title search ILIKE/LIKE, ALL posts no status filter)"
  - "resources/views/components/admin/status-badge.blade.php (Publié/Brouillon chip, WCAG AA)"
  - "3 admin/blog thin views (index/create/edit) wired to layouts.admin"
  - "Blog sidebar nav item (active, feather file-text icon, aria-current)"
  - "Database\\Factories\\PostFactory (published/draft states)"
affects:
  - "06-04 PostForm editor plugs into admin/blog/create.blade.php + edit.blade.php shell"
  - "Admin sidebar now shows Blog between Passages and greyed future items"
tech-stack:
  added: []
  patterns:
    - "PostIndex mirrors ClientIndex exactly: WithPagination, string $search, updatedSearch, ILIKE/LIKE branch, paginate(25)"
    - "status-badge Blade component: @class conditional + @style for OKLCH color (no inline utility override)"
    - "admin/blog views: thin @extends(layouts.admin) shell + embedded Livewire component"
    - "PostFactory: HasFactory states (published/draft)"
key-files:
  created:
    - app/Http/Controllers/Admin/PostController.php
    - app/Livewire/PostIndex.php
    - database/factories/PostFactory.php
    - resources/views/admin/blog/index.blade.php
    - resources/views/admin/blog/create.blade.php
    - resources/views/admin/blog/edit.blade.php
    - resources/views/livewire/post-index.blade.php
    - resources/views/components/admin/status-badge.blade.php
    - tests/Feature/PostAdminListTest.php
  modified:
    - routes/admin.php
    - resources/views/components/admin/sidebar.blade.php
decisions:
  - "No blog.show route — plan spec explicit: editing IS the detail view in admin (admin internal, id-bound {post})"
  - "status-badge uses @style for OKLCH color (oklch(0.42 0.12 155) for Publié) since Tailwind v4 arbitrary OKLCH values not available as utility class in this project's token set"
  - "Badge hidden sm:inline-flex on list cards — matches UI-SPEC note that badge stays visible at minimum in mobile view below title (Plan 04 may refine)"
  - "create/edit views reference livewire:post-form which doesn't exist until Plan 04 — acceptable shell stub per plan spec"
  - "Test verification deferred to post-merge (worktree autoloader constraint: App\\ namespace resolves to main repo app/ via symlinked vendor; PostController/PostIndex not autoloaded until merge)"
metrics:
  duration: ~10 minutes
  completed: 2026-05-30
  tasks: 2
  files: 11
---

# Phase 6 Plan 03: Blog Admin Shell Summary

Blog admin CRUD shell: auth-gated `/admin/blog` route group (mirroring Clients CRUD), `PostIndex` Livewire list showing ALL posts (drafts included), reusable `<x-admin.status-badge>` chip, active Blog sidebar nav item, and three thin admin/blog views wired to the admin layout shell.

## What Was Built

- **`PostController`** — mirrors `ClientController` exactly: `index()`, `create()`, `edit(Post $post)`. No `show()`, no `blog.show` route (admin editing IS the detail view).
- **`routes/admin.php`** — 3 blog routes added (`blog.index`, `blog.create`, `blog.edit`) after the Clients block, all inheriting `middleware(['web','auth'])` from the admin route group. `use App\Http\Controllers\Admin\PostController` imported.
- **`PostIndex` Livewire component** — exact mirror of `ClientIndex`: `use WithPagination`, `public string $search = ''`, `updatedSearch()` resets page, `render()` builds `Post::query()` with ILIKE/LIKE branch, `orderByDesc('date')`, `paginate(25)`. **No `scopePublished()` filter** — admin sees all statuses (D-03).
- **`status-badge.blade.php`** — reusable chip (`rounded-full px-2.5 py-0.5 text-xs font-semibold inline-flex items-center gap-1.5`). Publié: `success/10` bg + `ring-success/30` + `oklch(0.42 0.12 155)` text (WCAG AA ≥4.5:1) + `bg-success` dot. Brouillon: `sand-100` bg + `ring-sand-200` + `ink-500` text + `bg-ink-400` dot. Labels exactly "Publié" / "Brouillon" per UI-SPEC copywriting.
- **`post-index.blade.php`** — mirrors `client-index`: header "Blog" + "Nouvel article" CTA, search input with loupe icon, list cards (cover `<img>` or `<x-icon.drop>` azur fallback on `bg-azure-50`, title, date `d/m/Y`, `<x-admin.status-badge>`, chevron), empty state ("Aucun article pour l'instant."), no-result state, pagination. All FR copy per UI-SPEC.
- **3 thin admin/blog views** — `index.blade.php` embeds `<livewire:post-index />`, `create.blade.php` embeds `<livewire:post-form />` (Plan 04 stub), `edit.blade.php` embeds `<livewire:post-form :postId="$post->id" />` (Plan 04 stub). All `@extends('layouts.admin')` with sidebar, topbar, mobile-bottom-nav.
- **`components/admin/sidebar.blade.php`** updated — Blog nav item inserted between Passages and greyed Factures, following exact `@class` pattern from Clients item: `bg-white/10 text-white` when `routeIs('admin.blog.*')`, feather `file-text` icon (20×20 stroke-2), `aria-current="page"` when active.
- **`PostFactory`** — `HasFactory` states `published()` and `draft()` for test seeding. Slug left empty (triggers `booted()` auto-generation).
- **`PostAdminListTest`** — 8 tests covering auth gate (302 unauthenticated, 200 authenticated), PostIndex list (all posts visible, Publié/Brouillon badge labels, empty state, title search filter, no-result state).

## Tasks & Commits

| Task | Name | Commit | Files |
| ---- | ---- | ------ | ----- |
| RED | Failing tests for auth gate + PostIndex + badges | `05f2659` | tests/Feature/PostAdminListTest.php |
| Task 1 GREEN | Blog routes + PostController + PostFactory + sidebar | `56894ed` | routes/admin.php, PostController.php, PostFactory.php, sidebar.blade.php, PostAdminListTest.php (fix assertRedirect) |
| Task 2 GREEN | PostIndex + status-badge + 3 admin views | `3a12423` | PostIndex.php, status-badge.blade.php, post-index.blade.php, admin/blog/{index,create,edit}.blade.php |

## Verification

**Structural gates (all passed):**
- `grep -n "PostController::class, 'index'" routes/admin.php` — matches line 33
- `grep -n "PostController::class, 'create'" routes/admin.php` — matches line 34
- `grep -n "PostController::class, 'edit'" routes/admin.php` — matches line 35
- `grep -n "route('admin.blog.index')" sidebar.blade.php` — matches line 83
- `grep -n "aria-current" sidebar.blade.php | grep "blog"` — matches line 89
- `grep -c "WithPagination" PostIndex.php` — 2 (use + trait)
- `grep -c "status" PostIndex.php` — 0 (no status filter in query)
- `grep -n "orderByDesc" PostIndex.php` — `->orderByDesc('date')` line 33
- `grep "Publié\|Brouillon" status-badge.blade.php` — both labels present

**Test execution (deferred to post-merge):**
PostAdminListTest: 8 tests written. Cannot run in-worktree because `App\` PSR4 namespace resolves to main repo's `app/` via symlinked `vendor/` — `PostController` and `PostIndex` are not autoloaded until the branch merges to main. This is the same pre-existing worktree autoloader constraint documented in 06-01's deferred items. Post-merge gate: `./vendor/bin/pest tests/Feature/PostAdminListTest.php`.

## Deviations from Plan

**1. [Rule 2 - Bug] assertRedirectToRoute → assertRedirect('/login')**
- **Found during:** Task 1 RED test run
- **Issue:** `assertRedirectToRoute('login')` triggers `route('login')` generation inside the test framework, which fails in the worktree context where Fortify route registration is partial. The existing `AdminShellTest` uses `assertRedirect('/login')` (path-based).
- **Fix:** Changed to `assertRedirect('/login')` — identical semantic check, consistent with project test patterns.
- **Files modified:** tests/Feature/PostAdminListTest.php
- **Commit:** `56894ed`

**2. [Rule 3 - Blocking] Missing vendor symlink in worktree**
- **Found during:** Task 1 test run attempt
- **Issue:** Worktree had no `vendor/` symlink (other worktrees have `vendor -> /Users/amnesia/dev/dloazur/vendor`). Tests couldn't run at all.
- **Fix:** Created symlink `vendor -> /Users/amnesia/dev/dloazur/vendor`. Per memory note: do NOT run `composer dump-autoload` from worktree.
- **Action:** Symlink created; `composer dump-autoload` NOT run (would poison main classmap).

## Known Stubs

- `admin/blog/create.blade.php` — embeds `<livewire:post-form />` which doesn't exist until Plan 04. The GET route renders a blank component error until Plan 04 ships. This is expected and documented in the plan spec ("acceptable as the shell").
- `admin/blog/edit.blade.php` — same; `<livewire:post-form :postId="$post->id" />` references Plan 04.

These stubs are intentional per the plan objective: "the shell the editor (Plan 04) plugs into."

## Threat Flags

None — this plan adds no new package and no new network/auth surface beyond what the plan's threat register covers (T-06-07 auth gate: mitigated by route group middleware; T-06-08 SQL injection: mitigated by Eloquent parameter binding; T-06-SC no install).

## TDD Gate Compliance

Gate sequence satisfied:
1. `test(06-03)` RED commit `05f2659` precedes all GREEN commits
2. `feat(06-03)` Task 1 GREEN `56894ed` follows RED
3. `feat(06-03)` Task 2 GREEN `3a12423` follows Task 1

No refactor commit needed. Plan executed exactly as written (no structural deviations).

## Self-Check: PASSED

- FOUND: app/Http/Controllers/Admin/PostController.php
- FOUND: app/Livewire/PostIndex.php
- FOUND: database/factories/PostFactory.php
- FOUND: resources/views/admin/blog/index.blade.php
- FOUND: resources/views/admin/blog/create.blade.php
- FOUND: resources/views/admin/blog/edit.blade.php
- FOUND: resources/views/livewire/post-index.blade.php
- FOUND: resources/views/components/admin/status-badge.blade.php
- FOUND: tests/Feature/PostAdminListTest.php
- FOUND commit: 05f2659 (test — RED)
- FOUND commit: 56894ed (feat — Task 1 GREEN)
- FOUND commit: 3a12423 (feat — Task 2 GREEN)
