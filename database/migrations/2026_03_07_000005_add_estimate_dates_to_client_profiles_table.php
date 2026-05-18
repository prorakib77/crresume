<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('client_profiles', 'estimated_resume_completion_date')) {
                $table->date('estimated_resume_completion_date')
                    ->nullable()
                    ->after('service_end_date');
            }

            if (!Schema::hasColumn('client_profiles', 'estimated_cover_letter_completion_date')) {
                $table->date('estimated_cover_letter_completion_date')
                    ->nullable()
                    ->after('estimated_resume_completion_date');
            }

            if (!Schema::hasColumn('client_profiles', 'estimated_application_start_date')) {
                $table->date('estimated_application_start_date')
                    ->nullable()
                    ->after('estimated_cover_letter_completion_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('client_profiles', 'estimated_application_start_date')) {
                $table->dropColumn('estimated_application_start_date');
            }

            if (Schema::hasColumn('client_profiles', 'estimated_cover_letter_completion_date')) {
                $table->dropColumn('estimated_cover_letter_completion_date');
            }

            if (Schema::hasColumn('client_profiles', 'estimated_resume_completion_date')) {
                $table->dropColumn('estimated_resume_completion_date');
            }
        });
    }
};
