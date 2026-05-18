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
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('screen_shared')->default(false)->after('status');
            $table->timestamp('screen_share_started_at')->nullable()->after('screen_shared');
            $table->timestamp('screen_share_ended_at')->nullable()->after('screen_share_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['screen_shared', 'screen_share_started_at', 'screen_share_ended_at']);
        });
    }
};
