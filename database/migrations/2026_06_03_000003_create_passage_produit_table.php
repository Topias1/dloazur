<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passage_produit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passage_id')->constrained('passages')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->decimal('quantite', 8, 2)->nullable();
            $table->decimal('prix_snapshot', 10, 2)->nullable(); // prix HT au moment du passage (franchise 293 B — aucun calcul TVA)
            $table->timestamps();
            $table->unique(['passage_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passage_produit');
    }
};
