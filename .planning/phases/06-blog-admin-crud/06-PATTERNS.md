# Phase 6: blog-admin-crud - Pattern Map

**Mapped:** 2026-05-30
**Files analyzed:** 17 (new/modified)
**Analogs found:** 15 / 17

---

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|
| `app/Models/Post.php` | model | CRUD | `app/Models/Client.php` | role-match (Client lacks HasMedia; see medialibrary note) |
| `database/migrations/*_create_posts_table.php` | migration | CRUD | `database/migrations/2026_05_28_000005_create_passages_table.php` | exact (string(16) status, compound index) |
| `database/seeders/PostMigrationSeeder.php` | seeder | batch | `database/seeders/AdminSeeder.php` | role-match (same updateOrCreate, production-safe pattern) |
| `app/Http/Controllers/Admin/PostController.php` | controller | request-response | `app/Http/Controllers/Admin/ClientController.php` | exact |
| `app/Livewire/PostIndex.php` | Livewire component | CRUD | `app/Livewire/ClientIndex.php` | exact |
| `app/Livewire/PostForm.php` | Livewire component | CRUD | `app/Livewire/ClientForm.php` | exact + WithFileUploads extension |
| `resources/views/admin/blog/index.blade.php` | Blade view | request-response | `resources/views/admin/clients/index.blade.php` | exact |
| `resources/views/admin/blog/create.blade.php` | Blade view | request-response | `resources/views/admin/clients/create.blade.php` | exact |
| `resources/views/admin/blog/edit.blade.php` | Blade view | request-response | `resources/views/admin/clients/edit.blade.php` | exact |
| `resources/views/livewire/post-index.blade.php` | Blade view | CRUD | `resources/views/livewire/client-index.blade.php` | exact |
| `resources/views/livewire/post-form.blade.php` | Blade view | CRUD | `resources/views/livewire/client-form.blade.php` | role-match (new: EasyMDE + cover dropzone + status toggle) |
| `resources/js/post-editor.js` | JS module | event-driven | `resources/js/passage-form.js` (Alpine.data factory pattern) | partial-match (same Alpine.data registration shape) |
| `config/blog.php` | config | — | none in project | no analog |
| `app/Support/BlogRepository.php` (modify) | service | CRUD/transform | itself (internal refactor) | self |
| `app/Http/Controllers/BlogController.php` (modify) | controller | request-response | itself (add 410 branch) | self |
| `app/Http/Controllers/SitemapController.php` (modify) | controller | batch | itself (scopePublished gate) | self |
| `resources/views/components/admin/sidebar.blade.php` (modify) | Blade component | — | itself (add Blog nav item) | self |

---

## Pattern Assignments

### `app/Models/Post.php` (model, CRUD)

**Analog:** `app/Models/Client.php` (lines 1–49)

**Imports/namespace pattern** (`app/Models/Client.php` lines 1–10):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Add for Post:
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
```

**Fillable/casts pattern** (`app/Models/Client.php` lines 14–28):
```php
protected $fillable = [
    'title', 'slug', 'body', 'excerpt', 'status',
    'author', 'date', 'show_date',
];

