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
        Schema::create('tokens_sirene', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('abonnement_id', 26); // ULID
            $table->string('sirene_id', 26); // ULID - MCD field
            $table->string('site_id', 26)->nullable(); // ULID - MCD field
            $table->text('token_crypte'); // Token crypté (AES-256) - MCD name
            $table->text('token_hash')->unique()->nullable(); // Hash du token pour vérification
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamp('date_generation')->nullable(); // MCD field
            $table->timestamp('date_expiration')->nullable(); // MCD field
            $table->timestamp('date_activation')->nullable();
            $table->string('genere_par')->nullable(); // Admin qui a généré le token
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('abonnement_id')->references('id')->on('abonnements')->onDelete('restrict');
            $table->foreign('sirene_id')->references('id')->on('sirenes')->onDelete('restrict');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_sirene');
    }
};
