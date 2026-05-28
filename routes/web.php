<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/up', [HealthController::class, 'ping'])->name('health');
