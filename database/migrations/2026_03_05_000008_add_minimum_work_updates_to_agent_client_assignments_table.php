<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('agent_client_assignments', 'minimum_work_updates')) {
            Schema::table('agent_client_assignments', function (Blueprint $table) {
                $table->unsignedTinyInteger('minimum_work_updates')
                    ->default(4)
                    ->after('service_end_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('agent_client_assignments', 'minimum_work_updates')) {
            Schema::table('agent_client_assignments', function (Blueprint $table) {
                $table->dropColumn('minimum_work_updates');
            });
        }
    }
};
