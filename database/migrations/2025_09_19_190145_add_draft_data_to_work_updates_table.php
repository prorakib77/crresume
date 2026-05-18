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
            $table->json('draft_data')->nullable()->after('status');
            $table->timestamp('draft_saved_at')->nullable()->after('draft_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_updates', function (Blueprint $table) {
            $table->dropColumn(['draft_data', 'draft_saved_at']);
        });
    }
};
