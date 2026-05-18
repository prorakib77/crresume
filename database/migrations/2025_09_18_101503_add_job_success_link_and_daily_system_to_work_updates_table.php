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
        Schema::table('work_updates', function (Blueprint $table) {
            // Add job success link field
            $table->string('job_success_link')->nullable()->after('job_link');

            // Add service end date for client
            $table->date('service_end_date')->nullable()->after('client_id');

            // Add daily constraint index for one submission per agent per client per day
            $table->index(['agent_id', 'client_id', 'applied_date'], 'daily_submission_unique');
        });

        // Create agent_client_assignments table for managing which agents are assigned to which clients
        Schema::create('agent_client_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->date('assigned_date')->default(now());
            $table->date('service_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['agent_id', 'client_id', 'assigned_date']);
            $table->index(['agent_id', 'is_active']);
            $table->index(['client_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_updates', function (Blueprint $table) {
            $table->dropIndex('daily_submission_unique');
            $table->dropColumn(['job_success_link', 'service_end_date']);
        });

        Schema::dropIfExists('agent_client_assignments');
    }
};
