<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Blog Routes
|--------------------------------------------------------------------------
|
| Blog index + individual post routes. Filled by Plan 04.
|
| Plan 06 : routes enveloppées dans cache.headers:vitrine (public, max-age=300)
| conformément à RESEARCH Pitfall 11 — trafic solo-opérateur, scale-to-zero.
|
*/

Route::middleware('cache.headers:vitrine')->group(function () {
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show')->where('slug', '[a-z0-9-]+');
});
