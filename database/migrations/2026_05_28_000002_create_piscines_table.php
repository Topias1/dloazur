<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piscines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('nom')->nullable();
            $table->decimal('volume_m3', 6, 2)->nullable();
            $table->string('type')->nullable();
            $table->string('filtration')->nullable();
            $table->string('traitement')->nullable();
            $table->json('equipements')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piscines');
    }
};
