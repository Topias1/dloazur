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
| NOTE: Plan 06 will wrap these routes in a cache.headers middleware group
| to add Cache-Control: public, max-age=300, must-revalidate + Vary headers.
| Plan 06 surgically wraps the Route::get() calls below without changing them.
|
*/

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show')->where('slug', '[a-z0-9-]+');
