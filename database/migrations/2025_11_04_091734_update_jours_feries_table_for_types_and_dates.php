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
        Schema::table('jours_feries', function (Blueprint $table) {
            // Drop old columns that are no longer needed or replaced
            if (Schema::hasColumn('jours_feries', 'libelle')) {
                $table->dropColumn('libelle');
            }
            if (Schema::hasColumn('jours_feries', 'nom')) {
                $table->dropColumn('nom');
            }
            if (Schema::hasColumn('jours_feries', 'date_ferie')) {
                $table->dropColumn('date_ferie');
            }
            if (Schema::hasColumn('jours_feries', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('jours_feries', 'type_jour')) {
                $table->dropColumn('type_jour');
            }

            // Rename 'date' to 'date_debut' if it exists
            if (Schema::hasColumn('jours_feries', 'date')) {
                $table->renameColumn('date', 'date_debut');
            } else {
                // If 'date' column doesn't exist, add 'date_debut'
                $table->date('date_debut')->after('pays_id');
            }

            // Add 'intitule_journee' as text
            $table->text('intitule_journee')->after('pays_id');

            // Add 'date_fin'
            $table->date('date_fin')->nullable()->after('date_debut');

            // Ensure 'est_national' exists (assuming it's a boolean and might be added by another migration or was intended)
            if (!Schema::hasColumn('jours_feries', 'est_national')) {
                $table->boolean('est_national')->default(false)->after('date_fin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jours_feries', function (Blueprint $table) {
            // Revert columns to their previous state (approximated)
            if (Schema::hasColumn('jours_feries', 'intitule_journee')) {
                $table->dropColumn('intitule_journee');
            }
            if (Schema::hasColumn('jours_feries', 'date_fin')) {
                $table->dropColumn('date_fin');
            }
            if (Schema::hasColumn('jours_feries', 'date_debut')) {
                $table->renameColumn('date_debut', 'date');
            }
            if (Schema::hasColumn('jours_feries', 'est_national')) {
                $table->dropColumn('est_national');
            }

            // Re-add original columns (as they were in create_jours_feries_table)
            $table->string('libelle')->nullable()->after('pays_id');
            $table->string('nom')->nullable()->after('libelle');
            $table->date('date_ferie')->nullable()->after('nom');
            $table->enum('type', ['national', 'personnalise'])->default('national')->after('date_ferie');
        });
    }
};
