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
        Schema::create('missions_techniciens', function (Blueprint $table) {
            $table->string('id', 26)->primary(); // ULID
            $table->string('ordre_mission_id', 26); // ULID
            $table->string('technicien_id', 26); // ULID
            $table->string('statut')->default('en_attente'); // en_attente, acceptee, retiree, refusee, terminee
            $table->timestamp('date_acceptation')->nullable();
            $table->timestamp('date_cloture')->nullable();
            $table->timestamp('date_retrait')->nullable(); // si la mission est retirée
            $table->text('motif_retrait')->nullable(); // motif du retrait
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ordre_mission_id')->references('id')->on('ordres_mission')->onDelete('restrict');
            $table->foreign('technicien_id')->references('id')->on('techniciens')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions_techniciens');
    }
};
