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
            $table->boolean('candidature_cloturee')->default(false)->after('nombre_techniciens_acceptes');
            $table->timestamp('date_cloture_candidature')->nullable()->after('candidature_cloturee');
            $table->string('cloture_par', 26)->nullable()->after('date_cloture_candidature');

            $table->foreign('cloture_par')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordres_mission', function (Blueprint $table) {
            $table->dropForeign(['cloture_par']);
            $table->dropColumn(['candidature_cloturee', 'date_cloture_candidature', 'cloture_par']);
        });
    }
};
