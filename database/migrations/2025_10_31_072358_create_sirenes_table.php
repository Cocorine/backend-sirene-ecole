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
        Schema::create('sirenes', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('modele_id', 26)->nullable(); // ULID - MCD uses modele_id not modele_sirene_id
            $table->string('ecole_id', 26)->nullable(); // ULID
            $table->string('site_id', 26)->nullable(); // ULID - MCD field
            $table->string('numero_serie')->unique(); // Généré à l'usine
            $table->date('date_installation')->nullable(); // MCD field (not nullable)
            $table->date('date_fin')->nullable(); // MCD field
            $table->date('date_fabrication')->nullable();
            $table->string('etat')->default('active'); // MCD field
            $table->string('statut', 50)->nullable(); // MCD field - ex: active, hors_service
            $table->enum('old_statut', ['en_stock', 'reserve', 'installe', 'en_panne', 'hors_service'])->default('en_stock'); // Keep old for compatibility
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('modele_id')->references('id')->on('modeles_sirene')->onDelete('restrict');
            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sirenes');
    }
};
