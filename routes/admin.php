<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Back-office routes. Registered in bootstrap/app.php under:
|   middleware(['web', 'auth'])->prefix('admin')->name('admin.')
|
| GET /admin → admin.dashboard (DashboardController@index)
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Clients CRUD (Plan 02-02)
// Write actions (store/update/destroy) handled by Livewire components.
Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');

// Passages — historique (PASS-05, Plan 02-03)
// Write actions (store/create) handled by Plan 02-05 (saisie offline Alpine).
Route::view('passages', 'admin.passages.index')->name('passages.index');
