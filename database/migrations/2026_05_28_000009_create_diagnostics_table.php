<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostics', function (Blueprint $table) {
            $table->id();
            // Nullable FK — anonymous visitor diagnostics allowed
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('piscine_id')->nullable()->constrained('piscines')->nullOnDelete();
            $table->decimal('volume_m3', 6, 2)->nullable();
            $table->string('type_probleme', 32)->nullable();
            $table->json('mesures')->nullable();
            $table->json('recommandations')->nullable();
            // DIAG-03: disclaimer acceptance timestamp
            $table->timestamp('disclaimer_accepted_at')->nullable();
            $table->string('created_via', 16)->default('wizard');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostics');
    }
};
