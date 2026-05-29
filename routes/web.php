<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The home route (/) is now owned by routes/vitrine.php (Plan 01-03).
| HomeController + skeleton-home.blade.php remain on disk as historical
| artifacts until Plan 01-06 cutover cleanup; they are no longer reachable.
|
*/

// /up — health check : jamais mis en cache (Plan 06 cache.headers:health)
Route::middleware('cache.headers:health')->get('/up', [HealthController::class, 'ping'])->name('health');

// /offline — page hors-ligne précachée par Workbox (navigateFallback) — accessible sans auth
Route::view('/offline', 'offline')->name('offline');

// robots.txt — served as static file in production (public/robots.txt);
// this route ensures the test suite can assert it, since the Laravel test
// client routes through the kernel, not the web server file system.
Route::get('/robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');

// llms.txt — AI-crawler discoverability file (public marketing URLs only; no auth/admin paths)
Route::get('/llms.txt', function () {
    return response(file_get_contents(public_path('llms.txt')), 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('llms');

/*
|--------------------------------------------------------------------------
| Zyro legacy 301 redirects (D-24 — Plan 01-06)
|--------------------------------------------------------------------------
|
| Captured from https://dloazurpiscines.com/sitemap.xml on 2026-05-28.
| See .planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md for the
| full mapping table and SEO recovery options for the 3 legacy blog articles.
|
*/
Route::redirect('/services-et-nettoyage', '/services', 301);
Route::redirect('/nos-realisations', '/realisations', 301);
Route::redirect('/blog-list-nettoyage-piscine-professionnel', '/blog', 301);
Route::redirect('/page-article-blog-vierge', '/blog', 301);
// /conditions-generales — URL Zyro hors sitemap (footer link only) → Phase 1 /cgv
Route::redirect('/conditions-generales', '/cgv', 301);
// Typo variant (missing trailing 's') → canonical article slug (SEO recovery, D-24)
Route::redirect(
    '/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine',
    '/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines',
    301
);
