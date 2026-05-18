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
        if (Schema::hasTable('screen_sharing_logs') && Schema::hasColumn('screen_sharing_logs', 'meet_attendance_id')) {
            Schema::table('screen_sharing_logs', function (Blueprint $table) {
                $table->renameColumn('meet_attendance_id', 'attendance_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('screen_sharing_logs') && Schema::hasColumn('screen_sharing_logs', 'attendance_id')) {
            Schema::table('screen_sharing_logs', function (Blueprint $table) {
                $table->renameColumn('attendance_id', 'meet_attendance_id');
            });
        }
    }
};
