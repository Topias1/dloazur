---
phase: 06-blog-admin-crud
reviewed: 2026-05-30T00:00:00Z
depth: standard
files_reviewed: 26
files_reviewed_list:
  - app/Http/Controllers/Admin/PostController.php
  - app/Http/Controllers/BlogController.php
  - app/Livewire/PostForm.php
  - app/Livewire/PostIndex.php
  - app/Models/Post.php
  - app/Support/BlogRepository.php
  - config/blog.php
  - database/factories/PostFactory.php
  - database/migrations/2026_05_30_000001_create_posts_table.php
  - database/seeders/PostMigrationSeeder.php
  - package.json
  - phpunit.xml
  - resources/css/app.css
  - resources/js/app.js
  - resources/js/post-editor.js
  - resources/views/admin/blog/create.blade.php
  - resources/views/admin/blog/edit.blade.php
  - resources/views/admin/blog/index.blade.php
  - resources/views/components/admin/sidebar.blade.php
  - resources/views/components/admin/status-badge.blade.php
  - resources/views/livewire/post-form.blade.php
  - resources/views/livewire/post-index.blade.php
  - routes/admin.php
  - tests/Feature/BlogDbSourceTest.php
  - tests/Feature/PostAdminListTest.php
  - tests/Feature/PostFormTest.php
  - tests/Feature/PostMigrationSeederTest.php
  - tests/Feature/PostModelTest.php
findings:
  critical: 2
  warning: 6
  info: 4
  total: 12
status: issues_found
---

# Phase 6: Code Review Report

**Reviewed:** 2026-05-30
**Depth:** standard
**Files Reviewed:** 26
**Status:** issues_found

## Summary

Blog admin CRUD on Laravel 13 + Livewire 3. The DB read path, 410/404 SEO logic, cache discipline (scalar-array-only cache), slug-lock-on-publish, and EasyMDE `wire:ignore` integration are mostly well-built and well-tested. Two real correctness bugs surfaced, both around the `date` column: it is never populated on the admin write path, which breaks publish ordering and emits a misleading `datePublished` in the public JSON-LD/sitemap. There is also a Blade title-rendering bug that leaks raw `{{ }}` template text into `<title>` on the edit page. The remaining findings are robustness and quality concerns.

Test scope is good but has a notable blind spot: the entire admin write flow (PostForm) is exercised only with `BLOG_SOURCE=files` (phpunit.xml line 45), so the create → publish → public-read round-trip under `source=db` is never asserted end-to-end — which is exactly where the `date`-null bug hides.

## Critical Issues

### CR-01: New/edited posts are saved with `date = NULL` — breaks ordering, SEO date, and sitemap lastmod

**File:** `app/Livewire/PostForm.php:80-88` (and absence at `app/Models/Post.php:47-54`)
**Issue:** `PostForm::submit()` fills only `title, slug, body, excerpt, status, author`. It never sets `date`. The migration declares `date` as `nullable()` (`database/migrations/2026_05_30_000001_create_posts_table.php:19`) with no DB default, and `Post::booted()` only auto-fills `slug`, never `date`. So every post created or edited through the admin UI persists `date = NULL`. Downstream consequences:

1. **Admin list ordering is undefined.** `PostIndex::render()` and `BlogRepository` both `orderByDesc('date')`. On Postgres, `NULL` sorts *first* under `DESC` (NULLS FIRST is the default), so brand-new posts jump to the top inconsistently; on SQLite (test DB) NULLs sort last — so the bug is masked in tests and only manifests on the production Postgres path.
2. **Public SEO date is wrong.** `cacheablePayloadFromDb()` (`BlogRepository.php:125`) and `loadFromDb()` (line 149) fall back to `now()` when `date` is null, so `BlogController::buildArticleSchema()` emits `datePublished`/`dateModified` = *today* on every cache rebuild — the article's publish date silently changes each time the cache expires. `SitemapController` also stamps `lastmod` = now for these posts.

The published `.md`-migrated posts have a real `date` (via the seeder), so this only bites admin-authored posts — the entire point of this phase.

