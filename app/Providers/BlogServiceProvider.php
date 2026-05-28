<?php

namespace App\Providers;

use App\Support\BlogRepository;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * Registers BlogRepository as a singleton so that:
     * - Plan 03 SitemapController can detect it via app()->bound(BlogRepository::class)
     * - Blog routes resolve the same cached instance across a request
     */
    public function register(): void
    {
        $this->app->singleton(BlogRepository::class, fn () => new BlogRepository());
    }
}
