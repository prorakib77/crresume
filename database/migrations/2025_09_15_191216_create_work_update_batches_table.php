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
        Schema::create('work_update_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->date('submission_date');
            $table->integer('total_updates')->default(0);
            $table->integer('approved_updates')->default(0);
            $table->integer('rejected_updates')->default(0);
            $table->integer('pending_updates')->default(0);
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'partially_approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['agent_id', 'submission_date']);
            $table->index(['status', 'created_at']);
            
            // Unique constraint: one batch per agent per day
            $table->unique(['agent_id', 'submission_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_update_batches');
    }
};
