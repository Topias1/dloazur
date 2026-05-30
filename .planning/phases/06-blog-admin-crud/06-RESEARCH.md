# Phase 6: blog-admin-crud - Research

**Researched:** 2026-05-30
**Domain:** Laravel 13 CRUD admin / EasyMDE / Livewire 3 file upload / spatie/laravel-medialibrary v11 / Tailwind 4 CSS-first
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- **D-01** EasyMDE (`npm easymde`) for body; `wire:ignore` + Alpine `x-init` + CodeMirror `on('change')` → `$wire.set('body', editor.value())` sync; scope stylesheet under `.easymde-wrap` in `@layer`
- **D-02** Cover via Livewire upload → `spatie/laravel-medialibrary` → Scaleway S3; `cover` collection + thumbnail conversion ≈1200×630; `getFirstMediaUrl('cover','thumbnail')`; `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` + Scaleway CORS for `livewire-tmp/`
- **D-03** `status string(16) default('draft')` with `scopePublished()` (`where('status','published')`); 410 for unpublished-but-indexed, 404 for draft-only; migrated articles → `published`
- **D-04** Slug auto from `Str::slug`, editable while draft, locked (read-only) once published; unique index; `[a-z0-9-]+`
- **D-05** Idempotent seeder `Post::updateOrCreate(['slug'=>...])` reusing `BlogRepository::parse()`; preserves title, slug, date, show_date, author, excerpt, cover=null
- **D-06** Keep `resources/content/blog/*.md` as backup; files→DB flip behind `config/blog.php 'source'` env flag; rollback = one env flip

### Claude's Discretion

- CRUD wiring shape (controllers vs Livewire split) — follow established Clients pattern (`ClientController` GET views + `ClientForm`/`ClientIndex` Livewire write/list)
- Exact component/route names
- `Post` schema columns beyond those named (timestamps, indexes)
- Seeder production-guard mechanism (idempotent upsert satisfies re-runnability)

### Deferred Ideas (OUT OF SCOPE)

- Scheduled publishing (future-dated `published_at`)
- Editable-slug-after-publish + 301 redirect table (`post_redirects`)
- Categories/tags, comments, blog search, RSS, multi-author
</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| SITE-07 | Pages publiques SEO local Martinique (balises meta, sitemap, données structurées) | Blog SEO preserved: `scopePublished()` gates sitemap; 410 enables fast de-index; Article JSON-LD, og:type, real dates all preserved via unchanged `buildArticleSchema()` / `BlogController` |
| CONTENT-01 *(candidate)* | Pierre peut créer, éditer et dépublier des articles de blog depuis `/admin/blog` sans coder | Full CRUD: `PostController` GET views + `PostForm`/`PostIndex` Livewire components; EasyMDE body editor; cover upload; status toggle |
</phase_requirements>

---

## Summary

Phase 6 is a well-bounded admin CRUD with two genuine technical integration risks: the EasyMDE × Tailwind 4 CSS clash, and the Livewire 3 file upload → medialibrary → Scaleway S3 serverless chain. Everything else mirrors the already-proven Clients CRUD pattern in this repo.

The `BlogRepository` swap (files→Eloquent) is the single highest-leverage architectural change. Its public interface (`all()`, `find(string $slug): ?array`) must remain stable so `BlogController`, `SitemapController`, and `BlogTest.php` require zero changes. The cache discipline is critical: `serializable_classes=false` means the DB-backed implementation must cache a plain scalar array (same as the file-backed version), never an Eloquent Collection.

The seeder pattern to follow is `AdminSeeder` — production-safe standalone, `updateOrCreate` keyed on slug, runs via `php artisan db:seed --class=PostMigrationSeeder --force`.

**Primary recommendation:** Follow the Clients CRUD pattern exactly; research-verified EasyMDE init pattern below; use medialibrary `registerMediaCollections` with nested `registerMediaConversions` + `singleFile()` on the `cover` collection.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Blog post CRUD (create/edit/unpublish) | Admin UI (Livewire 3) | API/Backend (Eloquent) | Operator-only action behind `auth` middleware; follows Clients CRUD pattern |
| Cover image upload | Livewire temp → S3 direct | medialibrary conversion queue | Serverless: never hits local disk; medialibrary queued conversion for thumbnail |
| Markdown body editing | Browser (EasyMDE / CodeMirror) | Livewire sync via `$wire.set` | Editor runs entirely client-side; Livewire only holds the string value |
| Public blog read (index/show) | Backend (BlogRepository → Eloquent) | Cache (scalar array) | `BlogController`/`SitemapController` consume the `BlogRepository` interface unchanged |
| SEO: sitemap, JSON-LD, og:type | Backend (BlogController + SitemapController) | — | Untouched except `scopePublished()` gate on sitemap loop |
| Slug generation/locking | Backend (Post model save hook) | Admin UI (read-only field) | `Str::slug` on create, locked in model after publish |

---

## Standard Stack

### Core (already installed)
| Library | Version | Purpose | Why |
|---------|---------|---------|-----|
| `spatie/laravel-medialibrary` | 11.23.0 (installed) | Cover upload, thumbnail conversion, S3 storage | Already in stack [VERIFIED: composer show] |
| Livewire 3 | 3.x (in stack) | Admin CRUD write components | Established pattern [VERIFIED: CLAUDE.md stack lock] |
| Alpine.js 3 | 3.15.12 (installed) | EasyMDE init glue | Already in project [VERIFIED: package.json] |
| Tailwind 4 | 4.3.0 (installed) | Styling with CSS-first `@theme` | Stack lock [VERIFIED: package.json] |

