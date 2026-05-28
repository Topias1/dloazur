<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passage_id')->constrained('passages')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->text('signature_data')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['passage_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
