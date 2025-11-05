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
            $table->dropForeign(['technicien_id']);
            $table->dropColumn(['technicien_id', 'date_acceptation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordres_mission', function (Blueprint $table) {
            $table->string('technicien_id', 26)->nullable()->after('date_generation');
            $table->timestamp('date_acceptation')->nullable()->after('technicien_id');
            $table->foreign('technicien_id')->references('id')->on('techniciens')->onDelete('restrict');
        });
    }
};
