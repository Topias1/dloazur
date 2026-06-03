<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive frequence_jour column on piscines (admin-1, Plan 07-02).
 *
 * Stores the day of the week for the pool visit schedule (e.g. 'lundi', 'mardi', …, 'dimanche').
 * Value matches Carbon::now()->locale('fr')->isoFormat('dddd') output — used directly
 * in AgendaController query: Piscine::where('frequence_jour', $today).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piscines', function (Blueprint $table) {
            $table->string('frequence_jour', 16)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('piscines', function (Blueprint $table) {
            $table->dropColumn(['frequence_jour']);
        });
    }
};
