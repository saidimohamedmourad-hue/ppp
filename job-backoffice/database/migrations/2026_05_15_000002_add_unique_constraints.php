<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate job applications — keep only the latest per (userId, jobVacancyId)
        DB::statement("
            DELETE ja1 FROM job_applications ja1
            INNER JOIN job_applications ja2
            WHERE ja1.userId = ja2.userId
              AND ja1.jobVacancyId = ja2.jobVacancyId
              AND ja1.created_at < ja2.created_at
        ");

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['userId', 'jobVacancyId'], 'unique_user_job_application');
        });

        // Remove duplicate training applications — keep only the latest per (userId, trainingSessionId)
        DB::statement("
            DELETE ta1 FROM training_applications ta1
            INNER JOIN training_applications ta2
            WHERE ta1.userId = ta2.userId
              AND ta1.trainingSessionId = ta2.trainingSessionId
              AND ta1.created_at < ta2.created_at
        ");

        Schema::table('training_applications', function (Blueprint $table) {
            $table->unique(['userId', 'trainingSessionId'], 'unique_user_training_application');
        });
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
