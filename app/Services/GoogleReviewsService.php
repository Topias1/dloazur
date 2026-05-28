<?php

namespace App\Services;

use App\Models\GoogleReview;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleReviewsService
{
    /**
     * Fetch the Google Places Details API and upsert reviews into the local cache.
     *
     * Returns the number of reviews processed (0 on failure or when disabled).
     * Never throws — logs all errors to stderr for Laravel Cloud visibility (D-28 amended).
     */
    public function fetchAndUpsert(): int
    {
        if (! config('google-reviews.enabled')) {
            return 0;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => config('google-reviews.place_id'),
                'fields'   => 'reviews,rating,user_ratings_total',
                'language' => 'fr',
                'key'      => config('google-reviews.api_key'),
            ]);

            if ($response->failed()) {
                Log::error('Google Places API failed', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
                return 0;
            }

            $reviews = $response->json('result.reviews', []);

            if (empty($reviews)) {
                Log::warning('Google Places API returned no reviews', [
                    'status' => $response->json('status'),
                ]);
                return 0;
            }

            $count = 0;
            $fetchedAt = now();

            foreach ($reviews as $review) {
                $fingerprint = ($review['author_url'] ?? '') . '#' . ($review['time'] ?? '');

                GoogleReview::updateOrCreate(
                    ['google_review_id' => $fingerprint],
                    [
                        'author_name'               => $review['author_name'] ?? '',
                        'author_url'                => $review['author_url'] ?? '',
                        'profile_photo_url'         => $review['profile_photo_url'] ?? null,
                        'rating'                    => (int) ($review['rating'] ?? 0),
                        'comment'                   => $review['text'] ?? null,
                        'relative_time_description' => $review['relative_time_description'] ?? null,
                        'language'                  => $review['language'] ?? 'fr',
                        'reviewed_at'               => Carbon::createFromTimestamp($review['time']),
                        'fetched_at'                => $fetchedAt,
                    ]
                );

                $count++;
            }

            return $count;
        } catch (\Throwable $e) {
            Log::error('Google Reviews sync failed unexpectedly', [
                'exception' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Return the most recent reviews with rating >= $minRating, capped at $limit.
     * Used by the GoogleReviews Livewire component — queries local DB only (no API call).
     */
    public function latestFiltered(int $limit = 5, int $minRating = 4): Collection
    {
        return GoogleReview::query()
            ->where('rating', '>=', $minRating)
            ->orderByDesc('reviewed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Compute the average rating across all stored reviews.
     * Returns null if the table is empty.
     */
    public function averageRating(): ?float
    {
        $avg = GoogleReview::avg('rating');
        return $avg !== null ? (float) $avg : null;
    }

    /**
     * Total number of reviews stored in the local cache.
     */
    public function totalCount(): int
    {
        return (int) GoogleReview::count();
    }
}
