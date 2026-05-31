<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop duplicate rows BEFORE adding the unique constraint. The two
        // tables are handled identically, so the same closure is reused. We
        // use Eloquent's groupBy + a per-driver `WHERE id NOT IN (...)` query
        // so the same migration runs on MySQL, PostgreSQL and SQLite (the
        // last is required by Pest's in-memory test database).
        $this->dropDuplicates('job_applications',     'userId', 'jobVacancyId');
        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['userId', 'jobVacancyId'], 'unique_user_job_application');
        });

        $this->dropDuplicates('training_applications', 'userId', 'trainingSessionId');
        Schema::table('training_applications', function (Blueprint $table) {
            $table->unique(['userId', 'trainingSessionId'], 'unique_user_training_application');
        });
    }

    /**
     * Cross-database "keep only the latest row per (col1, col2) pair" delete.
     * Identifies the IDs to keep via a groupBy(max(created_at)) on the same
     * table, then deletes everything else. Works on MySQL/PostgreSQL/SQLite.
     */
    private function dropDuplicates(string $table, string $col1, string $col2): void
    {
        $keepIds = DB::table($table)
            ->select(DB::raw('MAX(created_at) AS max_created'), $col1, $col2)
            ->groupBy($col1, $col2)
            ->get()
            ->map(fn ($row) => DB::table($table)
                ->where($col1, $row->$col1)
                ->where($col2, $row->$col2)
                ->where('created_at', $row->max_created)
                ->value('id'))
            ->filter()
            ->all();

        if (empty($keepIds)) {
            // Empty table — nothing to drop. Skip to avoid `id NOT IN ()`
            // which is a SQL error on some drivers.
            return;
        }

        DB::table($table)->whereNotIn('id', $keepIds)->delete();
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('unique_user_job_application');
        });

        Schema::table('training_applications', function (Blueprint $table) {
            $table->dropUnique('unique_user_training_application');
        });
    }
};
