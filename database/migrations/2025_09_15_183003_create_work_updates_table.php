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
        // database/migrations/2025_09_15_000002_create_work_updates_table.php
Schema::create('work_updates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
    $table->string('job_title');
    $table->string('company');
    $table->date('applied_date');
    $table->string('job_link')->nullable();
    $table->string('applied_method')->nullable();
    $table->text('note')->nullable();
    $table->text('remarks')->nullable();
    $table->string('applied_proof')->nullable(); // store file path
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_updates');
    }
};
