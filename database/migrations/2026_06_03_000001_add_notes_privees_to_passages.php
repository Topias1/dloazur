<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive column notes_privees on passages (07-01, admin-2).
 * Bug fix: la colonne était absente de la migration, de $fillable, et de l'upsert —
 * ce qui causait une perte silencieuse des notes internes à la synchro offline.
 *
 * INVARIANT VIE PRIVÉE : cette colonne ne doit JAMAIS être exposée au portail client.
 * Vérifier PassageTimeline.php : notes_privees est absent de la query et de la vue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passages', function (Blueprint $table) {
            $table->text('notes_privees')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('passages', function (Blueprint $table) {
            $table->dropColumn(['notes_privees']);
        });
    }
};
