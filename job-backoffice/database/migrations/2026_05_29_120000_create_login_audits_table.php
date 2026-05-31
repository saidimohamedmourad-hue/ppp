<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 hardening — login audit log.
 *
 * Every successful (and selected failed) auth event lands here. Used for:
 *   - security forensics ("when did user X actually log in last?")
 *   - detecting credential stuffing ("100 login failures from one IP")
 *   - GDPR access requests ("here is your sign-in history")
 *
 * Failed attempts (`success=false`) carry no user_id since we don't reveal
 * which email exists; only the IP / UA / attempted_email are recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);           // password | google | facebook | password-reset
            $table->string('event', 32);              // login | reset | refused
            $table->boolean('success')->default(true);
            $table->string('attempted_email')->nullable();
            $table->string('ip', 45)->nullable();     // 45 covers IPv6
            $table->string('user_agent', 512)->nullable();
            $table->string('failure_reason', 128)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['ip', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_audits');
    }
};
