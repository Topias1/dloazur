<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // Pitfall 5: numero is separate from id, nullable until posted, then CGI séquentiel
            $table->string('numero')->nullable()->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('contrat_id')->nullable()->constrained('contrats')->nullOnDelete();
            $table->foreignId('passage_id')->nullable()->constrained('passages')->nullOnDelete();
            $table->json('lignes')->nullable();
            $table->decimal('total_ht', 10, 2)->default(0);
            $table->decimal('tva', 10, 2)->default(0);
            $table->decimal('total_ttc', 10, 2)->default(0);
            // Pitfall 4: TVA Martinique pre-set to 8.50%
            $table->decimal('tva_rate', 4, 2)->default(8.50);
            $table->string('statut', 16)->default('brouillon');
            // D-08: Phase 3 Odoo bridge
            $table->unsignedBigInteger('odoo_id')->nullable();
            $table->timestamp('odoo_synced_at')->nullable();
            $table->text('odoo_sync_error')->nullable();
            $table->date('date_echeance')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
