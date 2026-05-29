<?php

namespace App\Http\Controllers;

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

        abort_unless($post, 404);

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
            'articleJsonLd' => $this->buildArticleSchema($post),
        ]);
    }

    /**
     * @param  array{title: string, date: \Carbon\Carbon, show_date: bool, excerpt: string, author: string, slug: string}  $post
     */
    private function buildArticleSchema(array $post): string
    {
        $article = Schema::article()
            ->headline($post['title'])
            ->author(Schema::person()->name($post['author']));

        // Only assert a publish date when it is reliable. Legacy imports carry an
        // unknown date (show_date: false); emitting a placeholder would be a false
        // structured-data signal.
        if ($post['show_date']) {
            $article->datePublished($post['date']->toIso8601String());
        }

        return $article->toScript();
    }
}
