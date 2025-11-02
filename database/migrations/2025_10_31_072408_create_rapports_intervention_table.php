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
        Schema::create('rapports_intervention', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('intervention_id', 26); // ULID
            $table->text('rapport'); // MCD field name
            $table->timestamp('date_soumission')->nullable(); // MCD field
            $table->enum('statut', ['brouillon', 'valide'])->default('brouillon'); // MCD field
            $table->json('photo_url')->nullable(); // MCD field name
            $table->integer('review_note')->nullable(); // MCD field
            $table->text('review_admin')->nullable(); // MCD field
            $table->text('diagnostic')->nullable(); // Diagnostic du problème
            $table->text('travaux_effectues')->nullable(); // Travaux réalisés
            $table->text('pieces_utilisees')->nullable(); // Pièces remplacées/utilisées
            $table->enum('resultat', ['resolu', 'partiellement_resolu', 'non_resolu'])->default('resolu');
            $table->text('recommandations')->nullable();
            $table->json('photos')->nullable(); // URLs des photos
            $table->timestamp('date_rapport')->nullable()->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('intervention_id')->references('id')->on('interventions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports_intervention');
    }
};
