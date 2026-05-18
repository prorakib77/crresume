<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('agent_client_assignments', 'service_completed_at')) {
            Schema::table('agent_client_assignments', function (Blueprint $table) {
                $table->timestamp('service_completed_at')->nullable()->after('service_end_date');
            });
        }

        if (!Schema::hasColumn('agent_client_assignments', 'service_completed_by')) {
            Schema::table('agent_client_assignments', function (Blueprint $table) {
                $table->unsignedBigInteger('service_completed_by')
                    ->nullable()
                    ->after('service_completed_at');
                $table->index('service_completed_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('agent_client_assignments', 'service_completed_by')) {
            Schema::table('agent_client_assignments', function (Blueprint $table) {
                $table->dropIndex(['service_completed_by']);
                $table->dropColumn('service_completed_by');
            });
        }

        if (Schema::hasColumn('agent_client_assignments', 'service_completed_at')) {
            Schema::table('agent_client_assignments', function (Blueprint $table) {
                $table->dropColumn('service_completed_at');
            });
        }
    }
};
