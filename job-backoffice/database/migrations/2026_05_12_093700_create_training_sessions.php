<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->longText('description');
            $table->string('location');
            $table->date('trainingDate');
            $table->date('endDate')->nullable();
            $table->time('startTime')->nullable();
            $table->time('endTime')->nullable();
            $table->unsignedInteger('maxParticipants')->default(0);
            $table->unsignedInteger('currentParticipants')->default(0);
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('open');
            $table->string('salary')->nullable();
            $table->unsignedInteger('viewCount')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->uuid('trainingCategoryId');
            $table->uuid('schoolId');
            $table->foreign('trainingCategoryId')->references('id')->on('training_categories')->onDelete('restrict');
            $table->foreign('schoolId')->references('id')->on('schools')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
