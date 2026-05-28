<?php

use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VitrineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vitrine Routes — Plan 01-03
|--------------------------------------------------------------------------
|
| Public marketing site routes. Loaded under the "web" middleware group
| via bootstrap/app.php.
|
| Note: Plan 06 will wrap these Route::get() calls in
| Route::middleware('cache.headers:vitrine')->group(...) blocks.
| Plan 03 ships the route definitions in their final form; Plan 06 wraps
| surgically without touching the individual route definitions.
|
*/

Route::get('/', [VitrineController::class, 'home'])->name('home');
Route::get('/services', [VitrineController::class, 'services'])->name('services');
Route::get('/services/eau-verte-urgence', [VitrineController::class, 'eauVerteUrgence'])->name('services.eau-verte-urgence');
Route::get('/realisations', [VitrineController::class, 'realisations'])->name('realisations');
Route::get('/contact', [VitrineController::class, 'contact'])->name('contact');
Route::get('/mentions-legales', [VitrineController::class, 'mentionsLegales'])->name('legal.mentions');
Route::get('/cgv', [VitrineController::class, 'cgv'])->name('legal.cgv');
Route::get('/confidentialite', [VitrineController::class, 'confidentialite'])->name('legal.confidentialite');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
