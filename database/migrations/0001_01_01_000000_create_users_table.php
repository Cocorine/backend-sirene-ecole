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
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('nom_utilisateur', 100);
            $table->string('identifiant', 100)->unique();
            $table->string('mot_de_passe');
            $table->enum('type', ['ADMIN', 'TECHNICIEN', 'ECOLE', 'USER'])->default("USER");
            $table->string('user_account_type_id', 26)->nullable(); // ULID polymorphic
            $table->string('user_account_type_type', 100)->nullable();
            $table->string('role_id', 26)->nullable(); // ULID foreign key
            $table->boolean('actif')->default(false);
            $table->integer('statut')->default(-1); // -1, 0, 1
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_account_type_id', 'user_account_type_type']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 26)->nullable()->index(); // ULID
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