### New Dependency
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| `easymde` (npm) | 2.21.0 | Markdown toolbar editor for Pierre | D-01 locked; maintained (published 2026-05-03); GitHub: ionaru/easy-markdown-editor [VERIFIED: npm registry] |

### Supporting (no new installs)
| Library | Purpose | Note |
|---------|---------|------|
| `spatie/yaml-front-matter` | Parse `.md` files in migration seeder | Already in stack (used by `BlogRepository`) |
| `league/flysystem-aws-s3-v3` | S3 driver for Scaleway bucket | Bundled with Laravel; `s3` disk already configured |

**Installation:**
```bash
npm install easymde
```

No new Composer packages required.

---

## Package Legitimacy Audit

> slopcheck was unavailable — manual verification performed.

| Package | Registry | Age | Source Repo | postinstall | Disposition |
|---------|----------|-----|-------------|-------------|-------------|
| `easymde` | npm | ~7 years | github.com/Ionaru/easy-markdown-editor | none | Approved [ASSUMED — slopcheck unavailable] |

**slopcheck unavailable at research time.** All packages tagged `[ASSUMED]`. The planner should add a `checkpoint:human-verify` before the `npm install easymde` task.

Manual signals (positive): canonical GitHub repository with 2k+ stars, published 2026-05-03 (v2.21.0), no postinstall script, well-known successor to SimpleMDE.

---

## Architecture Patterns

### System Architecture Diagram

```
Pierre (browser)
  │
  ├─► GET /admin/blog/*   →  PostController (thin GET)  →  admin.posts.* views
  │                                ↓
  │                     [livewire.post-form | livewire.post-index]
  │
  ├─► file input         →  Livewire WithFileUploads  →  S3 livewire-tmp/
  │   (cover image)             (direct PUT, CORS)
  │                                ↓ on submit
  │                     Post::addMedia(tmp)->toMediaCollection('cover')
  │                             ↓ queued
  │                     Scaleway S3 dloazur-media/posts/{id}/cover.*
  │                     + thumbnail conversion (1200×630)
  │
  └─► EasyMDE (browser)  →  codemirror.on('change')  →  $wire.set('body', editor.value())
                                                              ↓
                                                         Post.body (raw markdown)

Public read path (unchanged interface):
  GET /blog/*  →  BlogController  →  BlogRepository::all()/find()
                                         ↓  (D-06 flag: 'source' => 'db')
                                     Post::scopePublished()->orderByDesc('date')
                                         ↓ returns scalar array (same shape)
                                     Cache (plain scalar array, no objects)

GET /sitemap.xml  →  SitemapController  →  BlogRepository::all() → scopePublished()
```

### Recommended Project Structure

```
app/
├── Http/Controllers/Admin/
│   └── PostController.php          # mirrors ClientController (thin GET only)
├── Livewire/
│   ├── PostForm.php                # mirrors ClientForm (#[Validate], mount(?int $id), submit())
│   └── PostIndex.php               # mirrors ClientIndex (WithPagination, search)
├── Models/
│   └── Post.php                    # HasMedia + InteractsWithMedia, scopePublished, registerMediaCollections
├── Providers/
│   └── BlogServiceProvider.php     # swap singleton to DB-backed repo behind config flag
└── Support/
    └── BlogRepository.php          # add all()/find() DB path; keep file path; flag routes via config

database/
├── migrations/
│   └── YYYY_MM_DD_create_posts_table.php
└── seeders/
    └── PostMigrationSeeder.php     # prod-safe, updateOrCreate keyed on slug

resources/
├── css/app.css                     # add .easymde-wrap scope block
├── views/
│   ├── admin/posts/
│   │   ├── index.blade.php         # embeds <livewire:post-index>
│   │   ├── create.blade.php        # embeds <livewire:post-form>
│   │   └── edit.blade.php          # embeds <livewire:post-form :postId="$post->id">
│   └── livewire/
│       ├── post-form.blade.php     # contains .easymde-wrap div
│       └── post-index.blade.php

config/
└── blog.php                        # 'source' => env('BLOG_SOURCE', 'db')
```

### Pattern 1: EasyMDE × Livewire 3 × Tailwind 4

**What:** Initialize EasyMDE via Alpine `x-init` inside a `wire:ignore` container. Sync changes back to Livewire via `codemirror.on('change')`. Scope EasyMDE's stylesheet in `app.css` to prevent Tailwind preflight from stomping toolbar/preview styles.

