---
phase: 06-blog-admin-crud
verified: 2026-05-30T18:00:00Z
status: human_needed
score: 17/17 must-haves verified
overrides_applied: 0
human_verification:
  - test: "Open /admin/blog — verify EasyMDE editor renders with admin-themed toolbar (sand/azure tokens, not unstyled) and that the editor survives a validation error (submit empty body, refill — editor must not blank out)"
    expected: "Toolbar shows bold/italic/heading etc with sand-100 bg and azure-600 active state; editor content persists after a validation error round-trip"
    why_human: "CSS scoping under .easymde-wrap in @layer components cannot be verified by grep; CodeMirror DOM survival after Livewire re-render requires browser observation"
  - test: "Upload a landscape cover image from /admin/blog/create — verify preview appears (S3 CORS working) and 'Remplacer'/'Retirer' controls show"
    expected: "Cover preview renders in-form; no spinner hang (Scaleway CORS permits browser PUT to livewire-tmp/)"
    why_human: "Requires Scaleway bucket CORS configured (user_setup in Plan 04) and a live S3 write — cannot be automated without real credentials"
  - test: "Create an article, set Publié, save — confirm slug field becomes a locked pill with lock icon, and the post is visible at /blog/{slug} returning 200"
    expected: "Slug field is a read-only pill (not a disabled input); GET /blog/{slug} returns 200 with og:type=article"
    why_human: "Slug-lock visual rendering (pill vs disabled input) requires browser; end-to-end publish round-trip depends on DB source being live"
  - test: "From a published article in /admin/blog, click 'Dépublier' — confirm inline confirm appears (no modal), confirm → /blog/{slug} returns 410 and admin list shows 'Brouillon'"
    expected: "Inline confirm UI (x-data confirming block) appears without a modal overlay; after confirm + save the public URL returns 410; admin list badge switches to Brouillon"
    why_human: "Alpine inline-confirm interaction and the publish→draft→410 round-trip require browser + live DB; 410 response depends on source=db active in production"
---

# Phase 6: Blog Admin CRUD Verification Report

**Phase Goal:** Permettre à Pierre (non-dev) de créer/éditer/dépublier des articles de blog depuis `/admin/blog`, sans toucher au code ni à git. Introduit un modèle `Post` + migration Postgres, un CRUD admin (liste, créer, éditer, dépublier), un éditeur Markdown, et bascule `BlogRepository` de fichiers→DB en migrant les 3 articles existants. Doit préserver les acquis SEO de la phase 999.1 : `og:type=article`, Article JSON-LD, dates réelles, et entrées sitemap.
**Verified:** 2026-05-30T18:00:00Z
**Status:** human_needed
**Re-verification:** No — initial verification

---

## Goal Achievement

### Observable Truths

