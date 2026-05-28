<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the 'draft' value from training_sessions.status enum.
 *
 * Pre-existing rows with status='draft' are migrated to 'closed' so they
 * stay hidden from candidates (same behavior as draft was).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('training_sessions')->where('status', 'draft')->update(['status' => 'closed']);

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE training_sessions MODIFY status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open'");
        }
        // For sqlite/pgsql the enum is enforced at the app layer (validation
        // rules); the column type is a varchar so no schema change needed.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE training_sessions MODIFY status ENUM('draft', 'open', 'closed', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
