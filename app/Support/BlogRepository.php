<?php

namespace App\Support;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class BlogRepository
{
    public function __construct(
        private readonly ?string $dir = null,
    ) {}

    /**
     * Return all blog posts sorted by date DESC.
     *
     * D-06: Routes to DB path (allFromDb) or file path based on config('blog.source').
     * Default is 'db' (Phase 6). Set BLOG_SOURCE=files to rollback to flat Markdown.
     */
    public function all(): Collection
    {
        // D-06: DB path — returns only published posts from the DB.
        if (config('blog.source') === 'db') {
            return $this->allFromDb();
        }

        // ---- File path (unchanged) ----
        $dir = $this->dir ?? resource_path('content/blog');

        // Skip cache in testing env so fixture swaps propagate immediately.
        if (app()->environment('testing')) {
            return $this->loadPosts($dir);
        }

        // Cache a PLAIN ARRAY of scalar-only post arrays — never a Collection or
        // Carbon object. config/cache.php has serializable_classes => false, which
        // passes allowed_classes=false to unserialize() and turns EVERY object
        // (including Illuminate\Support\Collection itself) into __PHP_Incomplete_Class
        // on read. collect() + hydrateDates() rebuild the Collection and Carbon
        // dates after the cache read. (#blog-cache-incomplete-class)
        return $this->hydrateDates(collect(
            Cache::remember('blog.index', 60 * 60, fn () => $this->cacheablePayload($dir))
        ));
    }

    /**
     * DB read path — returns published posts ordered by date DESC.
     *
     * Mirrors the cache discipline of the file path: in testing env skip the cache
     * so DB fixture changes propagate immediately; in production use Cache::remember
     * with a plain scalar array payload (no objects, no Carbon).
     */
    private function allFromDb(): Collection
    {
        // Skip cache in testing env (same discipline as the file path).
        if (app()->environment('testing')) {
            return $this->loadFromDb();
        }

        return $this->hydrateDates(collect(
            Cache::remember('blog.index', 60 * 60, fn () => $this->cacheablePayloadFromDb())
        ));
    }

    /**
     * Find a post by slug. Returns null if not found.
     *
     * @return array{title: string, slug: string, date: Carbon, show_date: bool, excerpt: string, author: string, cover: string|null, body: string, reading_time: int, filepath: string}|null
     */
    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /**
     * Build the value stored in the cache: a PLAIN ARRAY of scalar-only post
     * arrays (Carbon dates flattened to ISO-8601 strings). Crucially this is an
     * array, not a Collection — with serializable_classes => false, a cached
     * Collection object would itself deserialize to __PHP_Incomplete_Class.
     * Arrays and scalars are always allowed by unserialize(allowed_classes=false).
     *
     * Public so the cache round-trip can be regression-tested directly (the
     * normal `all()` path skips the cache under the testing environment).
     *
     * @return array<int, array<string, scalar|null>>
     */
    public function cacheablePayload(?string $dir = null): array
    {
        $dir ??= $this->dir ?? resource_path('content/blog');

        return $this->loadPosts($dir)
            ->map(function (array $post): array {
                $post['date'] = $post['date']->toIso8601String();

                return $post;
            })
            ->all();
    }

