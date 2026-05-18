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
        Schema::table('agent_client_assignments', function (Blueprint $table) {
            $table->text('apply_to')->nullable()->after('notes');
            $table->string('resume_file')->nullable()->after('apply_to');
            $table->string('onboarding_form_file')->nullable()->after('resume_file');
            $table->json('cover_letters')->nullable()->after('onboarding_form_file');
            $table->text('note_for_agent')->nullable()->after('cover_letters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_client_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'apply_to',
                'resume_file',
                'onboarding_form_file',
                'cover_letters',
                'note_for_agent'
            ]);
        });
    }
};
