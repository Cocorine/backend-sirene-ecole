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
        Schema::create('techniciens', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->decimal('review', 3, 1)->default(0.0); // MCD field - moyenne des notes reçues
            $table->text('specialite')->nullable(); // Ex: Électricien, Électronicien
            $table->boolean('disponibilite')->default(true); // MCD field
            $table->timestamp('date_inscription')->useCurrent(); // MCD field
            $table->enum('statut', ['actif', 'inactif', 'en_mission'])->default('actif');
            $table->date('date_embauche')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('techniciens');
    }
};
