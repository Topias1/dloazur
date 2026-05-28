<?php

namespace App\Http\Controllers;

use App\Support\BlogRepository;
use Illuminate\Http\Response;

/**
 * Generates sitemap.xml dynamically.
 *
 * Plan 04 provides the BlogRepository singleton (via BlogServiceProvider).
 * Plan 03 will extend this controller to include additional vitrine pages.
 *
 * The blog URLs are included here because BlogServiceProvider registers the
 * singleton — app()->bound(BlogRepository::class) returns true after Plan 04
 * ships, triggering the blog section in this controller.
 */
class SitemapController
{
    public function index(BlogRepository $blog): Response
    {
        $posts = $blog->all();

        $urls = [
            [
                'loc'        => url('/'),
                'changefreq' => 'weekly',
                'priority'   => '1.0',
            ],
            [
                'loc'        => url('/blog'),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ],
            [
                'loc'        => url('/contact'),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ],
        ];

        foreach ($posts as $post) {
            $urls[] = [
                'loc'        => url('/blog/' . $post['slug']),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
                'lastmod'    => $post['date']->toAtomString(),
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
