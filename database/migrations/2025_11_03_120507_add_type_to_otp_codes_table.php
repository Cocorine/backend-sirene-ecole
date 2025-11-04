<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->string('type', 50)->default('login')->after('code')
                ->comment('Type d\'OTP: login, password_reset, verification, two_factor');

            // Ajouter un index pour améliorer les performances de recherche
            $table->index(['telephone', 'type', 'verifie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropIndex(['telephone', 'type', 'verifie']);
            $table->dropColumn('type');
        });
    }
};
