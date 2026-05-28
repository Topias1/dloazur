<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Sync Google Reviews daily at 04:30 UTC (off-peak Martinique time).
 * withoutOverlapping() prevents double-runs if a previous sync is still running.
 * onOneServer() prevents parallel runs in multi-server deployments (D-28 amended, T-4-14).
 */
Schedule::command('reviews:sync')->dailyAt('04:30')->withoutOverlapping()->onOneServer();
