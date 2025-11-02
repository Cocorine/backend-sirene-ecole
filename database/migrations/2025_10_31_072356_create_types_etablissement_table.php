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
        Schema::create('types_etablissement', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('type')->unique(); // Ex: Maternelle, Primaire, Secondaire, Supérieur
            $table->string('code', 10)->unique(); // Ex: MAT, PRI, SEC, SUP
            $table->text('description')->nullable();
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
        Schema::dropIfExists('types_etablissement');
    }
};
