<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le type "longue_duree" à l'enum `type` des sessions de formation.
     * (en_ligne / accelerer / presentiel / longue_duree)
     *
     * L'ALTER ... MODIFY ... ENUM est spécifique à MySQL/MariaDB. Sur SQLite
     * (tests), la colonne est un simple varchar — on ne fait rien.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE training_sessions MODIFY COLUMN type ENUM('en_ligne','accelerer','presentiel','longue_duree') NOT NULL DEFAULT 'presentiel'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE training_sessions MODIFY COLUMN type ENUM('en_ligne','accelerer','presentiel') NOT NULL DEFAULT 'presentiel'");
        }
    }
};
