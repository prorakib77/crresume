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
            $table->enum('application_status', [
                'applied',
                'interview',
                'hired',
                'rejected'
            ])->default('applied')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_updates', function (Blueprint $table) {
            $table->dropColumn('application_status');
        });
    }
};
