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
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cache.headers' => \App\Http\Middleware\CacheHeaders::class,
        ]);

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
    })->create();
