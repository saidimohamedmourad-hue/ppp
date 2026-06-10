<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Niveau d'études (Algérie) déclaré par le candidat au moment de postuler /
     * de s'inscrire. Nullable pour ne pas casser les candidatures existantes ;
     * l'API l'exige sur les nouvelles soumissions.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('education_level')->nullable();
        });

        Schema::table('training_applications', function (Blueprint $table) {
            $table->string('education_level')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });

        Schema::table('training_applications', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
