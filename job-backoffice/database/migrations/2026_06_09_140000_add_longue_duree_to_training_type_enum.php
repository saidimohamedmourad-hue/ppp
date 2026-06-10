<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute le type "longue_duree" à l'enum `type` des sessions de formation.
     * (en_ligne / accelerer / presentiel / longue_duree)
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE training_sessions MODIFY COLUMN type ENUM('en_ligne','accelerer','presentiel','longue_duree') NOT NULL DEFAULT 'presentiel'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE training_sessions MODIFY COLUMN type ENUM('en_ligne','accelerer','presentiel') NOT NULL DEFAULT 'presentiel'");
    }
};