| # | Truth | Status | Evidence |
|---|-------|--------|----------|
| 1 | A Post can be created and queried from the posts table | ✓ VERIFIED | `app/Models/Post.php` — `Post extends Model implements HasMedia`; `PostModelTest` 10 assertions pass |
| 2 | Post::published() returns only status=published rows | ✓ VERIFIED | `scopePublished()` at line 38 does `where('status','published')`; PostModelTest green |
| 3 | Slug auto-generates from title via Str::slug on create | ✓ VERIFIED | `booted()` creating hook at line 49–53; PostModelTest slug-auto-gen passes |
| 4 | PostMigrationSeeder lands the 3 canonical articles and is safe to re-run (3 rows after 2 runs) | ✓ VERIFIED | `updateOrCreate(['slug' => ...])` keyed on slug; `grep -c PostMigrationSeeder DatabaseSeeder.php` = 0; PostMigrationSeederTest idempotency passes |
| 5 | With BLOG_SOURCE=db, BlogRepository::all() returns only published posts from the DB | ✓ VERIFIED | `allFromDb()` → `cacheablePayloadFromDb()` → `Post::published()` confirmed at BlogRepository.php:119,143; BlogDbSourceTest published-only test passes |
| 6 | A published post is visible at /blog/{slug}; a draft slug returns 410; never-existed returns 404 | ✓ VERIFIED | `BlogController::show()` 410 branch at line 29–31; BlogDbSourceTest 200/410/404 behaviors all pass |
| 7 | The DB-backed cached payload survives serializable_classes=false (no objects leak) | ✓ VERIFIED | `cacheablePayloadFromDb()` ends with `->all()` (plain array); Carbon → ISO-8601 string; BlogDbSourceTest cache round-trip test passes |
| 8 | Sitemap lists published posts only; og:type=article + Article JSON-LD + real dates preserved | ✓ VERIFIED | SitemapController uses `app(BlogRepository)->all()` (excludes drafts via scopePublished on db source); BlogController passes `'type' => 'article'` and `articleJsonLd` to view; JSON-LD built via `buildArticleSchema()` with `datePublished`/`dateModified`; BlogDbSourceTest sitemap-draft-exclusion + og:type assertions pass |
| 9 | An authenticated operator sees /admin/blog listing ALL posts (drafts + published) | ✓ VERIFIED | `PostIndex::render()` has no `scopePublished()` filter (admin sees all); comment at PostIndex.php:27 explicit; `grep -c 'where.*status' PostIndex.php` = 0; PostAdminListTest auth+list behaviors pass |
| 10 | Each list row shows a status badge (Publié / Brouillon) | ✓ VERIFIED | `status-badge.blade.php` contains both labels; WCAG-AA green at oklch(0.42 0.12 155); PostAdminListTest badge-labels test passes |
| 11 | Empty state appears when no posts exist; search filters by title | ✓ VERIFIED | `PostIndex` has ILIKE/LIKE branch on `$this->search`; post-index.blade.php has `@forelse`/empty-state; PostAdminListTest empty-state + search filter pass |
| 12 | An unauthenticated request to /admin/blog redirects to login | ✓ VERIFIED | Routes inherit `middleware(['web','auth'])` from admin group; PostAdminListTest asserts 302 to /login for guests |
| 13 | The sidebar shows an active Blog nav item | ✓ VERIFIED | `sidebar.blade.php` line 83–89: `route('admin.blog.index')`, `routeIs('admin.blog.*')` @class, `aria-current="page"` |
| 14 | Pierre creates a post (title, Markdown body, excerpt, optional cover, status) and it persists | ✓ VERIFIED | `PostForm::submit()` with `#[Validate]`, `$post->fill([...])`, `$post->save()`; PostFormTest create behavior passes |
| 15 | The Markdown body is edited via EasyMDE and stored as raw Markdown | ✓ VERIFIED | `post-editor.js` exports `postEditor` factory with `new window.EasyMDE(...)`; `$wire.set('body', ..., false)` sync at line 54; `app.js` imports EasyMDE + registers Alpine factory; `post-form.blade.php` has `wire:ignore .easymde-wrap` + hidden `wire:model="body"` |
| 16 | Slug auto-generates from title while draft, is locked (read-only) once published | ✓ VERIFIED | `PostForm::submit()` checks `$post->exists && $post->status === 'published'` → keeps persisted slug; PostFormTest slug-lock-on-publish passes; blade uses `@if($status === 'published')` to render locked pill |
| 17 | Publishing/unpublishing toggles status and flushes the blog.index cache | ✓ VERIFIED | `Cache::forget('blog.index')` at PostForm.php:108; CR-01 fix at line 91 stamps publish date; PostFormTest cache-flush test passes |

**Score:** 17/17 truths verified

---

### Required Artifacts

