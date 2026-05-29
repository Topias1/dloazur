<?php

namespace App\Support;

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
     */
    public function all(): Collection
    {
        $dir = $this->dir ?? resource_path('content/blog');

        // Skip cache in testing env so fixture swaps propagate immediately.
        if (app()->environment('testing')) {
            return $this->loadPosts($dir);
        }

        // Store only plain scalars in the cache (no Carbon objects) to survive
        // PHP unserialize() with serializable_classes = false (database cache store).
        // hydrateDates() re-wraps the ISO-8601 string back into Carbon on read.
        return $this->hydrateDates(
            Cache::remember('blog.index', 60 * 60, fn () => $this->serializablePosts($dir))
        );
    }

    /**
     * Find a post by slug. Returns null if not found.
     *
     * @return array{title: string, slug: string, date: Carbon, excerpt: string, author: string, body: string, filepath: string}|null
     */
    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /**
     * Load posts and replace Carbon dates with ISO-8601 strings for cache storage.
     * This avoids __PHP_Incomplete_Class when the database cache store deserializes
     * the payload with serializable_classes = false.
     */
    private function serializablePosts(string $dir): Collection
    {
        return $this->loadPosts($dir)->map(function (array $post): array {
            $post['date'] = $post['date']->toIso8601String();

            return $post;
        });
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
     * @return array{title: string, slug: string, date: Carbon, excerpt: string, author: string, body: string, filepath: string}
     */
    private function parse(string $path): array
    {
        $document = YamlFrontMatter::parseFile($path);

        $basename = basename($path, '.md');

        // Slug: from front matter or fall back to filename
        $slug = $document->matter('slug') ?: $basename;

        // Date: from front matter or Carbon::now() as fallback
        $rawDate = $document->matter('date');
        $date = $rawDate ? Carbon::parse($rawDate) : Carbon::now();

        return [
            'title'    => (string) $document->matter('title', ''),
            'slug'     => (string) $slug,
            'date'     => $date,
            'excerpt'  => (string) $document->matter('excerpt', ''),
            'author'   => (string) ($document->matter('author') ?: 'Pierre ADAM'),
            'body'     => (string) $document->body(),
            'filepath' => $path,
        ];
    }
}
