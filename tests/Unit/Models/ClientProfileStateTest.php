<?php

namespace Tests\Unit\Models;

use App\Models\ClientProfile;
use PHPUnit\Framework\TestCase;

class ClientProfileStateTest extends TestCase
{
    public function test_it_marks_visible_profiles_without_submission_as_pending(): void
    {
        $profile = new ClientProfile([
            'onboarding_visible' => true,
        ]);

        $this->assertSame(ClientProfile::ONBOARDING_STATUS_PENDING, $profile->resolvedOnboardingStatus());
        $this->assertTrue($profile->shouldShowOnboardingForm());
    }

    public function test_it_marks_profiles_with_submission_as_completed_when_hidden(): void
    {
        $profile = new ClientProfile([
            'onboarding_visible' => false,
        ]);
        $profile->onboarding_text = 'Submitted onboarding payload';

        $this->assertSame(ClientProfile::ONBOARDING_STATUS_COMPLETED, $profile->resolvedOnboardingStatus());
        $this->assertFalse($profile->shouldShowOnboardingForm());
    }

    public function test_it_marks_visible_profiles_with_existing_submission_as_requested_again(): void
    {
        $profile = new ClientProfile([
            'onboarding_visible' => true,
        ]);
        $profile->onboarding_text = 'Existing onboarding payload';

        $this->assertSame(ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN, $profile->resolvedOnboardingStatus());
        $this->assertSame('Requested Again', $profile->onboardingStatusLabel());
    }

    public function test_it_formats_vip_service_type_label(): void
    {
        $profile = new ClientProfile([
            'service_type' => ClientProfile::SERVICE_TYPE_VIP,
        ]);

        $this->assertSame('VIP', $profile->serviceTypeLabel());
    }
}
