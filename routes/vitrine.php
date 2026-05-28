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
| Plan 06 : les routes statiques sont enveloppées dans cache.headers:vitrine
| (Cache-Control: public, max-age=300). /contact est exclu (composant Livewire
| — stateful, requiert le réseau). /sitemap.xml a son propre profil hourly.
|
*/

// Routes vitrine statiques — cache public 5 min (RESEARCH Pitfall 11)
Route::middleware('cache.headers:vitrine')->group(function () {
    Route::get('/', [VitrineController::class, 'home'])->name('home');
    Route::get('/services', [VitrineController::class, 'services'])->name('services');
    Route::get('/services/eau-verte-urgence', [VitrineController::class, 'eauVerteUrgence'])->name('services.eau-verte-urgence');
    Route::get('/realisations', [VitrineController::class, 'realisations'])->name('realisations');
    Route::get('/mentions-legales', [VitrineController::class, 'mentionsLegales'])->name('legal.mentions');
    Route::get('/cgv', [VitrineController::class, 'cgv'])->name('legal.cgv');
    Route::get('/confidentialite', [VitrineController::class, 'confidentialite'])->name('legal.confidentialite');
});

// /contact — Livewire stateful : pas de cache public
Route::get('/contact', [VitrineController::class, 'contact'])->name('contact');

// /sitemap.xml — cache 1 h (se met à jour lors d'un nouveau post de blog)
Route::middleware('cache.headers:sitemap')->get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
