<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passages', function (Blueprint $table) {
            $table->id();
            // D-08: UUID idempotence key for offline sync — NOT a FK
            // (arrives from IndexedDB before client row may be reconciled)
            $table->uuid('client_uuid')->unique();
            $table->foreignId('piscine_id')->nullable()->constrained('piscines')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->dateTime('visited_at')->nullable();
            $table->string('status', 16)->default('draft');
            $table->decimal('ph_avant', 4, 2)->nullable();
            $table->decimal('ph_apres', 4, 2)->nullable();
            $table->decimal('chlore_libre', 5, 2)->nullable();
            $table->decimal('chlore_total', 5, 2)->nullable();
            $table->decimal('tac', 6, 2)->nullable();
            $table->decimal('th', 6, 2)->nullable();
            $table->decimal('sel_g_l', 5, 2)->nullable();
            $table->json('actions')->nullable();
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            // D-08: Phase 3 electronic signature
            $table->string('signature_path')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passages');
    }
};
