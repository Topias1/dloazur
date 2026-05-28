<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * D-28 amended: passive denormalized cache for Google Places API reviews.
     * No FK to other domain tables — only SyncGoogleReviewsCommand writes here.
     */
    public function up(): void
    {
        Schema::create('google_reviews', function (Blueprint $table) {
            $table->id();
            // Stable identifier from Google Places (author_url or time fingerprint)
            $table->string('google_review_id')->unique();
            $table->string('author_name');
            // 1-5 stars
            $table->unsignedTinyInteger('rating');
            // review.text — may be empty for star-only reviews
            $table->text('comment')->nullable();
            // Google's "il y a 2 mois" — passed through verbatim (locale=fr_FR in API call)
            $table->string('relative_time_description');
            $table->string('language', 8);
            $table->string('profile_photo_url')->nullable();
            // Derived from review.time (Unix epoch from Google)
            $table->timestamp('reviewed_at');
            // When SyncGoogleReviewsCommand last refreshed this row
            $table->timestamp('fetched_at');
            $table->timestamps();
            // Server-side filter for ≥4★ on home page
            $table->index('rating');
            // Server-side sort for "5 most recent"
            $table->index('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_reviews');
    }
};
