<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Same fix as the job_applications enum alignment, for the training side.
 *
 * The API validates `pending,reviewed,accepted,rejected` but the column was
 * declared as `(pending, accepted, rejected)`, so setting `reviewed` raised
 * "Data truncated for column 'status'". This brings the enum in sync.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE training_applications MODIFY status ENUM('pending', 'reviewed', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::table('training_applications')->where('status', 'reviewed')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE training_applications MODIFY status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
