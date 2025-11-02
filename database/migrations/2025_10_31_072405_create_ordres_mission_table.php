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
        Schema::create('ordres_mission', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('panne_id', 26)->nullable(); // ULID
            $table->string('ville_id', 26); // ULID
            $table->timestamp('date_generation')->useCurrent();
            $table->string('technicien_id', 26)->nullable(); // ULID - un seul technicien peut accepter
            $table->timestamp('date_acceptation')->nullable();
            $table->string('valide_par', 26)->nullable(); // ULID - admin validateur
            $table->string('statut')->default('en_attente'); // en_attente, en_cours, termine, cloture
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('restrict');
            $table->foreign('technicien_id')->references('id')->on('techniciens')->onDelete('restrict');
            $table->foreign('valide_par')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordres_mission');
    }
};
