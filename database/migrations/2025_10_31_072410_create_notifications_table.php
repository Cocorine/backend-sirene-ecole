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
        Schema::create('notifications', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('notifiable_id', 26); // ULID - Polymorphic
            $table->string('notifiable_type'); // Polymorphic (Ecole, Technicien, Admin)
            $table->string('ecole_id', 26)->nullable(); // ULID - MCD field
            $table->string('technicien_id', 26)->nullable(); // ULID - MCD field
            $table->enum('type', ['SMS', 'EMAIL', 'SYSTEME'])->default('SYSTEME'); // MCD field name (was 'canal')
            $table->enum('canal', ['sms', 'email', 'push', 'systeme'])->default('systeme');
            $table->text('message');
            $table->text('titre')->nullable();
            $table->json('data')->nullable(); // Données supplémentaires
            $table->boolean('statut')->default(false); // MCD field name (was 'lu') - lu ou non
            $table->boolean('lu')->default(false);
            $table->timestamp('date_lecture')->nullable();
            $table->timestamp('date_envoi')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['notifiable_id', 'notifiable_type']);
            $table->foreign('ecole_id')->references('id')->on('ecoles')->onDelete('restrict');
            $table->foreign('technicien_id')->references('id')->on('techniciens')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
