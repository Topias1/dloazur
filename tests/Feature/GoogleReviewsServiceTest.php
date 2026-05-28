<?php

use App\Models\GoogleReview;
use App\Services\GoogleReviewsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config([
        'google-reviews.api_key'  => 'test-api-key',
        'google-reviews.place_id' => 'ChIJtest123',
        'google-reviews.enabled'  => true,
    ]);
});

it('fetchAndUpsert with valid API response upserts rows', function () {
    Http::fake([
        '*/place/details/json*' => Http::response(
            json_decode(file_get_contents(base_path('tests/fixtures/google-places-response.json')), true)
        ),
    ]);

    $service = app(GoogleReviewsService::class);
    $count = $service->fetchAndUpsert();

    expect($count)->toBe(5);
    expect(GoogleReview::count())->toBe(5);

    $top = GoogleReview::where('rating', 5)->first();
    expect($top)->not->toBeNull();
    expect($top->author_name)->not->toBeEmpty();
    expect($top->reviewed_at)->not->toBeNull();
});

it('fetchAndUpsert is idempotent on re-run', function () {
    Http::fake([
        '*/place/details/json*' => Http::response(
            json_decode(file_get_contents(base_path('tests/fixtures/google-places-response.json')), true)
        ),
    ]);

    $service = app(GoogleReviewsService::class);

    $service->fetchAndUpsert();
    $firstFetchedAt = GoogleReview::first()->fetched_at;

    // Advance time so fetched_at changes
    Carbon::setTestNow(now()->addSeconds(5));

    $service->fetchAndUpsert();

    expect(GoogleReview::count())->toBe(5);
    expect(GoogleReview::first()->fetched_at->gt($firstFetchedAt))->toBeTrue();

    Carbon::setTestNow();
});

it('fetchAndUpsert returns 0 and skips HTTP when api_key missing', function () {
    Http::fake();
    config(['google-reviews.api_key' => null, 'google-reviews.enabled' => false]);

    $service = app(GoogleReviewsService::class);
    $count = $service->fetchAndUpsert();

    expect($count)->toBe(0);
    Http::assertNothingSent();
});

it('fetchAndUpsert returns 0 and logs error on Google API 4xx', function () {
    Http::fake([
        '*/place/details/json*' => Http::response(['error_message' => 'Invalid key'], 400),
    ]);
    Log::spy();

    $service = app(GoogleReviewsService::class);
    $count = $service->fetchAndUpsert();

    expect($count)->toBe(0);
    expect(GoogleReview::count())->toBe(0);
    Log::shouldHaveReceived('error')->once();
});

it('SyncGoogleReviewsCommand calls service and reports count', function () {
    Http::fake([
        '*/place/details/json*' => Http::response(
            json_decode(file_get_contents(base_path('tests/fixtures/google-places-response.json')), true)
        ),
    ]);

    $this->artisan('reviews:sync')
        ->expectsOutputToContain('5 reviews synced')
        ->assertExitCode(0);
});

it('latestFiltered returns only ≥4★ ordered by reviewed_at desc, capped at limit', function () {
    // Seed 8 rows: 3 with rating=3, 2 with rating=4, 3 with rating=5
    $base = now()->subDays(10);
    $rows = [];

    for ($i = 1; $i <= 3; $i++) {
        $rows[] = [
            'google_review_id'           => "low-$i",
            'author_name'                => "Low Rater $i",
            'author_url'                 => "https://g.co/low$i",
            'rating'                     => 3,
            'comment'                    => 'Pas terrible.',
            'relative_time_description'  => "il y a $i mois",
            'reviewed_at'                => $base->copy()->subDays($i * 2),
            'fetched_at'                 => now(),
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ];
    }

    for ($i = 1; $i <= 2; $i++) {
        $rows[] = [
            'google_review_id'           => "mid-$i",
            'author_name'                => "Mid Rater $i",
            'author_url'                 => "https://g.co/mid$i",
            'rating'                     => 4,
            'comment'                    => 'Très bien.',
            'relative_time_description'  => "il y a $i semaines",
            'reviewed_at'                => $base->copy()->addDays($i * 3),
            'fetched_at'                 => now(),
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ];
    }

    for ($i = 1; $i <= 3; $i++) {
        $rows[] = [
            'google_review_id'           => "high-$i",
            'author_name'                => "Top Rater $i",
            'author_url'                 => "https://g.co/high$i",
            'rating'                     => 5,
            'comment'                    => 'Excellent !',
            'relative_time_description'  => "il y a $i jours",
            'reviewed_at'                => $base->copy()->addDays(10 + $i),
            'fetched_at'                 => now(),
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ];
    }

    \Illuminate\Support\Facades\DB::table('google_reviews')->insert($rows);

    $service = app(GoogleReviewsService::class);
    $results = $service->latestFiltered(5, 4);

    expect($results->count())->toBe(5);
    expect($results->first()->reviewed_at->gte($results->last()->reviewed_at))->toBeTrue();
    expect($results->where('rating', '<', 4)->count())->toBe(0);
});