**Key pitfall:** Alpine `x-init` does NOT re-run after Livewire re-renders (since Alpine 3.14.4 regression, confirmed in alpinejs/alpine#4453). The `wire:ignore` directive prevents Livewire from morphing the editor DOM — this is the correct fix. Do NOT rely on `document.addEventListener('livewire:navigated')` for re-init; use `wire:ignore` to keep the DOM stable.

**Blade template pattern:**
```html
{{-- Source: livewire.laravel.com/docs/3.x/wire-ignore --}}
<div class="easymde-wrap" wire:ignore
     x-data="{}"
     x-init="
         const editor = new EasyMDE({
             element: $refs.bodyEditor,
             spellChecker: false,
             autosave: { enabled: false },
             toolbar: ['bold','italic','heading','|','unordered-list','ordered-list','link','|','preview','side-by-side','fullscreen'],
         });
         editor.codemirror.on('change', () => {
             $wire.set('body', editor.value(), false); // false = no debounce wait
         });
         // Populate on edit (value already in $wire.body from mount())
         if ($wire.body) { editor.value($wire.body); }
     ">
    <textarea id="body-editor" x-ref="bodyEditor"></textarea>
</div>
<input type="hidden" wire:model="body">
{{-- Hidden input keeps Livewire's wire:model binding for validation error display --}}
```

**Note:** `$wire.set('body', editor.value(), false)` — the third argument `false` disables the built-in Livewire debounce and fires immediately. Use it to avoid stale body on fast submit.

**CSS scoping in `resources/css/app.css`:**
```css
/* Source: Tailwind v4 docs — @layer components is the correct slot */
@layer components {
    /* Scope EasyMDE stylesheet under .easymde-wrap to prevent Tailwind preflight
       from resetting CodeMirror toolbar icons, preview padding, and button borders.
       Import easymde/dist/easymde.min.css globally in app.js (not here), then
       override the conflicting reset side-effects scoped to this wrapper. */
    .easymde-wrap .CodeMirror {
        border-radius: 0.5rem;
        font-family: ui-monospace, monospace;
        font-size: 0.875rem;
    }
    .easymde-wrap .editor-toolbar button {
        /* restore Tailwind-reset button borders */
        border: 1px solid var(--color-navy-200);
        background: var(--color-sand-50);
        color: var(--color-ink-700);
    }
    .easymde-wrap .editor-toolbar button:hover,
    .easymde-wrap .editor-toolbar button.active {
        background: var(--color-azure-100);
    }
    .easymde-wrap .editor-preview {
        background: var(--color-sand-50);
        padding: 1rem;
    }
}
```

**`app.js` import (at top, before Alpine):**
```js
import 'easymde/dist/easymde.min.css';
import EasyMDE from 'easymde';
window.EasyMDE = EasyMDE; // expose to Alpine x-init scope
```

**Tailwind v4 specifics — no `tailwind.config.js`:** The existing `app.css` opens with `@import "tailwindcss"` which pulls in preflight. Do NOT disable preflight globally (it would break the rest of the app). The scoped `.easymde-wrap` wrapper pattern in `@layer components` is the correct v4 approach — it overrides only within the editor. [CITED: tailwindcss.com/docs/preflight]

### Pattern 2: Livewire 3 File Upload → medialibrary → Scaleway S3

**What:** Livewire `WithFileUploads` handles browser-to-S3 direct upload via temporary signed URL. On form submit, `addMedia()` moves from `livewire-tmp/` to the final media collection.

**Component PHP:**
```php
// Source: livewire.laravel.com/docs/3.x/uploads
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class PostForm extends Component
{
    use WithFileUploads;

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:4096')]
    public $cover = null; // TemporaryUploadedFile|null

    // ... other fields

    public function submit(): void
    {
        $this->validate();

        $post = Post::updateOrCreate(...);

        if ($this->cover) {
            $post->addMedia($this->cover->getRealPath())
                 ->usingFileName(Str::slug($this->title) . '.' . $this->cover->getClientOriginalExtension())
                 ->toMediaCollection('cover', 's3');
        }
    }
}
```

**Note on `getRealPath()`:** When `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3`, the TemporaryUploadedFile exists in S3, not on the local filesystem. Use `$this->cover->store('livewire-tmp', 's3')` to get the path, then `addMediaFromDisk($path, 's3')`. Alternatively, keep `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` for the temporary stage and only persist to S3 on the medialibrary `toMediaCollection` call — simpler on serverless since the PHP process still has `/tmp` access. [ASSUMED — serverless `/tmp` availability on Laravel Cloud must be verified at execution time]

**Post model:**
```php
// Source: spatie.be/docs/laravel-medialibrary/v11/working-with-media-collections/defining-media-collections
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
             ->singleFile()        // replaces previous cover on re-upload
             ->useDisk('s3')
             ->registerMediaConversions(function (Media $media) {
                 $this->addMediaConversion('thumbnail')
                      ->width(1200)
                      ->height(630)
                      ->nonQueued(); // sync on serverless — no queue needed for single cover
             });
    }
}
```

**SEO URL:**
```php
$post->getFirstMediaUrl('cover', 'thumbnail')
// Returns null if no cover — fallback to asset('assets/brand/og-default.jpg') in buildArticleSchema()
```

**Scaleway CORS** — must allow browser `PUT` to `livewire-tmp/` prefix. CORS JSON for the bucket:
```json
{
  "CORSRules": [{
    "AllowedHeaders": ["*"],
    "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
    "AllowedOrigins": ["https://dloazurpiscines.com", "https://*.laravel.cloud"],
    "MaxAgeSeconds": 3600
  }]
}
```
Apply via AWS CLI: `aws s3api put-bucket-cors --bucket dloazur-media --cors-configuration file://cors.json --endpoint-url https://s3.fr-par.scw.cloud` [CITED: scaleway.com/en/docs/object-storage/api-cli/setting-cors-rules/]

**S3 temp cleanup:**
```bash
php artisan livewire:configure-s3-upload-cleanup
```
Sets a 24h lifecycle rule on `livewire-tmp/`. Run once per environment. [CITED: livewire.laravel.com/docs/3.x/uploads]

### Pattern 3: Clients CRUD → Post CRUD Translation

**Exact Clients pattern extracted from codebase:**

**Routes (`routes/admin.php`):**
```php
// GET views only; writes via Livewire
Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
```

**Post equivalent (add to `routes/admin.php`):**
```php
Route::get('blog', [PostController::class, 'index'])->name('blog.index');
Route::get('blog/create', [PostController::class, 'create'])->name('blog.create');
Route::get('blog/{post}', [PostController::class, 'show'])->name('blog.show');
Route::get('blog/{post}/edit', [PostController::class, 'edit'])->name('blog.edit');
```

**Controller (`App\Http\Controllers\Admin\PostController`):**
```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostController extends Controller
{
    public function index()    { return view('admin.posts.index'); }
    public function create()   { return view('admin.posts.create'); }
    public function show(Post $post) { return view('admin.posts.show', compact('post')); }
    public function edit(Post $post) { return view('admin.posts.edit', compact('post')); }
}
```

**Livewire component pattern (from `ClientForm`):**
- Namespace: `App\Livewire\PostForm`
- Properties: `#[Validate(...)]` attributes on each field
- `public ?int $postId = null;`
- `mount(?int $postId = null)` — loads `Post::findOrFail($postId)` and hydrates properties
- `submit()` — validates, upserts, dispatches `post-saved`, redirects to `admin.blog.index`
- `render()` → `view('livewire.post-form')`

**`PostIndex` follows `ClientIndex`:**
- `use WithPagination;`
- `public string $search = '';`
- `updatedSearch()` → `$this->resetPage();`
- Query: `Post::query()->when($search, ...)->orderByDesc('date')->paginate(25)` — shows ALL statuses (admin sees drafts too)
- `render()` → `view('livewire.post-index')`

### Pattern 4: Post Migration Schema

Based on D-03/D-04/D-05 and the established project migration pattern (uses `->string('status', 16)->default('draft')`, NOT native Postgres `->enum()`):

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();    // index implicit from unique()
    $table->text('body');               // raw markdown
    $table->text('excerpt')->nullable();
    $table->string('status', 16)->default('draft');  // 'draft'|'published'
    $table->string('author')->default('Pierre ADAM');
    $table->date('date')->nullable();   // datePublished/dateModified for SEO
    $table->boolean('show_date')->default(true);
    $table->timestamps();

    $table->index(['status', 'date']); // compound for scopePublished + orderByDesc
});
```

**Why `->string()` not `->enum()`:** The existing codebase uses `string(16)` for status columns (see `passages` and `factures` migrations). On Postgres 17, Laravel's `->enum()` compiles to varchar(255)+check constraint anyway — but `string()` is simpler to modify later (D-06: scheduled publishing would add `archived`). Consistent with project convention. [VERIFIED: codebase grep]

**`scopePublished()` on `Post` model:**
```php
public function scopePublished(Builder $query): Builder
{
    return $query->where('status', 'published');
}
```

**Slug auto-generation:**
```php
protected static function booted(): void
{
    static::creating(function (Post $post) {
        if (empty($post->slug)) {
            $post->slug = Str::slug($post->title);
        }
    });
}
```
Slug is locked in the form (read-only `<input>`) once `status=published`, enforced in `PostForm::submit()` by skipping slug update if published.

### Pattern 5: BlogRepository Files→DB Swap

**Config flag mechanism (D-06):**
```php
// config/blog.php
return [
    'source' => env('BLOG_SOURCE', 'db'), // 'db' | 'files'
];
```

**`BlogRepository::all()` — dual path:**
```php
public function all(): Collection
{
    if (config('blog.source') === 'db') {
        return $this->allFromDb();
    }
    // existing file path unchanged...
}

