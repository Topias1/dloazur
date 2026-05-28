<?php

use App\Http\Controllers\Portail\MagicLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes portail client + magic link (Plan 02-07)
|--------------------------------------------------------------------------
|
| Enregistrées dans bootstrap/app.php sous middleware('web').
| Deux groupes :
|   - guest:clients  → formulaire demande + confirmation (non authentifié)
|   - auth:clients   → espace client (authentifié via guard 'clients')
|
*/

// Routes accessibles uniquement aux clients NON connectés
Route::middleware('guest:clients')->group(function () {
    // GET /auth/magic — formulaire de demande de lien de connexion
    Route::get('/auth/magic', [MagicLinkController::class, 'requestForm'])
        ->name('portail.magic-link.request');

    // POST /auth/magic — création et envoi du magic link (rate limité)
    Route::post('/auth/magic', [MagicLinkController::class, 'send'])
        ->middleware('throttle:magic-link')
        ->name('portail.magic-link.send');

    // GET /auth/confirm?ml={token} — page statique D-50 (SafeLinks M365)
    // AUCUN side-effect ici — juste un formulaire POST
    Route::get('/auth/confirm', [MagicLinkController::class, 'confirmView'])
        ->name('portail.magic-link.confirm-view');

    // POST /auth/confirm — consomme le token et connecte le client
    Route::post('/auth/confirm', [MagicLinkController::class, 'confirm'])
        ->name('portail.magic-link.confirm');
});

// Routes accessibles uniquement aux clients connectés (guard 'clients')
Route::middleware('auth:clients')->prefix('portail')->group(function () {
    // GET /portail/passages — timeline des passages
    Route::get('/passages', fn () => view('portail.passages'))
        ->name('portail.passages');

    // POST /portail/logout — déconnexion
    Route::post('/logout', [MagicLinkController::class, 'logout'])
        ->name('portail.logout');
});
