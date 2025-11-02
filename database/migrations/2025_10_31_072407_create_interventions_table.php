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
        Schema::create('interventions', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('panne_id', 26)->nullable(); // ULID
            $table->string('technicien_id', 26); // ULID
            $table->string('ordre_mission_id', 26); // ULID - MCD field
            $table->timestamp('date_intervention')->nullable()->useCurrent(); // MCD field name
            $table->timestamp('date_affectation')->useCurrent(); // MCD field
            $table->timestamp('date_assignation')->nullable()->useCurrent();
            $table->timestamp('date_acceptation')->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->string('statut', 50)->default('en attente'); // MCD field
            $table->enum('old_statut', ['assignee', 'acceptee', 'en_cours', 'terminee', 'annulee'])->default('assignee');
            $table->integer('note_ecole')->nullable(); // MCD field - review de l'école (1 à 5)
            $table->text('commentaire_ecole')->nullable(); // MCD field
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('panne_id')->references('id')->on('pannes')->onDelete('restrict');
            $table->foreign('technicien_id')->references('id')->on('techniciens')->onDelete('restrict');
            $table->foreign('ordre_mission_id')->references('id')->on('ordres_mission')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
