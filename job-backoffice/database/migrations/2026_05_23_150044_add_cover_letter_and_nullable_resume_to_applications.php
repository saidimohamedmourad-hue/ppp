<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->mediumText('cover_letter')->nullable()->after('resumeId');
            $table->uuid('resumeId')->nullable()->change();
        });

        Schema::table('training_applications', function (Blueprint $table) {
            $table->mediumText('cover_letter')->nullable()->after('resumeId');
            $table->uuid('resumeId')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('cover_letter');
            $table->uuid('resumeId')->nullable(false)->change();
        });

        Schema::table('training_applications', function (Blueprint $table) {
            $table->dropColumn('cover_letter');
            $table->uuid('resumeId')->nullable(false)->change();
        });
    }
};
