<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'processed' and 'rejected'
        DB::statement("ALTER TABLE otp_submissions MODIFY COLUMN status ENUM('pending', 'reviewed', 'approved', 'processed', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum to original values
        DB::statement("ALTER TABLE otp_submissions MODIFY COLUMN status ENUM('pending', 'reviewed', 'approved') DEFAULT 'pending'");
    }
};