| Artifact | Expected | Status | Details |
|----------|----------|--------|---------|
| `app/Models/Post.php` | Eloquent Post model with HasMedia, scopePublished, slug auto-gen | ✓ VERIFIED | Implements HasMedia; scopePublished at line 38; booted() creating hook at line 49 |
| `database/migrations/2026_05_30_000001_create_posts_table.php` | posts table with string(16) status + compound index | ✓ VERIFIED | `string('status', 16)` at line 17; compound index `['status','date']` at line 23; `->enum()` not present |
| `database/seeders/PostMigrationSeeder.php` | Idempotent migration of the 3 .md articles | ✓ VERIFIED | `updateOrCreate(['slug' => ...])` at line 43; not registered in DatabaseSeeder |
| `config/blog.php` | source flag (db\|files) for files→DB cutover | ✓ VERIFIED | `env('BLOG_SOURCE', 'db')` confirmed |
| `app/Support/BlogRepository.php` | DB read path mirroring file path array shape + cache discipline | ✓ VERIFIED | `cacheablePayloadFromDb()` exists, ends with `->all()`, 10-key shape, Carbon→ISO-8601; file path unchanged |
| `app/Http/Controllers/BlogController.php` | 410-vs-404 branch in show() | ✓ VERIFIED | `abort(410)` inside `config('blog.source') === 'db'` branch at line 29–31 |
| `app/Http/Controllers/Admin/PostController.php` | Thin GET controllers index/create/edit | ✓ VERIFIED | `view('admin.blog.index')`, `view('admin.blog.create')`, `view('admin.blog.edit', compact('post'))` — no show() |
| `app/Livewire/PostIndex.php` | Paginated list of ALL posts with title search | ✓ VERIFIED | `use WithPagination`; ILIKE/LIKE branch; `orderByDesc('date')`; `paginate(25)`; no status filter |
| `resources/views/components/admin/status-badge.blade.php` | Reusable Publié/Brouillon chip | ✓ VERIFIED | Both labels present; OKLCH color for Publié |
| `app/Livewire/PostForm.php` | Create/edit write component with slug-lock, cover upload, cache flush | ✓ VERIFIED | `use WithFileUploads`; `Cache::forget('blog.index')`; `toMediaCollection('cover','s3')`; slug-lock logic at line 73–78; CR-01 date stamp at line 91 |
| `resources/js/post-editor.js` | Alpine postEditor factory initializing EasyMDE + $wire.set sync | ✓ VERIFIED | Exports `postEditor(initialBody)`; `new window.EasyMDE(...)`; `$wire.set('body', this.editor.value(), false)` at line 54 |
| `resources/views/livewire/post-form.blade.php` | Editor form: EasyMDE (wire:ignore), cover dropzone, slug field, status toggle, unpublish | ✓ VERIFIED | `wire:ignore`; `role="radiogroup"`; slug @if/published-lock pill; `x-data="{ confirming: false }"` inline-confirm; cover dropzone with Remplacer/Retirer |
| `resources/views/admin/blog/edit.blade.php` | Edit view with correct title (CR-02 fix) | ✓ VERIFIED | `@section('title'){{ $post->title }} — Modifier · Dlo Azur@endsection` — block form, not two-arg form |

### Key Link Verification

| From | To | Via | Status | Details |
|------|----|-----|--------|---------|
| `routes/admin.php` | `Admin/PostController.php` | blog.index/create/edit routes | ✓ WIRED | Lines 33–35; `use App\Http\Controllers\Admin\PostController` at line 6 |
| `resources/views/admin/blog/index.blade.php` | `app/Livewire/PostIndex.php` | `<livewire:post-index />` | ✓ WIRED | Confirmed in index.blade.php |
| `app/Support/BlogRepository.php` | `App\Models\Post` | `Post::published()->orderByDesc('date')` | ✓ WIRED | Lines 119 and 143 of BlogRepository.php |
| `app/Http/Controllers/BlogController.php` | `config('blog.source')` | 410 gate only on db source | ✓ WIRED | Line 29 of BlogController.php |
| `resources/views/livewire/post-form.blade.php` | `resources/js/post-editor.js` | `x-data="postEditor(@js($body))"` | ✓ WIRED | Confirmed in post-form.blade.php line 56; `Alpine.data('postEditor', postEditor)` in app.js line 60 |
| `app/Livewire/PostForm.php` | `Cache::forget('blog.index')` | flush on status change | ✓ WIRED | PostForm.php line 108 |

### Data-Flow Trace (Level 4)

