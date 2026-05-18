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
            // Add batch relationship
            $table->foreignId('batch_id')->nullable()->after('client_id')->constrained('work_update_batches')->onDelete('set null');
            
            // Enhance status column with more options
            $table->dropColumn('status');
        });
        
        // Re-add status column with enhanced enum values
        Schema::table('work_updates', function (Blueprint $table) {
            $table->enum('status', [
                'draft', 
                'submitted', 
                'under_review', 
                'approved', 
                'rejected', 
                'requires_revision'
            ])->default('draft')->after('applied_proof');
            
            // Add approval workflow fields
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            
            // Enhance applied_method with enum values
            $table->dropColumn('applied_method');
        });
        
        // Re-add applied_method column with enum values
        Schema::table('work_updates', function (Blueprint $table) {
            $table->enum('applied_method', [
                'web', 
                'linkedin', 
                'referral', 
                'direct', 
                'email', 
                'other'
            ])->nullable()->after('job_link');
            
            // Add indexes for better search performance
            $table->index(['status', 'created_at']);
            $table->index(['agent_id', 'applied_date']);
            $table->index(['client_id', 'applied_date']);
            $table->index(['batch_id']);
            
            // Full-text search indexes
            $table->fullText(['job_title', 'company']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_updates', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['batch_id']);
            $table->dropColumn(['batch_id', 'approved_at', 'rejection_reason']);
            
            // Drop indexes
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['agent_id', 'applied_date']);
            $table->dropIndex(['client_id', 'applied_date']);
            $table->dropIndex(['batch_id']);
            $table->dropFullText(['job_title', 'company']);
            
            // Restore original columns
            $table->dropColumn(['status', 'applied_method']);
        });
        
        // Restore original simple columns
        Schema::table('work_updates', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('applied_proof');
            $table->string('applied_method')->nullable()->after('job_link');
        });
    }
};
