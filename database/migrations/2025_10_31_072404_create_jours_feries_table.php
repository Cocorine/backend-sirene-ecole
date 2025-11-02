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
        Schema::create('jours_feries', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('calendrier_id', 26); // ULID - MCD field
            $table->string('ecole_id', 26)->nullable(); // ULID
            $table->string('pays_id', 26)->nullable(); // ULID
            $table->string('libelle'); // MCD field name
            $table->string('nom')->nullable(); // Ex: Jour de l'an, Fête du travail
            $table->date('date_ferie'); // MCD field name
            $table->date('date')->nullable();
            $table->enum('type', ['national', 'personnalise'])->default('national'); // National (pays) ou personnalisé (école)
            $table->boolean('recurrent')->default(false); // Si le jour férié se répète chaque année
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('calendrier_id')->references('id')->on('calendriers_scolaires')->onDelete('restrict');
            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jours_feries');
    }
};
