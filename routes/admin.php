<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PassageCreateController;
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
Route::view('passages', 'admin.passages.index')->name('passages.index');
// Passages — saisie offline-first (Plan 02-05, PASS-01..03, PASS-06)
// Vue rendue par Blade, logique côté client via Alpine + IndexedDB (pas Livewire — CF-02).
Route::get('passages/create', [PassageCreateController::class, 'create'])->name('passages.create');
