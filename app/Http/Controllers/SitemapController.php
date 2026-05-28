<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Generates /sitemap.xml dynamically.
 *
 * Phase 1 emits vitrine routes only (~7 URLs, sub-ms render).
 * Plan 04 hook: BlogRepository binding (if present) appends blog entries.
 * Plan 06 may add Cache-Control headers — see Plan 06 Task 1.
 */
final class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('home'))->setPriority(1.0)->setChangeFrequency('weekly'))
            ->add(Url::create(route('services'))->setPriority(0.8))
            ->add(Url::create(route('realisations'))->setPriority(0.8))
            ->add(Url::create(route('contact'))->setPriority(0.7))
            ->add(Url::create(route('legal.mentions'))->setPriority(0.3))
            ->add(Url::create(route('legal.cgv'))->setPriority(0.3))
            ->add(Url::create(route('legal.confidentialite'))->setPriority(0.3));

        // Plan 04 hook: append blog URLs if BlogRepository is registered in the container.
        if (app()->bound(\App\Support\BlogRepository::class)) {
            $sitemap->add(Url::create(route('blog.index'))->setPriority(0.6));
            foreach (app(\App\Support\BlogRepository::class)->all() as $post) {
                $sitemap->add(
                    Url::create(route('blog.show', ['slug' => $post['slug']]))
                        ->setLastModificationDate($post['date'])
                        ->setPriority(0.5)
                );
            }
        }

        return response($sitemap->render(), 200, ['Content-Type' => 'application/xml']);
    }
}
