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

        // Update the application_status enum to include 'incomplete'
        DB::statement("ALTER TABLE work_updates MODIFY COLUMN application_status ENUM('applied', 'interview', 'hired', 'rejected', 'incomplete') DEFAULT 'applied'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->supportsNativeEnumAlter()) {
            return;
        }

        // Revert back to original enum values
        DB::statement("ALTER TABLE work_updates MODIFY COLUMN application_status ENUM('applied', 'interview', 'hired', 'rejected') DEFAULT 'applied'");
    }
};
