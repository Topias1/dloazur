<?php

use App\Livewire\GoogleReviews;
use App\Models\GoogleReview;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config([
        'google-reviews.api_key'      => 'test-api-key',
        'google-reviews.place_id'     => 'ChIJtest123',
        'google-reviews.min_rating'   => 4,
        'google-reviews.home_limit'   => 5,
        'google-reviews.business_url' => 'https://g.page/dlo-azur-piscines',
        'google-reviews.enabled'      => true,
    ]);
});

it('component renders empty view when config disabled', function () {
    config(['google-reviews.enabled' => false]);

    Livewire::test(GoogleReviews::class)
        ->assertSet('hidden', true)
        ->assertDontSee('★')
        ->assertDontSee('avis Google');
});

it('component renders empty view when google_reviews table is empty', function () {
    GoogleReview::query()->delete();

    Livewire::test(GoogleReviews::class)
        ->assertSet('hidden', true)
        ->assertDontSee('avis Google');
});

it('component renders 5 most recent ≥4★ when table has data and config enabled', function () {
    // Seed 7 rows: 2 with rating=3 (filtered out), 3 with rating=4, 2 with rating=5
    $rows = [];
    for ($i = 1; $i <= 2; $i++) {
        $rows[] = [
            'google_review_id'          => "low-$i",
            'author_name'               => "Low Rater $i",
            'author_url'                => "https://g.co/low$i",
            'rating'                    => 3,
            'comment'                   => 'Pas top.',
            'relative_time_description' => 'il y a 1 an',
            'reviewed_at'               => now()->subMonths(12 + $i),
            'fetched_at'                => now(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }
    for ($i = 1; $i <= 3; $i++) {
        $rows[] = [
            'google_review_id'          => "mid-$i",
            'author_name'               => "Bon Client $i",
            'author_url'                => "https://g.co/mid$i",
            'rating'                    => 4,
            'comment'                   => 'Très bien.',
            'relative_time_description' => "il y a $i mois",
            'reviewed_at'               => now()->subMonths($i),
            'fetched_at'                => now(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }
    for ($i = 1; $i <= 2; $i++) {
        $rows[] = [
            'google_review_id'          => "top-$i",
            'author_name'               => "Top Client $i",
            'author_url'                => "https://g.co/top$i",
            'rating'                    => 5,
            'comment'                   => 'Excellent service !',
            'relative_time_description' => "il y a $i semaines",
            'reviewed_at'               => now()->subWeeks($i),
            'fetched_at'                => now(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }
    \Illuminate\Support\Facades\DB::table('google_reviews')->insert($rows);

    $component = Livewire::test(GoogleReviews::class);
    $component
        ->assertSet('hidden', false)
        ->assertSee('avis Google')
        ->assertViewHas('reviews', fn ($r) => $r->count() <= 5 && $r->count() > 0);

    // Verify the most recent ≥4★ author is rendered
    $topAuthor = GoogleReview::where('rating', '>=', 4)->orderByDesc('reviewed_at')->first()->author_name;
    $component->assertSeeText($topAuthor);
});

it('component displays average rating with French decimal comma', function () {
    // 3 reviews averaging 4.667 → rounds to 4.7 with 1 decimal
    $rows = [];
    foreach ([5, 5, 4] as $i => $rating) {
        $rows[] = [
            'google_review_id'          => "avg-$i",
            'author_name'               => "Reviewer $i",
            'author_url'                => "https://g.co/avg$i",
            'rating'                    => $rating,
            'comment'                   => 'Test review.',
            'relative_time_description' => 'il y a 1 mois',
            'reviewed_at'               => now()->subDays($i + 1),
            'fetched_at'                => now(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }
    \Illuminate\Support\Facades\DB::table('google_reviews')->insert($rows);

    // Avg = (5+5+4)/3 = 4.666... formatted to 1 decimal with comma = '4,7'
    Livewire::test(GoogleReviews::class)
        ->assertSeeText('4,7');
});

it('component links to Google Business profile', function () {
    $rows = [[
        'google_review_id'          => 'link-test',
        'author_name'               => 'Reviewer',
        'author_url'                => 'https://g.co/r1',
        'rating'                    => 5,
        'comment'                   => 'Great.',
        'relative_time_description' => 'il y a 1 jour',
        'reviewed_at'               => now()->subDay(),
        'fetched_at'                => now(),
        'created_at'                => now(),
        'updated_at'                => now(),
    ]];
    \Illuminate\Support\Facades\DB::table('google_reviews')->insert($rows);

    Livewire::test(GoogleReviews::class)
        ->assertSee('https://g.page/dlo-azur-piscines')
        ->assertSee('rel="noopener"', false);
});