**Fix:** Stamp the date on publish (and seed a created date on draft). In `PostForm::submit()`:
```php
$post->fill([
    'title'   => $this->title,
    'slug'    => $slug,
    'body'    => $this->body,
    'excerpt' => $this->excerpt ?: null,
    'status'  => $this->status ?: 'draft',
    'author'  => $post->author ?: 'Pierre ADAM',
    // Set publish date once, on first transition to published; keep it stable after.
    'date'    => $post->date ?? ($this->status === 'published' ? now() : null),
]);
```
Or give the column a sane default and stamp on publish transition. Add a `source=db` feature test that creates a post via PostForm, publishes it, and asserts `$post->date` is non-null and that two successive `cacheablePayloadFromDb()` calls return a stable `date`.

### CR-02: Edit page `<title>` renders literal `{{ $post->title }}` text

**File:** `resources/views/admin/blog/edit.blade.php:3`
**Issue:** `@section('title', '{{ $post->title }} — Modifier · Dlo Azur')`. The two-argument form of `@section` takes a literal string; Blade does not compile `{{ }}` inside it, and inside a single-quoted PHP string nothing is interpolated. The page title becomes the literal text `{{ $post->title }} — Modifier · Dlo Azur` in the browser tab and in any OG/title meta derived from it. (The create page uses a static string and is fine.)
**Fix:** Use the block form so Blade compiles the expression:
```blade
@section('title'){{ $post->title }} — Modifier · Dlo Azur@endsection
```

## Warnings

### WR-01: PostForm write path is never tested under `source=db` — coverage gap masks CR-01

**File:** `phpunit.xml:45`, `tests/Feature/PostFormTest.php`
**Issue:** `BLOG_SOURCE=files` is forced for the whole suite (phpunit.xml:45). `PostFormTest` never sets `config('blog.source', 'db')`, so the create→publish→public-read round-trip on the DB path is never asserted. The `date`-null bug (CR-01) sails through because SQLite NULL ordering happens to keep older tests green. Tests validate the happy path but not the integration that this phase exists to deliver.
**Fix:** Add a PostForm test that flips `config(['blog.source' => 'db'])`, creates + publishes a post, then asserts it appears in `BlogRepository::all()` with a stable, non-null `date`, and that `GET /blog/{slug}` returns 200.

### WR-02: `addError('save')` swallows the real failure, including cover-upload failures, leaving an orphaned post row

**File:** `app/Livewire/PostForm.php:90-109`
**Issue:** The `$post->save()` and the cover `addMediaFromDisk(...)` are in the same `try`. If `save()` succeeds but the S3 media write throws (Scaleway timeout, bad temp path, etc.), the catch fires *after* the post is already persisted — so the user sees "L'enregistrement a échoué" while a post row actually exists, and on retry `uniqueSlug()` will append `-2` to the slug (the first row already holds the slug). The generic French message also discards the distinct validation/transport cause. Cache::forget is inside the try too, so a media exception skips the cache flush.
**Fix:** Wrap the post create + media attach in a DB transaction, or attach media before signalling success and roll back / delete the post on media failure. At minimum, move `Cache::forget('blog.index')` out of the failure path and distinguish "saved but cover failed" from "save failed".

### WR-03: Cover upload uses original client extension for the stored filename — content/extension mismatch

**File:** `app/Livewire/PostForm.php:95-98`
**Issue:** The validation rule (`mimes:jpg,jpeg,png,webp`) validates the *content* type, but `usingFileName(Str::slug($this->title) . '.' . $this->cover->getClientOriginalExtension())` trusts the client-supplied extension for the persisted filename. A file with real PNG content but uploaded as `evil.jpg` (or odd-cased `.JPG`) is stored under the client extension, producing a filename whose extension may not match the actual MIME. Also, if two posts share a title, both covers serialize to the same filename — collection `singleFile()` scopes per-post so that is contained, but the extension trust is still wrong.
**Fix:** Derive the extension from the validated MIME, not the client string: `$this->cover->extension()` (guesses from content) or map MIME → extension explicitly. Lowercase it.

### WR-04: Livewire temp uploads staged to `livewire-tmp` on S3 are never cleaned up

**File:** `app/Livewire/PostForm.php:95-98`
**Issue:** `$this->cover->store('livewire-tmp', 's3')` writes the temp file to S3, then `addMediaFromDisk()` copies it into the `cover` collection. The original `livewire-tmp/...` object is left behind on Scaleway indefinitely (medialibrary copies, it does not move). Over time every cover upload leaks a duplicate billable object. Livewire's own temp-file GC targets the configured temp disk path, but a manually `store()`-d file outside its tracked lifecycle is not guaranteed to be swept.
**Fix:** Delete the staged temp object after the media is attached: `Storage::disk('s3')->delete($tmpPath);` (in a finally / after the toMediaCollection call), or use `addMediaFromDisk(...)->preservingOriginal(false)` semantics if available, or rely on a scheduled prune of `livewire-tmp`.

