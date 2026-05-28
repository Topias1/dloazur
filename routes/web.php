<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/up', [HealthController::class, 'ping'])->name('health');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
