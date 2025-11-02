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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('user_id', 26); // ULID - MCD field
            $table->timestamp('date_generation')->useCurrent(); // MCD field
            $table->timestamp('expire_le'); // MCD field name (was date_expiration)
            $table->boolean('utilise')->default(false); // MCD field
            $table->string('telephone');
            $table->string('code', 6);
            $table->boolean('valide')->default(false); // MCD field
            $table->boolean('est_verifie')->default(false); // MCD field name (was verifie)
            $table->boolean('verifie')->default(false);
            $table->timestamp('date_expiration')->nullable();
            $table->timestamp('date_verification')->nullable();
            $table->integer('tentatives')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->index(['telephone', 'code', 'est_verifie']);
            $table->index(['telephone', 'code', 'verifie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
