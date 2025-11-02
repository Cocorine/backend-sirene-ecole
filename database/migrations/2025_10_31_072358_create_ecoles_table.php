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
        Schema::create('ecoles', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('reference')->unique(); // MCD field
            $table->text('nom_complet'); // MCD field
            $table->string('slug', 100); // MCD field
            $table->string('telephone_contact', 20)->unique(); // MCD field
            $table->string('email_contact', 100)->unique()->nullable(); // MCD field
            $table->json('types_etablissement'); // MCD field - array of types
            $table->string('nom');
            $table->string('code_etablissement')->unique(); // Code unique de l'établissement
            $table->string('telephone')->unique();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_prenom')->nullable();
            $table->string('responsable_telephone')->nullable();
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->timestamp('date_inscription')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
