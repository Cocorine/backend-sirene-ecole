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
        Schema::create('pannes', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('ecole_id', 26); // ULID
            $table->string('sirene_id', 26); // ULID
            $table->string('site_id', 26)->nullable(); // ULID - MCD field
            $table->string('numero_panne')->unique()->nullable();
            $table->text('description');
            $table->timestamp('date_signalement')->useCurrent(); // MCD field name
            $table->timestamp('date_declaration')->nullable()->useCurrent();
            $table->timestamp('date_validation')->nullable();
            $table->string('valide_par', 26)->nullable(); // ULID - Admin qui a validé
            $table->enum('priorite', ['basse', 'moyenne', 'haute', 'urgente'])->default('moyenne');
            $table->enum('statut', ['declaree', 'validee', 'assignee', 'en_cours', 'resolue', 'fermee'])->default('declaree');
            $table->boolean('est_cloture')->default(false); // MCD field
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('sirene_id')->references('id')->on('sirenes')->onDelete('restrict');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('restrict');
            $table->foreign('valide_par')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pannes');
    }
};
