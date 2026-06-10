<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aligns the job_applications.status enum with the values the app actually
 * uses end-to-end (frontend STATUS_MAP + API validation):
 *
 *   pending      — default, awaiting recruiter action
 *   reviewed     — recruiter opened the application ("Vue")
 *   shortlisted  — recruiter pre-selected the candidate ("Présélectionné")
 *   accepted     — recruiter accepted (interview)
 *   rejected     — recruiter declined
 *
 * The original migration only declared (pending, accepted, rejected), so
 * trying to set `shortlisted`/`reviewed` threw "Data truncated for column
 * 'status'". This brings the column in sync. No data migration needed — the
 * three legacy values are a subset of the new set.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE job_applications MODIFY status ENUM('pending', 'reviewed', 'shortlisted', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
        }
        // sqlite/pgsql store enums as varchar — app-layer validation enforces
        // the allowed set, so no schema change is required there.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Collapse the extra states back into the original three before
            // shrinking the column so we don't truncate live rows.
            DB::table('job_applications')->where('status', 'reviewed')->update(['status' => 'pending']);
            DB::table('job_applications')->where('status', 'shortlisted')->update(['status' => 'accepted']);
            DB::statement("ALTER TABLE job_applications MODIFY status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
