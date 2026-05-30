<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive lead-capture columns on diagnostics (D-03, Plan 05-03).
 * No separate Lead model — these columns extend the existing Diagnostic row.
 *
 * Columns : prenom (req), commune (req), email (opt), site_web (opt).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnostics', function (Blueprint $table) {
            $table->string('prenom', 80)->nullable()->after('created_via');
            $table->string('commune', 80)->nullable()->after('prenom');
            $table->string('email', 160)->nullable()->after('commune');
            $table->string('site_web', 255)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('diagnostics', function (Blueprint $table) {
            $table->dropColumn(['prenom', 'commune', 'email', 'site_web']);
        });
    }
};