private function allFromDb(): Collection
{
    if (app()->environment('testing')) {
        return $this->loadFromDb();
    }
    return $this->hydrateDates(collect(
        Cache::remember('blog.index', 60 * 60, fn () => $this->cacheablePayloadFromDb())
    ));
}

public function cacheablePayloadFromDb(): array
{
    // MUST return plain scalar array — same discipline as file-backed version
    // (#blog-cache-incomplete-class: serializable_classes=false)
    return Post::scopePublished()
        ->orderByDesc('date')
        ->get()
        ->map(fn (Post $p): array => [
            'title'        => $p->title,
            'slug'         => $p->slug,
            'date'         => $p->date?->toIso8601String() ?? now()->toIso8601String(),
            'show_date'    => $p->show_date,
            'excerpt'      => (string) $p->excerpt,
            'author'       => $p->author,
            'cover'        => $p->getFirstMediaUrl('cover', 'thumbnail') ?: null,
            'body'         => $p->body,
            'reading_time' => max(1, (int) round(str_word_count(strip_tags($p->body)) / 200)),
            'filepath'     => null, // no file on DB path
        ])
        ->all(); // returns plain PHP array, not Collection
}
```

**`find(string $slug): ?array`** — unchanged signature; DB path: `Post::scopePublished()->where('slug', $slug)->first()` then map to same array shape. This keeps `BlogController::show()` and all callers untouched.

**Cache invalidation:** Existing `Cache::remember('blog.index', 3600, ...)` key must be busted when a post is published/unpublished. Add `Cache::forget('blog.index')` in `PostForm::submit()` after status change.

### Pattern 6: PostMigrationSeeder

Follows `AdminSeeder` pattern — standalone, production-safe via idempotent upsert:

```php
class PostMigrationSeeder extends Seeder
{
    /**
     * Idempotent migration of the 3 canonical blog .md files to the posts table.
     * Safe to re-run: updateOrCreate keyed on slug yields 1 row per slug.
     * Production-safe: upsert never overwrites manually-edited DB content if slug matches.
     *
     * Run via: php artisan db:seed --class=PostMigrationSeeder --force
     */
    public function run(): void
    {
        $repo = new BlogRepository();
        $files = [
            resource_path('content/blog/2026-05-bienvenue-dlo-azur.md'),
            resource_path('content/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines.md'),
            resource_path('content/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique.md'),
        ];

        foreach ($files as $path) {
            // BlogRepository::parse() is private — expose via a public parseFile() or use
            // YamlFrontMatter directly here (same logic). See pitfall note below.
            $parsed = YamlFrontMatter::parseFile($path);
            $basename = basename($path, '.md');
            $slug = $parsed->matter('slug') ?: $basename;

            Post::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'     => $parsed->matter('title', ''),
                    'body'      => (string) $parsed->body(),
                    'excerpt'   => $parsed->matter('excerpt', ''),
                    'author'    => $parsed->matter('author') ?: 'Pierre ADAM',
                    'date'      => $parsed->matter('date') ? Carbon::parse($parsed->matter('date'))->toDateString() : now()->toDateString(),
                    'show_date' => filter_var($parsed->matter('show_date', true), FILTER_VALIDATE_BOOLEAN),
                    'status'    => 'published',
                ]
            );
        }
    }
}
```

**Pitfall note:** `BlogRepository::parse()` is `private`. Options: (a) make it `public` or `protected` (preferred — it's reusable logic), (b) duplicate the parsing inline in the seeder using `YamlFrontMatter` directly (acceptable, ~10 lines). Recommend option (a): refactor `parse()` to `public` in the same PR.

### Pattern 7: 410 vs 404 in BlogController::show()

Current code uses `abort_unless($post, 404)`. Add 410 branch:

```php
public function show(BlogRepository $blog, string $slug): View
{
    $post = $blog->find($slug); // uses scopePublished() in DB path

    if (! $post) {
        // Check if a draft exists (DB path only — files path has no unpublished)
        if (config('blog.source') === 'db' && Post::where('slug', $slug)->exists()) {
            abort(410); // was published, now draft → HTTP 410 Gone
        }
        abort(404);
    }
    // ... rest unchanged
}
```

**Why this matters:** `abort(410)` tells Googlebot the URL is intentionally gone (fast de-index). `abort(404)` signals "not found" (slower de-index, Googlebot re-crawls). This is the SEO-critical distinction from D-03.

### Anti-Patterns to Avoid

- **Storing Eloquent models/Collections in cache:** `serializable_classes=false` in `config/cache.php` will turn any cached object into `__PHP_Incomplete_Class`. Always flatten to plain scalar arrays before caching. See `BlogRepository::cacheablePayload()` for the established pattern. [VERIFIED: codebase + BlogTest.php]
- **Using Livewire `wire:model` directly on a textarea for EasyMDE:** EasyMDE replaces the textarea with CodeMirror DOM. Livewire's DOM morphing will destroy the editor on re-render. Use `wire:ignore` on the wrapper and sync via `codemirror.on('change')`. [CITED: livewire.laravel.com/docs/3.x/wire-ignore]
- **Using native Postgres `->enum()` type:** The project uses `->string(16)` for status columns. Postgres native enums require DDL changes to add values later. [VERIFIED: codebase migrations]
- **Using the Browsershot PDF driver** on Laravel Cloud serverless — not relevant to this phase but noted because medialibrary image conversions using Browsershot would have the same Node.js constraint. The `->nonQueued()` GD/Imagick conversion used here has no binary dependency.
- **Running PostMigrationSeeder via DatabaseSeeder:** `DatabaseSeeder` is env-gated to local/testing only. Post migration seeder must be run explicitly (`--class=PostMigrationSeeder --force`), following the `AdminSeeder` pattern.
- **Not flushing the `blog.index` cache** after publish/unpublish: The public blog index will serve stale data for up to 1 hour. Add `Cache::forget('blog.index')` in `PostForm::submit()`.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Markdown editor with toolbar | Custom `contenteditable` + toolbar | `easymde` | CodeMirror handles cursor, shortcuts, undo, preview, fullscreen |
| Image resize/conversion | Custom Imagick invocation | medialibrary `->width()->height()` | Handles job queuing, failed conversion retry, URL generation |
| S3 direct upload | Custom presigned URL controller | `WithFileUploads` + `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` | Livewire handles signed URL generation, chunk size, progress events |
| YAML front matter parsing | Custom regex parser | `spatie/yaml-front-matter` (already installed) | Already tested; `BlogRepository::parse()` reuses it |
| Blog URL cache invalidation | Timed-only expiry | `Cache::forget('blog.index')` on status change | 1-hour stale data is unacceptable when Pierre publishes a post |

---

## Common Pitfalls

### Pitfall 1: EasyMDE x-init Re-run After Livewire Re-render

**What goes wrong:** `x-init` no longer re-fires after a Livewire re-render (Alpine 3.14.4+ regression, confirmed in alpinejs/alpine#4453). If the `wire:ignore` wrapper is absent, Livewire's morphing destroys the CodeMirror DOM and the editor goes blank.

**Why it happens:** Livewire's morphing algorithm replaces DOM nodes that changed. Without `wire:ignore`, the editor textarea is replaced on every Livewire update.

**How to avoid:** Wrap the EasyMDE container in `wire:ignore`. The editor DOM is then untouched by Livewire after init. Sync happens via `codemirror.on('change') → $wire.set()`, bypassing Livewire's DOM diff entirely.

**Warning signs:** Editor toolbar/preview disappears after submitting validation errors.

### Pitfall 2: Cache Serialization with DB-Backed BlogRepository

**What goes wrong:** `cacheablePayloadFromDb()` accidentally returns an Eloquent Collection or leaves Carbon objects in the array → `unserialize()` with `allowed_classes=false` turns them into `__PHP_Incomplete_Class` → blog 500 on second request (exact bug from staging, commit 50f9929).

**Why it happens:** `config/cache.php` has `serializable_classes => false`.

**How to avoid:** Call `->all()` on the mapped Collection to get a plain PHP array. Flatten Carbon dates to ISO-8601 strings. Mirror `cacheablePayload()` exactly. Add `it('DB blog cached payload survives serializable_classes=false')` regression test.

**Warning signs:** `/blog` returns 200 on first load, 500 on second request after cache warms.

### Pitfall 3: Livewire Temp Upload on Serverless Ephemeral Filesystem

**What goes wrong:** If `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` is left as `local` (default), Livewire writes temp files to `storage/app/livewire-tmp/` on the PHP server's local filesystem. On Laravel Cloud serverless (ephemeral), the file may disappear between the upload request and the form submit request.

**Why it happens:** Serverless functions can be terminated between requests; ephemeral local storage is not shared across invocations.

**How to avoid:** Set `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3`. The browser uploads directly to Scaleway S3 `livewire-tmp/` — no local filesystem involved. Run `php artisan livewire:configure-s3-upload-cleanup` to set 24h cleanup lifecycle.

**Warning signs:** `FileNotFoundException` or `ValidationException` on cover field when submitting a form after upload.

### Pitfall 4: Scaleway CORS Not Set for Direct Browser PUT

**What goes wrong:** Browser attempts PUT to Scaleway bucket URL → CORS preflight (`OPTIONS`) returns no `Access-Control-Allow-Methods: PUT` → browser blocks the upload → Livewire progress spinner hangs.

**Why it happens:** Scaleway buckets have no default CORS config. AWS S3 CORS also required for Livewire direct S3 uploads.

**How to avoid:** Configure CORS on the `dloazur-media` bucket before testing cover upload. See Pattern 2 above for the CORS JSON. Verify with: `curl -X OPTIONS -H "Origin: https://dloazurpiscines.com" -H "Access-Control-Request-Method: PUT" https://dloazur-media.s3.fr-par.scw.cloud/` → should return `Access-Control-Allow-Methods: PUT`.

