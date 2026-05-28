<?php

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
| Phase 2 will add Route::resource('clients', ClientController::class), etc.
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
