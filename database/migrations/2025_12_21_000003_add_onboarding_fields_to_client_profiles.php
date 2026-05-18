<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('onboarding_resume_file')->nullable()->after('resume');
            $table->string('onboarding_form_file')->nullable()->after('onboarding_resume_file');
            $table->longText('onboarding_text')->nullable()->after('onboarding_form_file');
            $table->text('onboarding_note')->nullable()->after('onboarding_text');
            $table->timestamp('onboarding_submitted_at')->nullable()->after('onboarding_note');
            $table->timestamp('onboarding_requested_at')->nullable()->after('onboarding_submitted_at');
            $table->boolean('onboarding_visible')->default(true)->after('onboarding_requested_at');
        });

        // Ensure existing clients get visibility enabled
        DB::table('client_profiles')
            ->whereNull('onboarding_visible')
            ->update(['onboarding_visible' => true]);
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_resume_file',
                'onboarding_form_file',
                'onboarding_text',
                'onboarding_note',
                'onboarding_submitted_at',
                'onboarding_requested_at',
                'onboarding_visible',
            ]);
        });
    }
};