### Pitfall 5: Slug Uniqueness Collision on Duplicate `Str::slug`

**What goes wrong:** Two articles with similar titles produce the same `Str::slug`. The `unique` constraint on `slug` throws an `IntegrityConstraintViolationException` on create.

**Why it happens:** `Str::slug('Mon article')` and `Str::slug('Mon Article')` both produce `mon-article`.

**How to avoid:** In `PostForm::submit()`, after generating the slug, check `Post::where('slug', $slug)->whereNot('id', $this->postId)->exists()` and append a numeric suffix if needed (e.g., `mon-article-2`). Or display the generated slug in the form while in `draft` status so Pierre can edit it before publishing.

### Pitfall 6: SitemapController Must Not Expose Drafts

**What goes wrong:** `SitemapController` loops over `app(BlogRepository::class)->all()` — if the DB path returns ALL posts (not just published), draft slugs appear in the sitemap → Googlebot crawls them → gets 404 → bad.

**Why it happens:** `BlogRepository::allFromDb()` might inadvertently omit `scopePublished()`.

**How to avoid:** `scopePublished()` is called in `cacheablePayloadFromDb()`. The `all()` → `allFromDb()` → `cacheablePayloadFromDb()` chain always applies `scopePublished()`. Verify with a test: `it('sitemap does not include draft posts')`.

