<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Niveau d'études minimum requis pour une formation, choisi par l'école à
     * la création / l'édition. Nullable pour ne pas casser les sessions
     * existantes ; l'API et le back-office l'exigent sur les nouvelles.
     */
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->string('min_education_level')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('min_education_level');
        });
    }
};