protected $casts = [
    'date'      => 'date',
    'show_date' => 'boolean',
];
```

**scopePublished** — project has no prior scope; add on Post:
```php
public function scopePublished(Builder $query): Builder
{
    return $query->where('status', 'published');
}
```

**Slug auto-generation booted hook** (no analog — new pattern):
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

**medialibrary registration** — first use in project (no analog in `app/Models/`); follow RESEARCH.md Pattern 2:
```php
class Post extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
             ->singleFile()
             ->useDisk('s3')
             ->registerMediaConversions(function (Media $media) {
                 $this->addMediaConversion('thumbnail')
                      ->width(1200)
                      ->height(630)
                      ->nonQueued();
             });
    }
}
```

**Note on disk name:** `config/filesystems.php` defines `'s3'` (Scaleway-pointed) and `'r2'` (Cloudflare). Passage photos use `'r2'` via raw `Storage::disk('r2')`. The medialibrary `cover` collection for `Post` uses `->useDisk('s3')` per D-02. Confirm the target bucket env var (`AWS_BUCKET` / Scaleway) is set in Laravel Cloud secrets. This is the FIRST use of `HasMedia`/`InteractsWithMedia` in a project model — no existing analog to copy from.

---

### `database/migrations/*_create_posts_table.php` (migration, CRUD)

**Analog:** `database/migrations/2026_05_28_000005_create_passages_table.php` (lines 1–42)

**Migration class structure** (passages migration lines 1–10):
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
```

**Status column convention** — CONFIRMED `->string(16)`, NOT `->enum()` (passages migration line 19):
```php
$table->string('status', 16)->default('draft');  // 'draft' | 'published'
```

**Compound index pattern** (passages migration line 34):
```php
$table->index(['status', 'date']); // mirrors: $table->index(['client_id', 'visited_at'])
```

**Full Post schema** (based on confirmed project conventions):
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();      // unique() implies index
    $table->text('body');                  // raw markdown
    $table->text('excerpt')->nullable();
    $table->string('status', 16)->default('draft');   // string not enum (project convention)
    $table->string('author')->default('Pierre ADAM');
    $table->date('date')->nullable();      // datePublished for SEO JSON-LD
    $table->boolean('show_date')->default(true);
    $table->timestamps();

    $table->index(['status', 'date']);     // scopePublished + orderByDesc('date')
});
```

---

### `database/seeders/PostMigrationSeeder.php` (seeder, batch)

**Analog:** `database/seeders/AdminSeeder.php` (lines 1–46, full file)

**Class structure and docblock pattern** (AdminSeeder lines 1–32):
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * PostMigrationSeeder — Idempotent migration of 3 .md files to posts table.
 *
 * Per D-05/D-06: NOT env-gated (unlike DatabaseSeeder).
 * Safe to re-run: updateOrCreate keyed on slug yields 1 row per slug.
 *
 * Run via: php artisan db:seed --class=PostMigrationSeeder --force
 */