---

## Code Examples

### Verified: `BlogRepository::cacheablePayload()` pattern to mirror

```php
// Source: app/Support/BlogRepository.php (verified in codebase)
public function cacheablePayload(?string $dir = null): array
{
    // Returns plain array of scalar-only post arrays.
    // Carbon dates flattened to ISO-8601 strings.
    // Never a Collection — serializable_classes=false.
    return $this->loadPosts($dir)
        ->map(function (array $post): array {
            $post['date'] = $post['date']->toIso8601String();
            return $post;
        })
        ->all();
}
```

The DB-backed equivalent must follow the same shape and discipline.

### Verified: Admin route group registration

```php
// Source: bootstrap/app.php (registered there per comment in routes/admin.php)
// Routes registered under: middleware(['web', 'auth'])->prefix('admin')->name('admin.')
// Add to routes/admin.php:
Route::get('blog', [PostController::class, 'index'])->name('blog.index');
Route::get('blog/create', [PostController::class, 'create'])->name('blog.create');
Route::get('blog/{post}', [PostController::class, 'show'])->name('blog.show');
Route::get('blog/{post}/edit', [PostController::class, 'edit'])->name('blog.edit');
```

### Verified: ClientForm submit redirect pattern to mirror

```php
// Source: app/Livewire/ClientForm.php (verified in codebase)
$this->dispatch('client-saved');
$this->redirect(route('admin.clients.index'), navigate: true);
// PostForm equivalent:
$this->dispatch('post-saved');
$this->redirect(route('admin.blog.index'), navigate: true);
```

### Verified: Array shape produced by BlogRepository::parse()

```php
// Source: app/Support/BlogRepository.php — the exact shape the DB path must mirror
[
    'title'        => string,
    'slug'         => string,
    'date'         => Carbon (hydrated) | string (cached),
    'show_date'    => bool,
    'excerpt'      => string,
    'author'       => string,
    'cover'        => string|null,  // URL string or null
    'body'         => string,       // raw markdown
    'reading_time' => int,
    'filepath'     => string|null,  // null in DB path
]
```

