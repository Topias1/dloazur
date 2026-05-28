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

// robots.txt — served as static file in production (public/robots.txt);
// this route ensures the test suite can assert it, since the Laravel test
// client routes through the kernel, not the web server file system.
Route::get('/robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');
