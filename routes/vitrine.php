<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vitrine Routes
|--------------------------------------------------------------------------
|
| Public marketing site routes. /contact is wired here by Plan 04.
| Plan 03 will add /services, /realisations, /mentions-legales, /cgv,
| /confidentialite, and the full home page.
| Loaded under the "web" middleware group via bootstrap/app.php.
|
*/

Route::view('/contact', 'vitrine.contact')->name('contact');