| Artifact | Data Variable | Source | Produces Real Data | Status |
|----------|--------------|--------|-------------------|--------|
| `PostIndex` | `$posts` (paginate) | `Post::query()->...->paginate(25)` | Yes — real Eloquent query, no empty stub | ✓ FLOWING |
| `BlogRepository::cacheablePayloadFromDb()` | mapped array | `Post::published()->orderByDesc('date')->get()` | Yes — DB query with scopePublished scope | ✓ FLOWING |
| `PostForm::submit()` | `$post->fill([...])` | Livewire properties from form inputs | Yes — title/body/excerpt/status/date persisted | ✓ FLOWING |

### Behavioral Spot-Checks

| Behavior | Command | Result | Status |
|----------|---------|--------|--------|
| All Phase 6 feature tests pass | `./vendor/bin/pest tests/Feature/Post*.php tests/Feature/BlogDb*.php --no-coverage` | 36/36 passed, 98 assertions | ✓ PASS |
| Full suite passes (post-merge) | `./vendor/bin/pest --no-coverage` | 464 tests, 460 passed, 4 skipped | ✓ PASS |
| string(16) status in migration | `grep -n 'string(.status., 16)' ...create_posts_table.php` | Matches line 17 | ✓ PASS |
| No enum() in migration | `grep -c 'enum(' ...create_posts_table.php` | 1 match (in comment only: "NOT ->enum()") | ✓ PASS |
| PostMigrationSeeder not in DatabaseSeeder | `grep -c 'PostMigrationSeeder' DatabaseSeeder.php` | 0 | ✓ PASS |
| parse() is public | `grep -n 'public function parse' BlogRepository.php` | Matches line 193 | ✓ PASS |
| BLOG_SOURCE=files in phpunit.xml | `grep -n 'BLOG_SOURCE' phpunit.xml` | Matches line 45, value "files" | ✓ PASS |
| easymde in package.json | `grep -n '"easymde"' package.json` | `"easymde": "^2.21.0"` at line 23 | ✓ PASS |

### Probe Execution

Step 7c: SKIPPED — no `scripts/*/tests/probe-*.sh` present. The phase has no declared probes.

### Requirements Coverage

| Requirement | Source Plan | Description | Status | Evidence |
|-------------|------------|-------------|--------|----------|
| CONTENT-01 | Plans 01–04 | Pierre (non-dev) crée, édite et dépublie des articles de blog depuis /admin/blog sans toucher au code ni à git | ✓ SATISFIED | Post model + admin CRUD + PostForm + EasyMDE + slug-lock + cache flush all verified above. Cover→S3 round-trip deferred to human verification per user instruction. |
| SITE-07 | Plan 02 | Pages publiques SEO local (balises meta, sitemap, données structurées) — Phase 6 preserves these | ✓ SATISFIED (preservation) | og:type=article passed from BlogController (line 46); Article JSON-LD built in buildArticleSchema() with real datePublished/dateModified; sitemap uses BlogRepository::all() which excludes drafts on db source; 410 for unpublished slugs aids Google de-index; BlogDbSourceTest og:type+JSON-LD+sitemap tests pass |

**Note on SITE-07:** This requirement is marked Complete in REQUIREMENTS.md (Phase 1 / Phase 999.1). Phase 6's obligation is preservation, not delivery. Verification confirms no regression: the 999.1 SEO acquis (og:type=article, Article JSON-LD, real dates, sitemap entries) are intact and now work correctly for DB-sourced posts as well as file-sourced posts.

### Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|------|------|---------|----------|--------|
| `resources/views/admin/blog/create.blade.php` | ~28 | `{{-- Form card (PostForm arrives in Plan 04) --}}` stale comment | INFO | Misleading — PostForm is already shipped. No functional impact. |
| `resources/views/admin/blog/edit.blade.php` | ~28 | Same stale comment | INFO | Same as above. |
| `app/Livewire/PostIndex.php` | 30–33 | `'%' . $this->search . '%'` without LIKE metachar escape | WARNING | `%` and `_` in the search term act as wildcards (IN-05 / WR-05 from code review). Correctness defect, not a security hole; Eloquent parameterizes the value. |
| `app/Livewire/PostForm.php` | 95–98 | `$this->cover->getClientOriginalExtension()` for stored filename | WARNING | Client-supplied extension trusted for S3 filename; WR-03 from code review. Low severity on a single-operator app. |
| `app/Livewire/PostForm.php` | 109–113 | `addError('save')` swallows media failure, leaving orphaned post row possible | WARNING | WR-02 from code review. No transaction wrapping post save + media attach. |
| `app/Livewire/PostForm.php` | 90 | `$set('cover', null)` in Retirer button clears pending upload but does NOT remove persisted media | WARNING | WR-06 from code review. Edit form cannot show existing cover; "Retirer" silently does nothing for persisted media. |

