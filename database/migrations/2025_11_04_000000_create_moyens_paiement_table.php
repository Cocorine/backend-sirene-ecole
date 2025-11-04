<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moyens_paiement', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('paiementable_id', 26);
            $table->string('paiementable_type');
            $table->enum('type', ['MOBILE_MONEY', 'CARTE_BANCAIRE', 'WALLET']);
            $table->string('operateur')->nullable();
            $table->string('numero_telephone')->nullable();
            $table->string('numero_carte_masque')->nullable();
            $table->string('token_carte')->nullable();
            $table->string('nom_titulaire')->nullable();
            $table->string('date_expiration')->nullable();
            $table->string('email_wallet')->nullable();
            $table->string('id_wallet')->nullable();
            $table->boolean('par_defaut')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestamp('derniere_utilisation')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['paiementable_id', 'paiementable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moyens_paiement');
    }
};
