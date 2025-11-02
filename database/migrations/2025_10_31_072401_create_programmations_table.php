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
        Schema::create('programmations', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('ecole_id', 26); // ULID
            $table->string('site_id', 26)->nullable(); // ULID - MCD field
            $table->string('sirene_id', 26); // ULID
            $table->string('abonnement_id', 26)->nullable(); // ULID - MCD field
            $table->string('calendrier_id', 26)->nullable(); // ULID - MCD field
            $table->string('nom_programmation')->nullable(); // Ex: "Programmation Trimestre 1"
            $table->json('horaire_json')->nullable(); // MCD field - ex: [{heure:"08:00", jour:"lundi"}, ...]
            $table->json('horaires_sonneries')->nullable(); // JSON des horaires (jours et heures)
            $table->time('horaire_debut'); // MCD field
            $table->time('horaire_fin'); // MCD field
            $table->json('jour_semaine'); // MCD field
            $table->boolean('jours_feries_inclus')->default(false); // MCD field
            $table->json('vacances')->nullable(); // MCD field
            $table->json('types_etablissement'); // MCD field
            $table->string('chaine_programmee'); // MCD field name
            $table->text('chaine_cryptee')->nullable(); // Chaîne cryptée à copier dans le module
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamp('date_creation')->useCurrent();
            $table->string('cree_par', 26)->nullable(); // ULID
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
            $table->foreign('sirene_id')->references('id')->on('sirenes')->onDelete('restrict');
            $table->foreign('abonnement_id')->references('id')->on('abonnements')->onDelete('restrict');
            $table->foreign('calendrier_id')->references('id')->on('calendriers_scolaires')->onDelete('restrict');
            $table->foreign('cree_par')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmations');
    }
};
