# Phase 6: blog-admin-crud - Context

**Gathered:** 2026-05-30
**Status:** Ready for planning

<domain>
## Phase Boundary

Give Pierre (non-dev) publication autonomy over the blog from `/admin/blog` — create, edit, and unpublish articles with no code/git involvement. Introduce a `Post` Eloquent model + Postgres migration, an admin CRUD (list / create / edit / unpublish) built on the **existing Clients CRUD pattern** (thin GET controllers for views + Livewire 3 write components, under `middleware(['web','auth'])->prefix('admin')->name('admin.')`), a Markdown editor, and flip `BlogRepository` from flat Markdown files → DB while migrating the 3 existing articles. Must preserve all Phase 999.1 blog SEO acquis: `og:type=article`, Article JSON-LD, real publish dates, sitemap entries.

**Not in scope (new capabilities → own phase):** comments, categories/tags, multi-author roles, blog search/filtering, post analytics, RSS feed, related-posts algorithm beyond the existing "à lire aussi" 2-sibling footer.

</domain>

<decisions>
## Implementation Decisions

### Markdown editor (D-01)
- **D-01:** Use **EasyMDE** (npm `easymde`) for the post body field. Keeps the body stored as **raw Markdown** — preserves the existing `<x-markdown>` (CommonMark) render path, the `reading_time` word-count, and clean diffs. Gives Pierre a toolbar (bold/italic/lists/links/image) + split-pane live preview.
  - Integration: `wire:ignore` container, init in Alpine `x-init`, sync via `codemirror.on('change') → $wire.set('body', editor.value())` (~15 lines).
  - **Tailwind 4 risk:** scope EasyMDE's stylesheet under a wrapper class (e.g. `.easymde-wrap`) in a `@layer` block in `resources/css/app.css` so Tailwind's reset does not stomp editor styles.
- **Rejected:** Tiptap/Trix WYSIWYG — emits HTML, which cascades into the storage format, `reading_time`, `<x-markdown>` renderer, and migration. Plain textarea — rejected (Pierre is not Markdown-fluent; defeats the autonomy goal).

### Cover image (D-02)
- **D-02:** Cover image via **Livewire file upload → `spatie/laravel-medialibrary` → Scaleway S3**. medialibrary is already locked in the stack. Use a `cover` collection with a **thumbnail conversion** (≈1200×630 for og:image + fast blog index). Expose SEO URL via `$post->getFirstMediaUrl('cover', 'thumbnail')` to replace the fragile front-matter path string.
  - **Serverless config required:** `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` + Livewire S3 temp-upload cleanup; Scaleway bucket CORS must allow browser `PUT` to the `livewire-tmp/` prefix.
  - **Note / consistency call for planner:** existing Passage photos deliberately use **raw `Storage::disk('s3')`, NOT medialibrary** ("§Pitfall 6"). This phase intentionally diverges to get auto-thumbnails + Eloquent association for covers. Planner: confirm this divergence is acceptable or align both — but medialibrary is the chosen default here.
- The 3 migrated articles have **no cover** → existing `asset('assets/brand/og-default.jpg')` fallback in `buildArticleSchema()` / `<x-seo.meta>` keeps working with zero data migration.

### Publish / unpublish model (D-03)
- **D-03:** `status enum('draft','published')` column, default `draft`. Maps to Pierre's mental model ("brouillon / publié") via a single admin toggle.
  - **Public visibility gate:** a `scopePublished()` (`where('status','published')`) applied to every PUBLIC query path — `BlogRepository::all()` / `find()`, and `SitemapController`. Admin list shows ALL posts.
  - **Unpublished URL behavior:** a post that exists in DB but is not published returns **`abort(410)` (Gone)** from `BlogController::show()` when it was previously live/indexed (fast Googlebot de-index); a never-published draft slug → plain **404**. Distinguish intentional removal from missing resource.
  - The 3 migrated articles get `status = 'published'`.
- **Rejected:** `published_at` timestamp (unintuitive "null=draft" for a non-dev; scheduling not needed now) and 3-value enum with `archived` (overkill for single-author).

### Slug + SEO continuity (D-04)
- **D-04:** **Auto-slug from title** (`Str::slug`) at draft time, **editable while `status=draft`**, **locked (read-only) once `status=published`**. Zero redirect infrastructure, zero SEO risk. Rare post-publish corrections handled via a documented one-liner runbook (`Post::where('slug','old')->update(['slug'=>'new'])`), not a redirects table.
- **Rejected:** editable-anytime + auto-301 `post_redirects` table (extra migration/middleware/chain risk; no anticipated need — additive later if blog scales); permanently-locked-from-first-save (typo-forever friction).
- **Slug uniqueness:** unique index on `slug` column; `[a-z0-9-]+` to match existing `blog.show` route constraint.

### Migration of the 3 existing articles (D-05)
- **D-05:** **Idempotent seeder** that reuses the existing `BlogRepository` parser and `Post::updateOrCreate(['slug' => $slug], [...])`. Preserves byte-for-byte: title, the 3 canonical slugs, real `date` (datePublished/dateModified), `show_date` (two legacy articles carry `show_date:false`), `author` (default "Pierre ADAM"), `excerpt`, `cover` (null).
  - Re-runnable safely (upsert keyed on slug).
- **D-06:** **Keep** `resources/content/blog/*.md` files as backup; do NOT delete. Remove them from the `BlogRepository` read path behind a **config flag** (files→DB cutover) so rollback is one flag flip.

