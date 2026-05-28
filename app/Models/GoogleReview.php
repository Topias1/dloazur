<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Passive denormalized cache for Google Places API reviews (D-28 amended).
 * Only SyncGoogleReviewsCommand writes here — readonly from app perspective.
 * No factory needed (populated by sync command, not by dev fixtures).
 */
class GoogleReview extends Model
{
    protected $table = 'google_reviews';

    protected $fillable = [
        'google_review_id',
        'author_name',
        'rating',
        'comment',
        'relative_time_description',
        'language',
        'profile_photo_url',
        'reviewed_at',
        'fetched_at',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'reviewed_at' => 'datetime',
        'fetched_at'  => 'datetime',
    ];
}
