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
        Schema::create('oauth_settings', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->default('google_meet');
            $table->text('credentials_json')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('calendar_id')->default('primary');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('meet_room_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_generate_meetings')->default(true);
            $table->time('meeting_start_time')->default('09:00:00');
            $table->time('meeting_end_time')->default('17:00:00');
            $table->integer('meeting_duration_minutes')->default(60);
            $table->json('meeting_attendees')->nullable();
            $table->text('meeting_description')->nullable();
            $table->boolean('send_notifications')->default(true);
            $table->boolean('create_calendar_events')->default(true);
            $table->boolean('auto_join_enabled')->default(false);
            $table->json('meeting_settings')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('sync_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_settings');
    }
};
