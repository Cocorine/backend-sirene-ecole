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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('ecole_id', 26); // ULID
            $table->string('site_id', 26)->nullable(); // ULID - MCD field
            $table->string('sirene_id', 26); // ULID - MCD field
            $table->string('parent_abonnement_id', 26)->nullable(); // ULID - MCD field (self-referencing)
            $table->string('numero_abonnement')->unique()->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('statut', ['actif', 'expire', 'en_attente', 'suspendu'])->default('en_attente');
            $table->decimal('montant', 10, 2)->default(0);
            $table->boolean('auto_renouvellement')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
            $table->foreign('sirene_id')->references('id')->on('sirenes')->onDelete('restrict');
            // $table->foreign('parent_abonnement_id')->references('id')->on('abonnements')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
