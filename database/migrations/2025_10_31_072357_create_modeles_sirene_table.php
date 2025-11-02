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
        Schema::create('modeles_sirene', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('nom')->unique(); // Ex: SireneV1, SireneV2
            $table->string('reference')->unique(); // Référence commerciale
            $table->text('description')->nullable();
            $table->json('specifications')->nullable(); // Specs techniques (voltage, fréquence, portée, etc.)
            $table->string('version_firmware')->nullable(); // MCD field
            $table->decimal('prix_unitaire', 10, 2)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modeles_sirene');
    }
};
