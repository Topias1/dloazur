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

        return Cache::remember('blog.index', 60 * 60, fn () => $this->loadPosts($dir));
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
