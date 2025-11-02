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
        Schema::create('fichiers', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('nom');
            $table->string('slug');
            $table->string('fichierable_id', 26); // ULID polymorphic
            $table->string('fichierable_type'); // ex: 'intervention', 'rapport'
            $table->string('auteur_id', 26); // ULID
            $table->string('ecole_id', 26); // ULID
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fichierable_id', 'fichierable_type']);
            $table->foreign('auteur_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichiers');
    }
};