`blog/show.blade.php` accesses all keys via `$post['key']` array syntax — the Eloquent path must return arrays, not model instances.

### Verified: Admin sidebar nav item pattern

```php
// Source: resources/views/components/admin/sidebar.blade.php
<a href="{{ route('admin.blog.index') }}"
    @class([
        'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
        'bg-white/10 text-white'            => request()->routeIs('admin.blog.*'),
        'hover:bg-white/8 hover:text-white' => !request()->routeIs('admin.blog.*'),
    ])
    @if(request()->routeIs('admin.blog.*')) aria-current="page" @endif>
    {{-- Pencil/document icon SVG here --}}
    Blog
</a>
```

---

## State of the Art

| Old Approach | Current Approach | Impact |
|--------------|------------------|--------|
| Blog content in flat `.md` files | DB-backed Eloquent `Post` model with config flag rollback | Pierre can publish without git; file backup retained |
| `abort_unless($post, 404)` for all missing slugs | 410 for unpublished-indexed, 404 for never-published | Googlebot de-indexes removed articles faster |
| `BlogRepository::parse()` private | `public parse()` | Reusable in seeder without duplication |
| Tailwind preflight global reset | Scoped `.easymde-wrap` overrides in `@layer components` | Editor toolbar/preview styles survive Tailwind v4 reset |

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | `easymde` v2.21.0 legitimacy (slopcheck unavailable, manual signals used) | Package Legitimacy Audit | Low — well-established package; add `checkpoint:human-verify` before install |
| A2 | Laravel Cloud serverless provides `/tmp` access for Livewire local temp uploads | Pitfall 3 / Pattern 2 | Medium — if `/tmp` unavailable, must use `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` (still D-02 compliant); test during Wave 0 |
| A3 | `medialibrary->nonQueued()` conversion works synchronously on serverless (GD/Imagick available) | Pattern 2 | Medium — Laravel Cloud provisions PHP with GD; if not, conversion falls back to queued (cover URL returns null until job runs) |
| A4 | Scaleway bucket name and CORS endpoint — `dloazur-media` and `s3.fr-par.scw.cloud` — assumed from CLAUDE.md config pattern | Pattern 2 / Pitfall 4 | Low — verify actual bucket name in Laravel Cloud secrets |

---

## Open Questions

1. **Laravel Cloud `/tmp` filesystem access for Livewire temp uploads**
   - What we know: Laravel Cloud is serverless; ephemeral local disk is unreliable between requests
   - What's unclear: Whether a single HTTP request can write to `/tmp` and read it back in the same invocation (likely yes for single-request upload)
   - Recommendation: Default to `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` as D-02 specifies; avoids the uncertainty entirely

2. **`BlogRepository::parse()` visibility**
   - What we know: Currently `private`; seeder needs it
   - What's unclear: Whether making it `public` breaks any expectations
   - Recommendation: Make it `public` in the same migration wave — it's pure parsing logic with no side effects

3. **Existing `BlogTest.php` test compatibility after DB swap**
   - What we know: `BlogTest.php` tests hit `/blog` and `/blog/bienvenue-dlo-azur` expecting data from `.md` files; it uses `app()->instance(BlogRepository::class, $repo)` with fixture dirs
   - What's unclear: Whether the file-backed tests will still pass after the config default changes to `db`
   - Recommendation: In the test environment, keep `BLOG_SOURCE=files` (or set it in `TestCase::setUp()`) so existing tests continue to pass. Add separate DB-backed tests for the new CRUD.

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PostgreSQL 17 | `posts` migration | ✓ | 17 (Neon managed) | — |
| Scaleway S3 `dloazur-media` bucket | Cover upload | ✓ (assumed) | — | local disk for dev |
| GD or Imagick | medialibrary thumbnail conversion | ✓ (assumed Laravel Cloud PHP) | — | conversion fails silently; cover URL null |
| npm / Vite | `npm install easymde` | ✓ | Vite 8.0.14 | — |
| `spatie/yaml-front-matter` | PostMigrationSeeder | ✓ | installed | — |

**Missing dependencies with no fallback:** None identified.

**Missing dependencies with fallback:** Scaleway CORS config (must be set before cover upload works in staging; fallback during dev: use `local` disk for covers).

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Pest PHP 4.7.0 |
| Config file | `phpunit.xml` / `Pest.php` |
| Quick run command | `./vendor/bin/pest --filter blog` |
| Full suite command | `./vendor/bin/pest` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| SITE-07 | Sitemap excludes drafts | Feature | `./vendor/bin/pest --filter "sitemap does not include draft"` | ❌ Wave 0 |
| SITE-07 | Blog show 410 for unpublished-but-indexed slug | Feature | `./vendor/bin/pest --filter "blog show returns 410"` | ❌ Wave 0 |
| SITE-07 | Article JSON-LD preserved on DB-backed post | Feature | Extend `BlogTest.php` | ✅ (extend) |
| SITE-07 | Cached blog payload (DB path) survives serializable_classes=false | Feature | `./vendor/bin/pest --filter "DB blog cached payload"` | ❌ Wave 0 |
| CONTENT-01 | Admin can create a post (Livewire) | Feature | Livewire testing with `Livewire::test(PostForm::class)` | ❌ Wave 0 |
| CONTENT-01 | Admin can publish/unpublish a post | Feature | `Livewire::test(PostForm::class)->set('status','published')` | ❌ Wave 0 |
| CONTENT-01 | Slug locks on publish | Unit | `Post::create([...])` + assert slug immutable | ❌ Wave 0 |
| CONTENT-01 | PostMigrationSeeder is idempotent | Feature | Run twice, assert 3 rows | ❌ Wave 0 |