    /**
     * Build the DB-backed cacheable payload — a PLAIN ARRAY of scalar-only post arrays.
     *
     * Mirrors the shape and cache discipline of cacheablePayload() exactly:
     * - Carbon dates flattened to ISO-8601 strings (never Carbon in cache).
     * - ->all() at the end returns a plain PHP array, never a Collection.
     * - filepath is null (no file on disk for DB-backed posts).
     *
     * Public so the cache round-trip can be regression-tested directly
     * (config/cache.php serializable_classes=false — see #blog-cache-incomplete-class).
     *
     * @return array<int, array<string, scalar|null>>
     */
    public function cacheablePayloadFromDb(): array
    {
        return Post::published()
            ->orderByDesc('date')
            ->get()
            ->map(fn (Post $p): array => [
                'title'        => (string) $p->title,
                'slug'         => (string) $p->slug,
                'date'         => $p->date?->toIso8601String() ?? now()->toIso8601String(),
                'show_date'    => (bool) $p->show_date,
                'excerpt'      => (string) $p->excerpt,
                'author'       => (string) $p->author,
                'cover'        => $p->getFirstMediaUrl('cover', 'thumbnail') ?: null,
                'body'         => (string) $p->body,
                'reading_time' => max(1, (int) round(str_word_count(strip_tags((string) $p->body)) / 200)),
                'filepath'     => null, // no file on the DB path
            ])
            ->all(); // plain PHP array — never Collection (serializable_classes=false)
    }

    /**
     * Load DB posts for the testing environment — returns a hydrated Collection
     * (date as Carbon) mirroring loadPosts() for the file path.
     */
    private function loadFromDb(): Collection
    {
        return Post::published()
            ->orderByDesc('date')
            ->get()
            ->map(fn (Post $p): array => [
                'title'        => (string) $p->title,
                'slug'         => (string) $p->slug,
                'date'         => $p->date ?? now(), // Carbon instance (hydrated)
                'show_date'    => (bool) $p->show_date,
                'excerpt'      => (string) $p->excerpt,
                'author'       => (string) $p->author,
                'cover'        => $p->getFirstMediaUrl('cover', 'thumbnail') ?: null,
                'body'         => (string) $p->body,
                'reading_time' => max(1, (int) round(str_word_count(strip_tags((string) $p->body)) / 200)),
                'filepath'     => null,
            ]);
    }

    /**
     * Re-hydrate ISO-8601 date strings back into Carbon instances after a cache read.
     */
    private function hydrateDates(Collection $posts): Collection
    {
        return $posts->map(function (array $post): array {
            if (is_string($post['date'])) {
                $post['date'] = Carbon::parse($post['date']);
            }

            return $post;
        });
    }

    private function loadPosts(string $dir): Collection
    {
        if (! is_dir($dir)) {
            return collect();
        }

        return collect(File::files($dir))
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->map(fn ($file) => $this->parse($file->getPathname()))
            ->filter()
            ->sortByDesc(fn ($post) => $post['date']->timestamp)
            ->values();
    }

    /**
     * Parse a markdown file with YAML front matter.
     *
     * @return array{title: string, slug: string, date: Carbon, show_date: bool, excerpt: string, author: string, cover: string|null, body: string, reading_time: int, filepath: string}
     */
    public function parse(string $path): array
    {
        $document = YamlFrontMatter::parseFile($path);

        $basename = basename($path, '.md');

        // Slug: from front matter or fall back to filename
        $slug = $document->matter('slug') ?: $basename;

        // Date: from front matter or Carbon::now() as fallback. The date always
        // drives ordering; `show_date` controls whether it is *displayed*. Legacy
        // articles imported from the old site have an unknown publish date, so they
        // carry `show_date: false` and surface reading time instead of a wrong date.
        $rawDate = $document->matter('date');
        $date = $rawDate ? Carbon::parse($rawDate) : Carbon::now();

        // Cover image: optional front-matter path (e.g. /assets/blog/<slug>.jpg)
        $cover = $document->matter('cover');

        $body = (string) $document->body();

        return [
            'title'        => (string) $document->matter('title', ''),
            'slug'         => (string) $slug,
            'date'         => $date,
            'show_date'    => filter_var($document->matter('show_date', true), FILTER_VALIDATE_BOOLEAN),
            'excerpt'      => (string) $document->matter('excerpt', ''),
            'author'       => (string) ($document->matter('author') ?: 'Pierre ADAM'),
            'cover'        => $cover ? (string) $cover : null,
            'body'         => $body,
            'reading_time' => max(1, (int) round(str_word_count(strip_tags($body)) / 200)),
            'filepath'     => $path,
        ];
    }
}
