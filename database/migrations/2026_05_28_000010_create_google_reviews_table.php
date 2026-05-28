<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('google_review_id')->unique()->comment('Fingerprint: author_url#time — used for upsert idempotency (D-28 amended)');
            $table->string('author_name');
            $table->string('author_url');
            $table->string('profile_photo_url')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('relative_time_description')->nullable();
            $table->string('language', 10)->default('fr');
            $table->timestamp('reviewed_at');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index(['rating', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_reviews');
    }
};
