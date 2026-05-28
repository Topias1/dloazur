<?php

use App\Http\Controllers\Api\PassageController;
use App\Http\Controllers\Api\PassagePhotoController;
use Illuminate\Support\Facades\Route;

/*
 | Routes API offline-sync.
 | Enregistrées dans bootstrap/app.php sous middleware(['web','auth'])->prefix('api')->name('api.')
 | CSRF exempté pour /api/* dans bootstrap/app.php (Pitfall 5, validateCsrfTokens).
 */

Route::post('passages', [PassageController::class, 'store'])->name('passages.store');

Route::post('passages/{uuid}/photos', [PassagePhotoController::class, 'store'])
     ->whereUuid('uuid')
     ->name('passages.photos.store');
