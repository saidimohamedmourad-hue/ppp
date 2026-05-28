<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds:
 *   - `type` enum on training_sessions (en_ligne / accelerer / presentiel)
 *   - `cancellation_reason` text on training_sessions (shown when status=cancelled)
 *   - `is_waitlist` boolean on training_applications
 *     (true = applied while session was full; does not count toward currentParticipants)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->enum('type', ['en_ligne', 'accelerer', 'presentiel'])
                ->default('presentiel')
                ->after('location');
            $table->text('cancellation_reason')->nullable()->after('status');
        });

        Schema::table('training_applications', function (Blueprint $table) {
            $table->boolean('is_waitlist')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn(['type', 'cancellation_reason']);
        });
        Schema::table('training_applications', function (Blueprint $table) {
            $table->dropColumn('is_waitlist');
        });
    }
};
