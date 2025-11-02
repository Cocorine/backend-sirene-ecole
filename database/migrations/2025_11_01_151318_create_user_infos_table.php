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
        Schema::create('user_infos', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('user_id', 26); // ULID
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone', 20)->unique();
            $table->string('email', 100)->unique()->nullable();
            $table->string('ville_id', 26)->nullable(); // ULID
            $table->text('adresse')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};
