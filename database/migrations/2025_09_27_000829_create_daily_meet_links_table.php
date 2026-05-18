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
        Schema::create('daily_meet_links', function (Blueprint $table) {
            $table->id();
            $table->date('meeting_date');
            $table->string('meeting_title');
            $table->text('meeting_description')->nullable();
            $table->string('google_meet_link');
            $table->string('google_calendar_event_id')->nullable();
            $table->datetime('meeting_start_time');
            $table->datetime('meeting_end_time');
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('meeting_date');
            $table->index(['meeting_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_meet_links');
    }
};
