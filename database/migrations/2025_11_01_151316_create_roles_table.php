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
        Schema::create('roles', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('nom');
            $table->string('slug');
            $table->string('roleable_id', 26)->nullable(); // ULID polymorphic
            $table->string('roleable_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['roleable_id', 'roleable_type']);

            // Unique composite: le nom du role est unique par rapport à roleable
            $table->unique(['roleable_id', 'roleable_type', 'nom']);
            $table->unique(['roleable_id', 'roleable_type', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
