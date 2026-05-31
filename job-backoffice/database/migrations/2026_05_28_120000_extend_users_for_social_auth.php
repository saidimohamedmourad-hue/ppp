<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 of PLAN_AUTH_SOCIAL.md.
 *
 * - Makes `password` nullable so social-only accounts (Google / Facebook /
 *   Phone) don't need to set one.
 * - Adds avatar_url so we can persist the photo from the social provider.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // MySQL/MariaDB: dropping the NOT NULL via raw DDL is the most
        // portable path (avoids needing doctrine/dbal). Laravel's
        // ->change() also works in 11+ but raw DDL is faster on big tables.
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
        } else {
            // SQLite (test runner) + PostgreSQL: ->change() uses Laravel 11's
            // native column-altering pipeline so we don't depend on dbal.
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable()->change();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable(false)->change();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar_url')) {
                $table->dropColumn('avatar_url');
            }
        });
    }
};
