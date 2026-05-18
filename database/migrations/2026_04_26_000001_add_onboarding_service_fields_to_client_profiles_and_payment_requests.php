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
            $table->string('onboarding_status')->default('pending')->after('onboarding_visible');
            $table->string('service_package')->nullable()->after('estimated_application_start_date');
            $table->string('service_type')->default('regular')->after('service_package');
            $table->string('client_signature_path')->nullable()->after('service_type');
            $table->timestamp('policy_acknowledged_at')->nullable()->after('client_signature_path');
        });

        Schema::table('payment_requests', function (Blueprint $table) {
            $table->string('payment_link', 2048)->nullable()->after('note');
        });

        DB::table('client_profiles')
            ->orderBy('id')
            ->select([
                'id',
                'onboarding_visible',
                'onboarding_submitted_at',
                'onboarding_requested_at',
                'onboarding_text',
                'onboarding_resume_file',
                'onboarding_form_file',
                'onboarding_note',
            ])
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $hasOnboardingPayload =
                        filled($profile->onboarding_submitted_at)
                        || filled($profile->onboarding_text)
                        || filled($profile->onboarding_resume_file)
                        || filled($profile->onboarding_form_file)
                        || filled($profile->onboarding_note);

                    $servicePackage = null;
                    $onboardingText = (string) ($profile->onboarding_text ?? '');

                    if (preg_match('/Selected Package:\s*([^\r\n]+)/i', $onboardingText, $matches) === 1) {
                        $servicePackage = trim((string) ($matches[1] ?? '')) ?: null;
                    }

                    $onboardingStatus = 'pending';

                    if ((bool) $profile->onboarding_visible) {
                        $onboardingStatus = $hasOnboardingPayload ? 'requested_again' : 'pending';
                    } elseif ($hasOnboardingPayload) {
                        $onboardingStatus = 'completed';
                    }

                    DB::table('client_profiles')
                        ->where('id', $profile->id)
                        ->update([
                            'onboarding_status' => $onboardingStatus,
                            'service_package' => $servicePackage,
                            'service_type' => 'regular',
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropColumn('payment_link');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_status',
                'service_package',
                'service_type',
                'client_signature_path',
                'policy_acknowledged_at',
            ]);
        });
    }
};
