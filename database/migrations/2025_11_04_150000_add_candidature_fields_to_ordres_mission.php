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
            $table->timestamp('date_debut_candidature')->nullable()->after('date_generation');
            $table->timestamp('date_fin_candidature')->nullable()->after('date_debut_candidature');
            $table->integer('nombre_techniciens_requis')->default(1)->after('date_fin_candidature');
            $table->integer('nombre_techniciens_acceptes')->default(0)->after('nombre_techniciens_requis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordres_mission', function (Blueprint $table) {
            $table->dropColumn([
                'date_debut_candidature',
                'date_fin_candidature',
                'nombre_techniciens_requis',
                'nombre_techniciens_acceptes'
            ]);
        });
    }
};
