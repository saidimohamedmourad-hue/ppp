<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One IQRA user can be linked to multiple social-auth providers (Google,
 * Facebook, Phone via Firebase). This table is the join: it stores the
 * provider-side stable identifier and an optional bag of provider metadata.
 *
 * The (provider, provider_user_id) pair is unique — that's how we recognize a
 * returning user on subsequent sign-ins.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_user_id');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_providers');
    }
};
