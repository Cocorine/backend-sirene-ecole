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
        Schema::table('programmations', function (Blueprint $table) {
            $table->json('jours_feries_exceptions')->nullable()->after('jours_feries_inclus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programmations', function (Blueprint $table) {
            $table->dropColumn('jours_feries_exceptions');
        });
    }
};
