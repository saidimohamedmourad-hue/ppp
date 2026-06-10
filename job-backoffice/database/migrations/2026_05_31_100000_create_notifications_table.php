<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standard Laravel notifications table — same shape as `php artisan
 * notifications:table` would emit, but tweaked for UUID primary keys on the
 * notifiable side (our User model is UUID).
 *
 * Each row represents one delivered notification:
 *   - polymorphic notifiable so a single table can serve every notifiable
 *     type we might add later (companies, schools, etc.)
 *   - `data` is JSON-encoded payload (icon, title, action_url, …)
 *   - `read_at` is null while unread; set when the user clicks the bell entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Hot path: "give me unread notifications for this user" — used
            // by the bell badge endpoint.
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
