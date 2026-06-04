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

// robots.txt — généré dynamiquement selon config('app.indexable').
// Servi par Laravel (plus de fichier statique public/robots.txt) afin que le
// contenu dépende de l'environnement : les sites de test (SITE_INDEXABLE absent
// → false) interdisent toute indexation ET bloquent les crawlers IA ; la prod
// réelle (SITE_INDEXABLE=true) sert la version permissive.
Route::get('/robots.txt', function () {
    $sitemap = rtrim((string) config('app.url'), '/').'/sitemap.xml';

    if (config('app.indexable')) {
        $body = <<<TXT
            User-agent: *
            Allow: /
            Disallow: /admin

            Sitemap: {$sitemap}
            TXT;
    } else {
        // Sites de test : on interdit tout (moteurs + crawlers IA explicites).
        $aiBots = ['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-Web', 'anthropic-ai', 'CCBot', 'Google-Extended', 'PerplexityBot', 'Bytespider', 'Amazonbot', 'Applebot-Extended', 'meta-externalagent', 'cohere-ai'];
        $aiBlocks = collect($aiBots)
            ->map(fn (string $bot) => "User-agent: {$bot}\nDisallow: /")
            ->implode("\n\n");

        $body = <<<TXT
            User-agent: *
            Disallow: /

            {$aiBlocks}

            Sitemap: {$sitemap}
            TXT;
    }

    return response($body."\n", 200, ['Content-Type' => 'text/plain']);
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
