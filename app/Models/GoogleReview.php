<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleReview extends Model
{
    protected $fillable = [
        'google_review_id',
        'author_name',
        'author_url',
        'profile_photo_url',
        'rating',
        'comment',
        'relative_time_description',
        'language',
        'reviewed_at',
        'fetched_at',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'reviewed_at' => 'datetime',
        'fetched_at'  => 'datetime',
    ];
}
