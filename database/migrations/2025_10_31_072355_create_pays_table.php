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
        Schema::create('pays', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('nom')->unique();
            $table->string('code_iso', 3)->unique(); // Code ISO du pays (ex: BEN, TGO)
            $table->string('indicatif_tel', 10); // Indicatif téléphonique (ex: +229)
            $table->string('devise', 3)->nullable(); // Code devise (ex: XOF, EUR)
            $table->string('fuseau_horaire')->default('UTC');
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
        Schema::dropIfExists('pays');
    }
};