### Sampling Rate

- **Per task commit:** `./vendor/bin/pest --filter blog`
- **Per wave merge:** `./vendor/bin/pest`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps

- [ ] `tests/Feature/PostAdminTest.php` — covers CONTENT-01 (Livewire CRUD)
- [ ] `tests/Feature/PostMigrationSeederTest.php` — covers idempotency
- [ ] Extend `tests/Feature/BlogTest.php` — add 410 branch, DB-backed cache test, sitemap draft-exclusion
- [ ] Regression: existing `BlogTest.php` must still pass with `BLOG_SOURCE=files` in test env

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | yes | `middleware(['web','auth'])` on all admin routes — already enforced by route group |
| V4 Access Control | yes | Admin-only; `middleware('auth')` gates `/admin/*`; no client-guard overlap |
| V5 Input Validation | yes | `#[Validate]` on all PostForm fields; slug `[a-z0-9-]` constraint on public route |
| V5 File Upload | yes | `image|mimes:jpg,jpeg,png,webp|max:4096` validation before S3 |
| V6 Cryptography | — | Not applicable (no new crypto) |

### Known Threat Patterns

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| Markdown XSS in body | Tampering | `<x-markdown>` uses CommonMark `safe_mode` (`html_input=strip`) — verified in `BlogTest.php` |
| Unauthenticated admin POST | Elevation | `middleware(['web','auth'])` on route group |
| File upload type confusion (non-image) | Tampering | `mimes:jpg,jpeg,png,webp` validation + medialibrary MIME check |
| Draft slug enumeration (410 vs 404 timing) | Information Disclosure | Timing is identical; both are fast `abort()` calls — no leakage |

---

## Project Constraints (from CLAUDE.md)

| Constraint | Impact on This Phase |
|------------|---------------------|
| Livewire 3 for admin write components | PostForm/PostIndex are Livewire — confirmed |
| No Livewire for offline | Not relevant (admin blog is online-only) |
| Tailwind v4 CSS-first, no `tailwind.config.js` | EasyMDE scoping in `@layer components` in `app.css` — no config file touch |
| PostgreSQL 17 | Migration uses `->string(16)` not native `->enum()` — project convention |
| Laravel Cloud EU serverless | No local disk for temp uploads → `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` |
| `serializable_classes=false` | DB-backed `cacheablePayloadFromDb()` must return plain scalar arrays |
| `spatie/laravel-medialibrary ^11.22` | Use v11 API: `registerMediaCollections` + nested `registerMediaConversions` |
| DomPDF/no Browsershot on serverless | Not relevant to blog admin (no PDF generation in this phase) |
| Pest v4 | All new tests use Pest syntax |
| DESIGN.md tokens in admin UI | Admin blog UI must use existing OKLCH tokens; planner should reference `impeccable` skill for any UI design decisions |

---

## Sources

### Primary (HIGH confidence)
- Codebase grep (app/, database/, tests/, resources/) — exact signatures, migration patterns, BlogRepository shape, existing test coverage [VERIFIED]
- `livewire.laravel.com/docs/3.x/wire-ignore` — `wire:ignore` directive semantics [CITED]
- `livewire.laravel.com/docs/3.x/uploads` — `WithFileUploads`, `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK`, `livewire:configure-s3-upload-cleanup` [CITED]
- `spatie.be/docs/laravel-medialibrary/v11/working-with-media-collections/defining-media-collections` — `registerMediaCollections` + nested conversions + `singleFile()` [CITED]
- `spatie.be/docs/laravel-medialibrary/v11/converting-images/defining-conversions` — `->width()->height()->nonQueued()` [CITED]
- `tailwindcss.com/docs/preflight` — v4 preflight disable syntax, `@layer components` approach [CITED]
- `scaleway.com/en/docs/object-storage/api-cli/setting-cors-rules/` — Scaleway CORS JSON structure for S3-compatible API [CITED]
- npm registry: `npm view easymde` — v2.21.0, published 2026-05-03, github.com/Ionaru/easy-markdown-editor, no postinstall [VERIFIED: npm registry]

### Secondary (MEDIUM confidence)
- `spatie.be/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model` — HasMedia + InteractsWithMedia model setup [CITED]
- alpinejs/alpine#4453 — `x-init` regression in Alpine 3.14.4+ re: Livewire re-renders [MEDIUM — GitHub discussion]
- `consoledotlog.co.uk/updating-postgres-enum-laravel-migration` — Postgres enum = varchar+check; project uses `->string()` instead [MEDIUM, cross-verified with codebase]

### Tertiary (LOW confidence)
- None — all critical claims verified against live sources or codebase

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — all libraries verified installed; one new npm package manually verified
- Architecture: HIGH — all patterns extracted directly from live codebase; no inference
- Pitfalls: HIGH — cache pitfall verified against BlogTest.php + commit 50f9929; others from official docs
- EasyMDE integration: MEDIUM — `x-init` Alpine regression cited from GitHub discussion; `wire:ignore` pattern from official docs

**Research date:** 2026-05-30
**Valid until:** 2026-07-30 (stable stack; EasyMDE API unlikely to break)
