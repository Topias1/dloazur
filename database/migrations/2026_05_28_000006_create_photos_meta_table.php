<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passage_id')->constrained('passages')->cascadeOnDelete();
            $table->string('disk')->default('r2');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
            $table->index('passage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos_meta');
    }
};
