<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function supportsNativeEnumAlter(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->supportsNativeEnumAlter()) {
            return;
        }

        // Update the enum to include 'otp_submission'
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('info', 'success', 'warning', 'error', 'work_update', 'approval', 'rejection', 'system', 'otp_request', 'otp_submission') DEFAULT 'info'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsNativeEnumAlter()) {
            return;
        }

        // Revert the enum to remove 'otp_submission'
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('info', 'success', 'warning', 'error', 'work_update', 'approval', 'rejection', 'system', 'otp_request') DEFAULT 'info'");
    }
};
