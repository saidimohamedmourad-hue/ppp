<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('filename');
            $table->string('fileUri');
            $table->string('contactDetails');
            $table->longText('education');
            $table->longText('experience');
            $table->longText('summary');
            $table->longText('skills');

            /*************  ✨ Windsurf Command 🌟  *************/
            $table->timestamps();
            $table->softDeletes();
/*******  de69a2a3-fdcc-4bc7-abe6-59f8cc91d057  *******/    

            $table->uuid('userId');
            $table->foreign('userId')->references('id')->on('users')->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
