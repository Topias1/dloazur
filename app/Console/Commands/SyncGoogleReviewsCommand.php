<?php

namespace App\Console\Commands;

use App\Services\GoogleReviewsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGoogleReviewsCommand extends Command
{
    protected $signature = 'reviews:sync';

    protected $description = 'Synchronize Google Places reviews into local cache (D-28 amended)';

    public function handle(GoogleReviewsService $svc): int
    {
        $start = microtime(true);

        $count = $svc->fetchAndUpsert();

        $duration = round((microtime(true) - $start) * 1000);

        if ($count > 0) {
            $this->info("{$count} reviews synced in {$duration}ms.");
            Log::info('Google reviews synced', ['count' => $count, 'duration_ms' => $duration]);
        } else {
            $this->warn('0 reviews synced — check GOOGLE_PLACES_API_KEY / GOOGLE_PLACE_ID or see Laravel Cloud logs.');
        }

        return self::SUCCESS;
    }
}
