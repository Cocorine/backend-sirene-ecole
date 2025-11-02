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
        Schema::table('ordres_mission', function (Blueprint $table) {
            $table->foreign('panne_id')->references('id')->on('pannes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordres_mission', function (Blueprint $table) {
            $table->dropForeign(['panne_id']);
        });
    }
};
