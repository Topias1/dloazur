<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('type', 24);
            $table->string('libelle')->nullable();
            $table->decimal('prix_ht_mensuel', 10, 2)->nullable();
            $table->smallInteger('jour_facturation')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