### Claude's Discretion
- CRUD wiring shape (controllers vs Livewire split) — follow the established Clients pattern (`ClientController` GET views + `ClientForm`/`ClientIndex` Livewire write/list). Planner decides exact component/route names.
- Migration mechanism is locked as a seeder (D-05); the seeder must be guarded from unintended production auto-run or be safe to re-run (idempotent upsert satisfies this).
- `Post` schema columns beyond those named (timestamps, indexes) — planner's call, must cover all parsed fields above.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Phase scope & requirements
- `.planning/ROADMAP.md` — Phase 6 entry (goal, dependency on 999.1, SEO preservation mandate)
- `.planning/REQUIREMENTS.md` — `SITE-07` (SEO local Martinique: meta, sitemap, structured data); candidate new `CONTENT-xx` req to derive at planning
- `.planning/phases/999.1-seo-launch-readiness-post-cutover-optimization/999.1-CONTEXT.md` — blog SEO decisions to preserve (Article schema, og:type, real dates, sitemap lastmod)

### Blog data + SEO (the files this phase rewires)
- `app/Support/BlogRepository.php` — current file-parser (YAML front matter → array); `all()`/`find(slug)`/`cacheablePayload()`; **cache gotcha: serializable_classes=false → cache a plain scalar array, never a Collection/Carbon (#blog-cache-incomplete-class)**. The DB flip must honor this if any caching stays.
- `app/Http/Controllers/BlogController.php` — `index()`, `show()` (404 via `abort_unless`), `buildArticleSchema()` (Article JSON-LD: headline, datePublished/dateModified, author, image, mainEntityOfPage, publisher). 410 logic lands here.
- `app/Http/Controllers/SitemapController.php` §37-43 — appends blog URLs if `BlogRepository` bound; must respect `scopePublished()`.
- `resources/views/blog/show.blade.php` §48 — `<x-markdown>{!! $post['body'] !!}</x-markdown>` (the render path Markdown storage must keep feeding).
- `routes/blog.php` §19-20 — `blog.index` / `blog.show` (slug `[a-z0-9-]+`).
- `resources/content/blog/*.md` — the 3 articles to migrate (2 with `show_date:false`).

### Admin CRUD pattern to mirror
- `routes/admin.php` — route group + Clients CRUD route shape (GET views; writes via Livewire).
- `app/Http/Controllers/Admin/ClientController.php` — thin GET controllers (index/create/show/edit).
- `app/Livewire/ClientForm.php`, `app/Livewire/ClientIndex.php` — Livewire write/list component pattern (`#[Validate]`, `mount(?int $id)`, `submit()`).
- `resources/views/layouts/admin.blade.php` + `resources/views/components/admin/sidebar.blade.php` — admin shell + nav (add Blog entry).

### Media (cover upload)
- Existing Passage photo handling using raw `Storage::disk('s3')` ("§Pitfall 6") — reference for the medialibrary-vs-raw consistency call (D-02). `config/filesystems.php` `s3` disk (Scaleway endpoint).

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `BlogRepository::parse()` — reuse directly in the migration seeder (D-05); already handles every field + slug fallback + Carbon dates.
- Clients CRUD (`ClientController` + `ClientForm` + `ClientIndex`) — direct template for `Post` CRUD (validation attributes, mount/submit, index list).
- `spatie/laravel-medialibrary` (in stack, wired to Scaleway `s3`) — cover upload + thumbnail conversion.
- `<x-markdown>` Blade component + `buildArticleSchema()` — keep as-is; body stays Markdown so both keep working unchanged.
- Static 301 redirect pattern already in `routes/web.php` (legacy Zyro URLs) — reference if editable-slug+301 is ever revisited (not this phase).

### Established Patterns
- Admin = `['web','auth']->prefix('admin')->name('admin.')`; reads via controllers, writes via Livewire. Follow it.
- Public blog reads go through `BlogRepository`; keeping that interface (`all()`/`find()`) lets `BlogController`/`SitemapController` stay untouched aside from the `scopePublished()` gate.
- Cache discipline: `serializable_classes=false` — any DB-side caching must cache scalar arrays, not Eloquent models/Collections.

### Integration Points
- `BlogRepository` internal swap files→Eloquent (behind config flag, D-06) — the single highest-leverage change; keep the public method signatures stable.
- `SitemapController` blog hook — must filter to published only.
- `BlogController::show()` — add 410 branch for unpublished-but-existing posts.
- Admin sidebar nav — add `/admin/blog` link.

</code_context>

<specifics>
## Specific Ideas

- Pierre's vocabulary: "brouillon / publié" — admin UI status labels should use these French terms.
- og:image dimensions target ≈1200×630 for the cover thumbnail conversion.
- Migration seeder must land the exact 3 canonical slugs:
  - `de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines`
  - `les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique`
  - `bienvenue-dlo-azur` (from `2026-05-bienvenue-dlo-azur.md`)

</specifics>

<deferred>
## Deferred Ideas

- **Scheduled publishing** (future-dated `published_at`) — not needed now; revisit if Pierre asks. Would migrate the `status` enum toward a `published_at` timestamp.
- **Editable-slug-after-publish + 301 redirect table** (`post_redirects`) — additive migration if the blog scales to content-marketing volume.
- **Categories / tags, comments, blog search, RSS, multi-author** — each a separate capability/phase.

None of the above were in scope — discussion stayed within the phase boundary.

</deferred>

---

*Phase: 6-blog-admin-crud*
*Context gathered: 2026-05-30*