class PostMigrationSeeder extends Seeder
{
    public function run(): void
    {
```

**updateOrCreate idempotent upsert pattern** (AdminSeeder lines 31–38):
```php
// AdminSeeder pattern:
$user = User::updateOrCreate(
    ['email' => env('OPERATOR_EMAIL', ...)],
    ['name' => ..., 'password' => ...]
);

// PostMigrationSeeder equivalent:
Post::updateOrCreate(
    ['slug' => $slug],
    ['title' => ..., 'body' => ..., 'status' => 'published', ...]
);
```

**DatabaseSeeder production-gate pattern** (DatabaseSeeder lines 13–23) — PostMigrationSeeder avoids the gate entirely by being standalone, same as AdminSeeder. Do NOT register in DatabaseSeeder. Run explicitly:
```bash
php artisan db:seed --class=PostMigrationSeeder --force
```

**Parsing pattern** — `BlogRepository::parse()` is currently `private` (line 108 of `BlogRepository.php`). Must be made `public` before the seeder can call it. Alternatively use `YamlFrontMatter::parseFile()` directly (same 10-line logic is visible in `BlogRepository::parse()` lines 110–139).

---

### `app/Http/Controllers/Admin/PostController.php` (controller, request-response)

**Analog:** `app/Http/Controllers/Admin/ClientController.php` (lines 1–29, full file — exact copy)

**Full pattern** (ClientController lines 1–29):
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;   // swap Client → Post

class PostController extends Controller
{
    public function index()
    {
        return view('admin.blog.index');   // 'admin.clients.index' → 'admin.blog.index'
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function show(Post $post)
    {
        return view('admin.blog.show', compact('post'));
    }

    public function edit(Post $post)
    {
        return view('admin.blog.edit', compact('post'));
    }
}
```

No changes in structure. The model binding `Post $post` uses `slug` if Post defines `getRouteKeyName()` — or uses `id` (default). Decide in planner: the admin routes can use `{post}` (id-bound) since they're internal; the public `blog.show` route uses slug already.

---

### `app/Livewire/PostIndex.php` (Livewire component, CRUD)

**Analog:** `app/Livewire/ClientIndex.php` (lines 1–42, full file)

**Imports/traits** (ClientIndex lines 1–12):
```php
<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PostIndex extends Component
{
    use WithPagination;

    public string $search = '';
```

**updatedSearch reset** (ClientIndex lines 17–20):
```php
public function updatedSearch(): void
{
    $this->resetPage();
}
```

**Render query** (ClientIndex lines 22–38) — adapted for Post:
```php
public function render(): View
{
    $driver = DB::connection()->getDriverName();
    $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

    $posts = Post::query()
        ->when($this->search, fn ($q) =>
            $q->where('title', $likeOp, '%' . $this->search . '%')
        )
        ->orderByDesc('date')   // NOT 'updated_at' — blog is date-ordered
        ->paginate(25);         // admin sees ALL statuses (drafts included)

    return view('livewire.post-index', compact('posts'));
}
```

Note: unlike `ClientIndex`, PostIndex does NOT filter by status — admin sees ALL posts (drafts + published). `scopePublished()` is only applied in the public `BlogRepository` path.

---

### `app/Livewire/PostForm.php` (Livewire component, CRUD)

**Analog:** `app/Livewire/ClientForm.php` (lines 1–83, full file) + `WithFileUploads` extension

**Class structure** (ClientForm lines 1–13):
```php
<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;   // NEW vs ClientForm

class PostForm extends Component
{
    use WithFileUploads;   // NEW vs ClientForm
```

**Validate attribute pattern** (ClientForm lines 15–28):
```php
#[Validate('required|string|max:80')]
public string $name = '';

// PostForm equivalent:
#[Validate('required|string|max:160')]
public string $title = '';

#[Validate('nullable|string')]    // auto-generated, editable while draft
public string $slug = '';

#[Validate('required|string')]
public string $body = '';

#[Validate('nullable|string|max:300')]
public string $excerpt = '';

#[Validate('nullable|string|max:16')]
public string $status = 'draft';

#[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:4096')]
public $cover = null;   // TemporaryUploadedFile|null
```

**mount pattern** (ClientForm lines 31–42):
```php
public ?int $clientId = null;

public function mount(?int $clientId = null): void
{
    if ($clientId) {
        $client = Client::findOrFail($clientId);
        $this->clientId = $client->id;
        $this->name     = $client->name;
        // ...
    }
}

// PostForm equivalent:
public ?int $postId = null;

public function mount(?int $postId = null): void
{
    if ($postId) {
        $post = Post::findOrFail($postId);
        $this->postId   = $post->id;
        $this->title    = $post->title;
        $this->slug     = $post->slug;
        $this->body     = $post->body;
        $this->excerpt  = (string) $post->excerpt;
        $this->status   = $post->status;
        // do NOT hydrate $this->cover — it's a TemporaryUploadedFile, not a URL
    }
}
```

**submit + redirect pattern** (ClientForm lines 44–77):
```php
public function submit(): void
{
    $this->validate();

    try {
        if ($this->clientId) {
            Client::findOrFail($this->clientId)->update([...]);
        } else {
            Client::create([...]);
        }
    } catch (\Throwable $e) {
        Log::error('Client save failed', ['exception' => $e->getMessage()]);
        $this->addError('save', "L'enregistrement a échoué.");
        return;
    }

    $this->dispatch('client-saved');
    $this->redirect(route('admin.clients.index'), navigate: true);
}

// PostForm additions beyond ClientForm:
// 1. Slug locking: if status='published', skip slug update
// 2. Cover upload: $post->addMedia($this->cover)->toMediaCollection('cover', 's3')
// 3. Cache flush: Cache::forget('blog.index') after status change
// 4. dispatch('post-saved') + redirect to route('admin.blog.index')
```

**render** (ClientForm line 79–82):
```php
public function render(): View
{
    return view('livewire.post-form');
}
```

---

### `resources/views/admin/blog/index.blade.php` (Blade view, request-response)

**Analog:** `resources/views/admin/clients/index.blade.php` (lines 1–16, full file)

**Exact copy pattern**:
```blade
@extends('layouts.admin')

@section('title', 'Blog · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <livewire:post-index />         {{-- was: client-index --}}
    <x-admin.mobile-bottom-nav />
@endsection
```

---

### `resources/views/admin/blog/create.blade.php` (Blade view, request-response)

**Analog:** `resources/views/admin/clients/create.blade.php` (lines 1–36, full file)

**Key differences from analog** (clients/create.blade.php lines 14–33):
```blade
<div class="px-5 sm:px-8 py-7 max-w-2xl">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-7">
        <a href="{{ route('admin.blog.index') }}"   {{-- was: admin.clients.index --}}
            class="h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200 flex items-center justify-center ...">
            <svg ...><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="font-display font-semibold text-2xl text-ink-950">Nouvel article</h1>
    </div>

    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
        <livewire:post-form />      {{-- was: client-form --}}
    </div>

</div>
```

---

### `resources/views/admin/blog/edit.blade.php` (Blade view, request-response)

**Analog:** `resources/views/admin/clients/edit.blade.php` (lines 1–44, full file)

**Key differences** (clients/edit.blade.php lines 14–44):
```blade
<div class="px-5 sm:px-8 py-7 max-w-2xl space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.blog.index') }}"   {{-- was: admin.clients.show --}}
            class="h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200 ...">
            <svg ...><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="font-display font-semibold text-xl text-ink-950">{{ $post->title }}</h1>
    </div>

    <div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6">
        <h2 class="font-display font-semibold text-base text-ink-900 mb-5">Article</h2>
        <livewire:post-form :postId="$post->id" />   {{-- was: :clientId="$client->id" --}}
    </div>
    {{-- NOTE: No second card (no Piscine equivalent) --}}
</div>
```

---

### `resources/views/livewire/post-index.blade.php` (Blade view, CRUD)

**Analog:** `resources/views/livewire/client-index.blade.php` (lines 1–95, full file)

**Header section** (client-index lines 2–13) — change label and route:
```blade
<div class="px-5 sm:px-8 py-7 space-y-7">
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">Blog</h1>
        <a href="{{ route('admin.blog.create') }}"
            class="h-11 px-5 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600 transition-colors inline-flex items-center gap-2">
            <svg ...><path d="M12 5v14M5 12h14"/></svg>
            Nouvel article
        </a>
    </div>
```

**Search input** (client-index lines 15–28) — change placeholder + aria-label:
```blade
    <input wire:model.live.debounce.300ms="search"
           placeholder="Rechercher un article…"
           aria-label="Rechercher un article"
           ...same classes...>
```

**List item pattern** (client-index lines 32–66) — key structural differences:
- Replace `<span>` initials-avatar with `<img>` cover thumbnail OR fallback `<x-icon.drop>` azur block
- Replace piscine chip with status badge `<x-admin.status-badge :status="$post->status">`
- Link to `route('admin.blog.edit', $post)` (not show)
- Display `$post->title` + date `d/m/Y` meta

**Empty state** (client-index lines 68–84) — change copy per UI-SPEC copywriting table.

**Pagination** (client-index lines 88–93) — identical.

---

### `resources/views/livewire/post-form.blade.php` (Blade view, CRUD)

**Analog:** `resources/views/livewire/client-form.blade.php` (lines 1–98, full file)

**Form wrapper + field style** (client-form lines 1–16):
```blade
<form wire:submit.prevent="submit" class="space-y-5">

    {{-- Field pattern: identical for Title, Excerpt --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">
            Titre <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input wire:model="title" type="text" maxlength="160"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none">
        @error('title')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
    </div>
```

**EasyMDE container** (new, no analog in client-form):
```blade
    {{-- Body / Markdown editor — wire:ignore mandatory --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Contenu *</label>
        <div wire:ignore class="easymde-wrap"
             x-data="postEditor(@js($body))"
             x-init="init($refs.ta)">
            <textarea x-ref="ta"></textarea>
        </div>
        <input type="hidden" wire:model="body">
        @error('body')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
    </div>
```

**Cover dropzone** (new, no analog in client-form):
```blade
    {{-- Cover image --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Image de couverture</label>
        @if ($cover)
            <img src="{{ $cover->temporaryUrl() }}" class="rounded-xl ring-1 ring-navy-900/8 w-full aspect-[1200/630] object-cover">
        @else
            <label for="cover-input"
                class="rounded-xl border-2 border-dashed border-sand-200 bg-sand-50 p-6 text-center hover:border-azure-300 transition-colors cursor-pointer block">
                Glissez une image ou cliquez pour choisir
            </label>
            <input id="cover-input" type="file" wire:model="cover" class="sr-only">
        @endif
        <div wire:loading wire:target="cover" class="text-sm text-ink-500">Envoi…</div>
        @error('cover')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
    </div>
```

**Submit button** (client-form lines 88–96 — identical):
```blade
    <button type="submit"
        class="h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors"
        wire:loading.attr="disabled"
        wire:loading.class="opacity-60">
        <span wire:loading.remove>Enregistrer</span>
        <span wire:loading>Enregistrement…</span>
    </button>
```

**Save error block** (client-form lines 81–83):
```blade
    @error('save')
        <p class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3 text-sm text-danger">{{ $message }}</p>
    @enderror
```

---

### `resources/js/post-editor.js` (JS module, event-driven)

**Analog:** `resources/js/passage-form.js` — Alpine.data factory shape registered in `app.js`

**app.js registration pattern** (app.js lines 4–7, 47–48):
```js
// Existing pattern:
import { passageForm } from './passage-form.js';
Alpine.data('passageForm', passageForm);

// PostEditor equivalent:
import { postEditor } from './post-editor.js';
Alpine.data('postEditor', postEditor);
```

**app.js EasyMDE import additions** (at top of app.js, before Alpine import):
```js
import 'easymde/dist/easymde.min.css';
import EasyMDE from 'easymde';
window.EasyMDE = EasyMDE;   // expose to x-init scope
```

**post-editor.js factory shape** (new — no exact analog; follows RESEARCH.md Pattern 1):
```js
export function postEditor(initialBody) {
    return {
        editor: null,
        init(textarea) {
            this.editor = new EasyMDE({
                element: textarea,
                spellChecker: false,
                autosave: { enabled: false },
                toolbar: ['bold','italic','heading','|','unordered-list','ordered-list','link','|','preview','side-by-side','fullscreen'],
            });
            if (initialBody) { this.editor.value(initialBody); }
            this.editor.codemirror.on('change', () => {
                this.$wire.set('body', this.editor.value(), false);
            });
        },
    };
}
```

---

### `config/blog.php` (config)

**No analog in project.** Nearest structural reference is `config/filesystems.php` (env-keyed config pattern):

```php
// config/filesystems.php pattern:
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    // ...
],

// config/blog.php:
return [
    'source' => env('BLOG_SOURCE', 'db'),   // 'db' | 'files'
];
```

---

### `app/Support/BlogRepository.php` (modify — files→DB swap)

**Self-referential** — modify the existing file. Critical constraints from live code:

**`all()` method** (BlogRepository lines 20–38) — add DB branch before the file path:
```php
public function all(): Collection
{
    // D-06: config flag routes to DB or file path
    if (config('blog.source') === 'db') {
        return $this->allFromDb();
    }
    // ... existing file path unchanged from line 22 onwards
}
```

**`cacheablePayload()` signature** (BlogRepository lines 62–73) — the DB equivalent MUST mirror this return shape exactly:
```php
// EXISTING (lines 62–73) — return type and discipline to preserve:
public function cacheablePayload(?string $dir = null): array
{
    return $this->loadPosts($dir)
        ->map(function (array $post): array {
            $post['date'] = $post['date']->toIso8601String(); // Carbon → string
            return $post;
        })
        ->all(); // plain PHP array, never Collection
}
```

**Array shape contract** (BlogRepository line 107–139 docblock + return) — DB path must return identical keys:
```php
// Required keys (from BlogRepository::parse() return, lines 129–140):
[
    'title', 'slug', 'date', 'show_date', 'excerpt',
    'author', 'cover', 'body', 'reading_time', 'filepath' // filepath=null for DB path
]
```

**Cache key** (`'blog.index'`, line 36) — `PostForm::submit()` must call `Cache::forget('blog.index')` on every publish/unpublish.

---

### `app/Http/Controllers/BlogController.php` (modify — 410 branch)

**Self-referential** — add 410 branch after the current `abort_unless` (line 24):

**Current code** (BlogController lines 22–24):
```php
$post = $blog->find($slug);

abort_unless($post, 404);
```

**Replace with** (RESEARCH.md Pattern 7):
```php
$post = $blog->find($slug); // scopePublished() applied in DB path

if (! $post) {
    if (config('blog.source') === 'db' && Post::where('slug', $slug)->exists()) {
        abort(410); // unpublished-but-indexed → HTTP 410 Gone (fast Googlebot de-index)
    }
    abort(404);
}
```

**`buildArticleSchema()`** (BlogController lines 46–57) — unchanged; already uses `$post['cover'] ?? asset('assets/brand/og-default.jpg')` fallback which handles null cover.

---

### `app/Http/Controllers/SitemapController.php` (modify — scopePublished gate)

**Self-referential** — the blog hook loop (SitemapController lines 38–47) already uses `BlogRepository::all()`. The `scopePublished()` filter is applied inside `BlogRepository::allFromDb()` — so the SitemapController needs NO code change if the BlogRepository contract is honored. The only guard needed is confirming `BlogRepository::all()` never returns draft posts on the DB path.

**Current loop** (SitemapController lines 40–46) — keep as-is:
```php
foreach (app(\App\Support\BlogRepository::class)->all() as $post) {
    $sitemap->add(
        Url::create(route('blog.show', ['slug' => $post['slug']]))
            ->setLastModificationDate($post['date'])
            ->setPriority(0.5)
    );
}
```

---

### `resources/views/components/admin/sidebar.blade.php` (modify — Blog nav item)

**Self-referential** — insert between the Passages item (line 58–80) and the greyed Factures item (line 83). Copy the exact `@class` pattern from the Clients item (lines 43–56):

**Clients item pattern** (sidebar.blade.php lines 43–56):
```blade
<a href="{{ route('admin.clients.index') }}"
    @class([
        'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
        'bg-white/10 text-white'             => request()->routeIs('admin.clients.*'),
        'hover:bg-white/8 hover:text-white'  => !request()->routeIs('admin.clients.*'),
    ])
    @if(request()->routeIs('admin.clients.*')) aria-current="page" @endif>
    <svg ...> ... </svg>
    Clients
</a>
```

**Blog item to insert** (replace route + routeIs pattern + icon SVG per UI-SPEC):
```blade
<a href="{{ route('admin.blog.index') }}"
    @class([
        'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
        'bg-white/10 text-white'            => request()->routeIs('admin.blog.*'),
        'hover:bg-white/8 hover:text-white' => !request()->routeIs('admin.blog.*'),
    ])
    @if(request()->routeIs('admin.blog.*')) aria-current="page" @endif>
    {{-- feather file-text icon (UI-SPEC Surface 1 §Sidebar) --}}
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <path d="M14 2v6h6"/>
        <path d="M16 13H8M16 17H8M10 9H8"/>
    </svg>
    Blog
</a>
```

---

### `routes/admin.php` (modify — blog routes)

**Self-referential** — append after the Clients CRUD block (lines 24–27). Copy exact route shape:

**Clients routes pattern** (admin.php lines 24–27):
```php
Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
```

**Blog routes to add**:
```php
// Blog CRUD (Phase 6)
// Write actions handled by Livewire PostForm/PostIndex components.
Route::get('blog', [PostController::class, 'index'])->name('blog.index');
Route::get('blog/create', [PostController::class, 'create'])->name('blog.create');
Route::get('blog/{post}', [PostController::class, 'show'])->name('blog.show');
Route::get('blog/{post}/edit', [PostController::class, 'edit'])->name('blog.edit');
```

Add `use App\Http\Controllers\Admin\PostController;` at the top of the file.

---

### `resources/css/app.css` (modify — `.easymde-wrap` scoping)

**Self-referential** — append after the closing `}` of `@theme` block (line 105) or after `:root` block (line 113). Use `@layer components` per Tailwind v4 convention (app.css already uses `@layer base` at line 115):

**Existing @layer base pattern** (app.css lines 115–119):
```css
@layer base {
    * { -webkit-tap-highlight-color: transparent; }
    html { scroll-behavior: smooth; }
    body { ... }
```

**Add after existing layers**:
```css
@layer components {
    /* Scope EasyMDE stylesheet under .easymde-wrap to prevent Tailwind v4 preflight
       from resetting CodeMirror toolbar icons, preview padding, and button borders.
       EasyMDE CSS imported in app.js — this block only overrides conflicting resets. */
    .easymde-wrap .CodeMirror {
        border-radius: var(--radius-xl);
        font-family: var(--font-sans);
        font-size: 0.875rem;
        border: 1px solid var(--color-sand-200);
    }
    .easymde-wrap .editor-toolbar button {
        border: 1px solid var(--color-navy-200);
        background: var(--color-sand-100);
        color: var(--color-ink-700);
    }
    .easymde-wrap .editor-toolbar button:hover,
    .easymde-wrap .editor-toolbar button.active {
        background: var(--color-azure-100);
        color: var(--color-azure-600);
    }
    .easymde-wrap .editor-preview {
        background: var(--color-sand-50);
        padding: 1rem;
    }
    .easymde-wrap .EasyMDEContainer .CodeMirror {
        min-height: 320px;
    }
}
```

---

## Shared Patterns

### Admin Route Group Registration
**Source:** `bootstrap/app.php` (referenced in `routes/admin.php` comment, line 12)
**Apply to:** `routes/admin.php` new blog routes, `PostController`
```
middleware(['web', 'auth'])->prefix('admin')->name('admin.')
```
All new blog routes are automatically under this group — no per-route middleware needed.

### Error Handling (Livewire form)
**Source:** `app/Livewire/ClientForm.php` lines 69–73
**Apply to:** `PostForm::submit()`
```php
} catch (\Throwable $e) {
    Log::error('Client save failed', ['exception' => $e->getMessage()]);
    $this->addError('save', "L'enregistrement a échoué.");
    return;
}
```
Display with: `@error('save') <p class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3 text-sm text-danger">{{ $message }}</p> @enderror`

### Redirect After Save
**Source:** `app/Livewire/ClientForm.php` lines 75–76
**Apply to:** `PostForm::submit()`
```php
$this->dispatch('post-saved');
$this->redirect(route('admin.blog.index'), navigate: true);
```

### Status Column Convention
**Source:** `database/migrations/2026_05_28_000005_create_passages_table.php` line 19
**Apply to:** `posts` migration
```php
$table->string('status', 16)->default('draft');   // NOT ->enum() — project convention
```

### Cache Scalar Array Discipline
**Source:** `app/Support/BlogRepository.php` lines 62–73 (cacheablePayload + comment block lines 50–60)
**Apply to:** `BlogRepository::cacheablePayloadFromDb()` in the DB swap
- Call `->all()` on any mapped Collection before returning
- Flatten Carbon dates to `->toIso8601String()` strings
- Return `array<int, array<string, scalar|null>>`, never `Collection` or model instances

### Blade View Shell
**Source:** `resources/views/admin/clients/index.blade.php` lines 1–16
**Apply to:** All three `resources/views/admin/blog/*.blade.php` files
```blade
@extends('layouts.admin')
@section('sidebar') <x-admin.sidebar :user="auth()->user()" /> @endsection
@section('topbar') <x-admin.topbar /> @endsection
@section('main') ... <x-admin.mobile-bottom-nav /> @endsection
```

### Tailwind Design Tokens
**Source:** `resources/css/app.css` lines 19–105
**Apply to:** All new Blade/CSS — key tokens:
- Cards: `rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs`
- Inputs: `h-12 w-full rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none px-4`
- Primary button: `h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600`
- Labels: `text-sm font-semibold text-ink-900 mb-1.5`
- Errors: `mt-1 text-sm text-danger`

---

## No Analog Found

| File | Role | Data Flow | Reason |
|---|---|---|---|
| `config/blog.php` | config | — | First config-flag file in project; no prior env-routing config |
| `app/Models/Post.php` (HasMedia trait) | model | — | First `HasMedia`/`InteractsWithMedia` model in `app/Models/`; `media` migration exists but no model uses it yet |

---

## Key Observations for Planner

1. **`string(16)` status confirmed** — `passages` migration line 19 uses `->string('status', 16)->default('draft')`. This is the project convention. Post migration must match.

2. **medialibrary `media` table exists** — `2026_05_28_132229_create_media_table.php` is already migrated. `Post` will be the first model to use `HasMedia`. No existing model to copy the trait usage from — follow RESEARCH.md Pattern 2 verbatim.

3. **PassagePhotoController uses raw `Storage::disk('r2')`** (not medialibrary) — this is the §Pitfall 6 divergence noted in D-02. Post cover intentionally diverges to medialibrary. Both can coexist; the `r2` and `s3` disks are separate configs.

4. **`filesystems.php` has `s3` (Scaleway) and `r2` (Cloudflare R2)** — Post cover uses `->useDisk('s3')`. Confirm `AWS_BUCKET` env var points to the Scaleway bucket in Laravel Cloud secrets (the comment says "Scaleway Object Storage" but the disk is named `s3`).

5. **`BlogRepository::parse()` is `private`** (line 108) — must be changed to `public` before `PostMigrationSeeder` can call it. This is a prerequisite task in Wave 0.

6. **Alpine.data factory pattern** for `postEditor` follows the `passageForm` shape in `app.js` lines 4/47 — import named export, register with `Alpine.data()`.

7. **`DatabaseSeeder` is env-gated to local/testing only** (lines 13–23) — `PostMigrationSeeder` must be standalone like `AdminSeeder`, never registered in `DatabaseSeeder`.

---

## Metadata

**Analog search scope:** `app/`, `database/`, `resources/views/`, `resources/js/`, `resources/css/`, `routes/`, `config/`
**Files scanned:** 24
**Pattern extraction date:** 2026-05-30
