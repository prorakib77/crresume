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
        Schema::create('agent_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->enum('activity_type', ['login', 'logout', 'page_visit', 'check_in', 'check_out']);
            $table->string('page_url')->nullable();
            $table->string('page_title')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('activity_time');
            $table->json('additional_data')->nullable(); // For storing extra information
            $table->timestamps();

            $table->index(['agent_id', 'activity_time']);
            $table->index(['activity_type', 'activity_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_activities');
    }
};
