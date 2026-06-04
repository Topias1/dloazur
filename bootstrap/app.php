<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        // Plan 01-01 overrides Laravel 13's default /up health endpoint via a
        // controller in routes/web.php so we can return {app, db} JSON.
        then: function () {
            Route::middleware('web')->group(base_path('routes/vitrine.php'));
            Route::middleware('web')->group(base_path('routes/blog.php'));
            Route::middleware(['web', 'auth'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/portail.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cache.headers' => \App\Http\Middleware\CacheHeaders::class,
        ]);

        // Exempte /api/* du CSRF. Compensé par le middleware auth (session cookie).
        // Le PWA est same-domain donc le cookie est envoyé automatiquement (T-2-03, Pitfall 5).
        $middleware->validateCsrfTokens(except: ['api/*']);

        // SearchIndexing pose X-Robots-Tag: noindex,nofollow,noai,noimageai tant que
        // config('app.indexable') est false (sites de test). Fail-closed : actif par défaut.
        $middleware->append(\App\Http\Middleware\SearchIndexing::class);

        // ServiceWorkerHeaders ajoute Service-Worker-Allowed: / sur /build/sw.js (D-60, Pitfall 2).
        // Sans ce header le SW est scopé à /build/ et n'intercepte rien sur /admin/*, /portail/*.
        $middleware->append(\App\Http\Middleware\ServiceWorkerHeaders::class);

        // CacheHeaders est ajouté EN FIN de la pile globale (après les middlewares
        // Livewire) afin de surcharger l'en-tête no-cache posé par
        // Livewire\DisableBackButtonCacheMiddleware sur les pages avec composants.
        // Il utilise l'attribut _cache_profile posé par le route middleware
        // cache.headers:vitrine|sitemap|health ; si l'attribut est absent,
        // le middleware est no-op (routes admin, login, etc.).
        $middleware->append(\App\Http\Middleware\CacheHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Redirige le guard 'clients' vers /auth/magic au lieu de /login (D-53)
        // Sans ça, auth:clients renvoie vers /login (guard web) — fuite inter-guard
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            Request $request
        ) {
            if (in_array('clients', $e->guards(), true) && ! $request->expectsJson()) {
                return redirect()->route('portail.magic-link.request');
            }
        });
    })->create();