### WR-05: PostIndex search interpolates raw user input into a LIKE/ILIKE pattern — wildcard injection

**File:** `app/Livewire/PostIndex.php:30-33`
**Issue:** `$q->where('title', $likeOp, '%' . $this->search . '%')`. The value is bound (no SQL injection), but `%` and `_` in the search term are treated as wildcards rather than literals, so a search for `_` matches every single-char position and `%` matches everything. Not a security hole, but a correctness/robustness defect in user-facing search.
**Fix:** Escape LIKE metacharacters before binding:
```php
$term = addcslashes($this->search, '%_\\');
$q->where('title', $likeOp, '%' . $term . '%');
```

### WR-06: Cover removal in the form deletes the preview but never removes persisted media on save

**File:** `resources/views/livewire/post-form.blade.php:90` + `app/Livewire/PostForm.php:90-99`
**Issue:** The "Retirer" button does `$set('cover', null)`, which clears the *pending upload* property. But `submit()` only ever *adds* media when `$this->cover` is truthy — there is no code path that clears an already-persisted cover from the `cover` collection. So an editor who opens a published post and clicks "Retirer" sees the preview disappear, saves, and the old cover remains attached on S3 and still renders on the public blog. The UI implies a destructive action that the backend never performs.
**Fix:** Track an explicit `removeCover` boolean and in `submit()` call `$post->clearMediaCollection('cover')` when set. Note: mount() never hydrates the existing cover into the preview either (comment at PostForm.php:59), so the edit form cannot even show the current cover — the whole "replace/remove existing cover" UX is non-functional.

## Info

### IN-01: `excerpt` validated `max:300` but stored in a `TEXT` column with no DB constraint

**File:** `database/migrations/...create_posts_table.php:16`, `app/Livewire/PostForm.php:37`
**Issue:** `title` is `string` (255) with `Validate max:160`; `status` is `string(16)` with `Validate max:16`. `excerpt` is `text` but validated `max:300` — fine, but a direct write (seeder, tinker, future API) bypassing the Livewire rule can store an arbitrarily long excerpt that then feeds OG `<meta description>`. Low risk; noting the validation/schema asymmetry.
**Fix:** Acceptable as-is; if excerpt is meta-description-bound, consider a column length or a model-level mutator/truncation.

### IN-02: PostIndex list triggers a media query per row (N+1) on the cover thumbnail

**File:** `resources/views/livewire/post-index.blade.php:37`
**Issue:** `$post->getFirstMediaUrl('cover', 'thumbnail')` inside the `@forelse` loads the media relation lazily per post — 25 extra queries per page. Flagged as INFO only because performance is out of v1 review scope, but it is a real query amplification on the admin list.
**Fix:** Eager-load media in `PostIndex::render()`: `Post::query()->with('media')...`.

### IN-03: `reading_time` word-count logic duplicated three times

**File:** `app/Support/BlogRepository.php:131, 155, 223`
**Issue:** `max(1, (int) round(str_word_count(strip_tags(...)) / 200))` is copy-pasted in `cacheablePayloadFromDb`, `loadFromDb`, and `parse`. Same for the per-post array shape (10 keys) duplicated between `cacheablePayloadFromDb` and `loadFromDb`. Drift risk: a change to one path silently diverges the file vs DB shape.
**Fix:** Extract a private `readingTime(string $body): int` helper and a single `postArray(...)` mapper that both DB methods share, parameterized only on whether `date` is a Carbon or ISO string.

### IN-04: Stale "PostForm arrives in Plan 04" comments in shipped views

**File:** `resources/views/admin/blog/create.blade.php:28`, `resources/views/admin/blog/edit.blade.php:28`
**Issue:** Both Blade cards carry the comment `{{-- Form card (PostForm arrives in Plan 04) --}}` even though `<livewire:post-form />` is already wired right below. Leftover scaffolding comment, mildly misleading.
**Fix:** Remove or update the comment.

---

_Reviewed: 2026-05-30_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
