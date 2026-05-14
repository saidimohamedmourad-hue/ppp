<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->float('aiGeneratedScore', 2)->default(0);
            $table->mediumText('aiGeneratedFeedback')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->uuid('userId');
            $table->uuid('trainingSessionId');
            $table->uuid('resumeId');
            $table->foreign('userId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('trainingSessionId')->references('id')->on('training_sessions')->onDelete('restrict');
            $table->foreign('resumeId')->references('id')->on('resumes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_applications');
    }
};
