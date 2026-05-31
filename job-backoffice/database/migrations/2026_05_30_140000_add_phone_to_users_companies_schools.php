<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a `phone` column to the three contact-relevant tables.
 *
 *   - users.phone      → candidate's phone, shown on their job applications
 *                        to recruiters so they can be contacted directly.
 *                        Also used as the contact phone for company-owner
 *                        and school-owner roles.
 *   - companies.phone  → public contact phone shown on each job vacancy.
 *   - schools.phone    → public contact phone shown on each training session.
 *
 * All three are nullable so we don't break existing rows. The "required"
 * enforcement happens at the application layer (validation rules + UX in the
 * register/apply forms) which is reversible without a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 32)->nullable()->after('email');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'phone')) {
                $table->string('phone', 32)->nullable()->after('website');
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'phone')) {
                $table->string('phone', 32)->nullable()->after('website');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) $table->dropColumn('phone');
        });
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'phone')) $table->dropColumn('phone');
        });
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'phone')) $table->dropColumn('phone');
        });
    }
};
