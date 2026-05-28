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

        return view('blog.show', [
            'post'          => $post,
            'title'         => $post['title'] . ' · Dlo Azur Piscines',
            'description'   => $post['excerpt'],
            'articleJsonLd' => $this->buildArticleSchema($post),
        ]);
    }

    /**
     * @param  array{title: string, date: \Carbon\Carbon, excerpt: string, author: string, slug: string}  $post
     */
    private function buildArticleSchema(array $post): string
    {
        return Schema::article()
            ->headline($post['title'])
            ->datePublished($post['date']->toIso8601String())
            ->author(Schema::person()->name($post['author']))
            ->toScript();
    }
}
