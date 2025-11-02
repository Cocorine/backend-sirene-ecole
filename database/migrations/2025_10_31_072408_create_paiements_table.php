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
        Schema::create('paiements', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('abonnement_id', 26); // ULID
            $table->string('ecole_id', 26); // ULID
            $table->string('numero_transaction')->unique()->nullable();
            $table->decimal('montant', 10, 2);
            $table->enum('moyen', ['MOBILE_MONEY', 'CARTE_BANCAIRE', 'QR_CODE', 'VIREMENT'])->default('MOBILE_MONEY'); // MCD field name
            //$table->enum('mode_paiement', ['qr_code', 'en_ligne', 'mobile_money', 'carte', 'especes'])->nullable();

            $table->string('statut', 50)->nullable(); // MCD field - ex: succès, échec
            //$table->enum('old_statut', ['en_attente', 'valide', 'echoue', 'rembourse'])->default('en_attente');
            $table->string('reference_externe')->nullable(); // Référence du prestataire de paiement
            $table->json('metadata')->nullable(); // Données supplémentaires (API response, etc.)
            $table->timestamp('date_paiement');
            $table->timestamp('date_validation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('abonnement_id')->references('id')->on('abonnements')->onDelete('restrict');
            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
