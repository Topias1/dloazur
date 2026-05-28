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
