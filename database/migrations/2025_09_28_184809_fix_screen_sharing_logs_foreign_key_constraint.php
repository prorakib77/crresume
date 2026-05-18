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
        // This migration is no longer needed since the foreign key constraint
        // is already properly set up in the create_screen_sharing_logs_table migration
        // The table was created with the correct foreign key constraint
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is no longer needed since the foreign key constraint
        // is already properly set up in the create_screen_sharing_logs_table migration
        return;
    }
};
