<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\BlogRepository;
use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;

class BlogController
{
    public function index(BlogRepository $blog): View
    {
        return view('blog.index', [
            'posts'       => $blog->all(),
            'title'       => 'Blog · Dlo Azur Piscines',
            'description' => "Actualités, conseils d'entretien et de dépannage de piscines en Martinique.",
        ]);
    }

    public function show(BlogRepository $blog, string $slug): View
    {
        $post = $blog->find($slug);

        if (! $post) {
            // D-03: 410 Gone for a slug that exists in DB but is not published
            // (was indexed by Googlebot, now unpublished — signals fast de-index).
            // 404 for slugs that never existed at all.
            if (config('blog.source') === 'db' && Post::where('slug', $slug)->exists()) {
                abort(410);
            }
            abort(404);
        }

        // Sibling articles for the "à lire aussi" footer (and internal linking).
        $morePosts = $blog->all()
            ->reject(fn (array $p): bool => $p['slug'] === $slug)
            ->take(2)
            ->values();

        return view('blog.show', [
            'post'          => $post,
            'morePosts'     => $morePosts,
            'title'         => $post['title'] . ' · Dlo Azur Piscines',
            'description'   => $post['excerpt'],
            'type'          => 'article',
            'articleJsonLd' => $this->buildArticleSchema($post),
        ]);
    }

    /**
     * @param  array{title: string, date: \Carbon\Carbon, show_date: bool, excerpt: string, author: string, slug: string, cover: string|null}  $post
     */
    private function buildArticleSchema(array $post): string
    {
        $article = Schema::article()
            ->headline($post['title'])
            ->datePublished($post['date']->toIso8601String())
            ->dateModified($post['date']->toIso8601String())
            ->author(Schema::person()->name($post['author']))
            ->image($post['cover'] ?? asset('assets/brand/og-default.jpg'))
            ->mainEntityOfPage(url('/blog/' . $post['slug']))
            ->publisher(Schema::organization()->name('Dlo Azur Piscines'));

        return $article->toScript();
    }
}
