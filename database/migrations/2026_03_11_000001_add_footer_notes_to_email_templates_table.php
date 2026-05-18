<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table): void {
            $table->text('footer_note')->nullable()->after('from_email');
            $table->text('content_note')->nullable()->after('footer_note');
        });

        $globalFooterNote = 'This is an automated email.';

        if (Schema::hasTable('customization_settings')) {
            $storedFooterNote = DB::table('customization_settings')
                ->where('setting_key', 'email_footer_note')
                ->value('setting_value');

            if (is_string($storedFooterNote) && trim($storedFooterNote) !== '') {
                $globalFooterNote = trim($storedFooterNote);
            }
        }

        DB::table('email_templates')
            ->whereNull('footer_note')
            ->update(['footer_note' => $globalFooterNote]);

        DB::table('email_templates')
            ->where('template_key', 'daily_work_update')
            ->whereNull('content_note')
            ->update([
                'content_note' => 'This is an automated message and this inbox is not monitored. Please do not reply to this email.',
            ]);

        $dailyTemplates = DB::table('email_templates')
            ->where('template_key', 'daily_work_update')
            ->get(['id', 'body_template']);

        foreach ($dailyTemplates as $template) {
            $bodyTemplate = (string) ($template->body_template ?? '');

            if (
                $bodyTemplate !== ''
                && str_contains($bodyTemplate, 'This is an automated message and this inbox is not monitored. Please do not reply to this email.')
                && !str_contains($bodyTemplate, '{{content_note}}')
            ) {
                DB::table('email_templates')
                    ->where('id', $template->id)
                    ->update([
                        'body_template' => str_replace(
                            'This is an automated message and this inbox is not monitored. Please do not reply to this email.',
                            '{{content_note}}',
                            $bodyTemplate
                        ),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table): void {
            $table->dropColumn(['footer_note', 'content_note']);
        });
    }
};
