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
        Schema::create('calendriers_scolaires', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('pays_id', 26); // ULID
            $table->string('annee_scolaire', 9); // MCD field - Ex: 2024-2025
            $table->text('description')->nullable(); // MCD field
            $table->date('date_rentree');
            $table->date('date_fin_annee');
            $table->json('periodes_vacances')->nullable(); // JSON des périodes de vacances
            $table->json('jours_feries_defaut')->nullable(); // Jours fériés par défaut du pays
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendriers_scolaires');
    }
};
