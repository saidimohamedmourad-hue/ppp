<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','company-owner','job-seeker','school-owner') NOT NULL DEFAULT 'job-seeker'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        // Required so MySQL accepts shrinking the ENUM (school-owner would be invalid).
        DB::table('users')->where('role', 'school-owner')->update(['role' => 'job-seeker']);

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','company-owner','job-seeker') NOT NULL DEFAULT 'job-seeker'");
    }
};