**Debt marker gate:** No `TBD`, `FIXME`, or `XXX` markers found in any Phase 6 file. CLEAN.

**CR-01 and CR-02 FIXED:** Code review blockers were resolved in commit b0b3ebd before this verification ran. PostForm now stamps `date` on first publish transition (line 91); edit.blade.php uses block-form `@section('title')` (line 3). Both verified directly in the codebase and backed by regression tests in PostFormTest.

### Human Verification Required

The following items require browser + live DB/S3 verification. They were deferred by the user as human-verification items and are **not** automated must-haves.

#### 1. EasyMDE Editor Rendering and Validation Survival

**Test:** Log in to /admin, open /admin/blog/create. Write Markdown in the EasyMDE editor; toggle preview. Then submit the form with an empty body field (to trigger a validation error), refill the body, and confirm.
**Expected:** Toolbar styling matches the admin theme (sand/azure tokens from `.easymde-wrap` CSS, not unstyled CodeMirror defaults). Editor content persists across a validation error — Livewire re-renders the form but `wire:ignore` protects the EasyMDE DOM, so the body text must not blank out.
**Why human:** CSS scoping under `@layer components` cannot be verified by grep; CodeMirror DOM survival across Livewire re-render requires observing the browser.

#### 2. Cover Upload via Scaleway S3 CORS

**Test:** From /admin/blog/create, upload a landscape image (JPEG or PNG, any size ≤4MB).
**Expected:** After selecting the file, the cover preview renders in the form (not a spinner that hangs indefinitely). "Remplacer" and "Retirer" controls appear below the preview. Saving the article stores the cover in S3 and the public blog post shows the cover image.
**Why human:** Requires Scaleway bucket CORS configured (user_setup in Plan 04: `livewire-tmp/` prefix allowed for browser PUT from app origin). Cannot be automated without live Scaleway credentials and CORS policy.

#### 3. Slug Lock and Public Publish Round-Trip

**Test:** Create an article, set status to "Publié", save. Confirm the slug field becomes a locked pill with a lock icon (not a disabled input). Navigate to /blog/{slug} in a browser.
**Expected:** Locked pill renders as `dloazurpiscines.com/blog/{slug}` with a lock icon, no editable input. GET /blog/{slug} returns 200 with the article content and og:type=article.
**Why human:** Slug-lock visual (pill vs disabled input) requires browser; end-to-end publish requires source=db active in a running environment.

#### 4. Unpublish Inline Confirm → 410 Round-Trip

**Test:** From a published article's edit page, click "Dépublier". Confirm the inline confirm UI appears without a modal overlay (no backdrop, no dialog element). Click "Dépublier" in the confirm block, then save the form. Navigate to /blog/{slug} in a browser.
**Expected:** Inline Alpine confirm shows within the form card (x-data confirming). After save, the public URL returns HTTP 410. The admin list shows "Brouillon" badge for the article.
**Why human:** Alpine `x-data="{ confirming: false }"` interaction requires browser. 410 response requires source=db and a running server.

---

## Gaps Summary

No gaps. All 17 must-haves are VERIFIED in the codebase. Both code-review blockers (CR-01 null publish date, CR-02 edit title leak) were fixed and confirmed with regression tests before this verification ran. The 6 warnings and 4 info items from the code review are non-blocking robustness concerns; none prevent the phase goal from being achieved for the current single-operator use case.

Status is `human_needed` because 4 browser-dependent behaviors (EasyMDE rendering, S3 CORS cover upload, slug-lock visual, unpublish 410 round-trip) cannot be verified programmatically and were explicitly deferred by the user to human verification.

---

_Verified: 2026-05-30T18:00:00Z_
_Verifier: Claude (gsd-verifier)_
