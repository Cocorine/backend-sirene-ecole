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
        Schema::create('sites', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('ecole_principale_id', 26); // ULID
            $table->string('nom');
            $table->boolean('est_principale')->default(true);
            $table->text('adresse')->nullable();
            $table->string('ville_id', 26)->nullable(); // ULID
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ecole_principale_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('restrict');

            // Unique composite: le nom du site est unique par rapport à l'école
            $table->unique(['ecole_principale_id', 'nom']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
