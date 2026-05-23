<?php

namespace Tests\Unit\Services;

use App\Models\CustomizationSetting;
use App\Services\EmailTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://crresumes.com',
        ]);

        URL::forceRootUrl('https://crresumes.com');
        URL::forceScheme('https');
    }

    public function test_email_header_logo_prefers_the_active_site_logo(): void
    {
        CustomizationSetting::setValue('site_logo', 'customization/logos/current-site-logo.png', 'image', 'branding');
        CustomizationSetting::setValue('email_header_logo', 'customization/email-branding/stale-email-logo.png', 'image', 'email');
        CustomizationSetting::setValue('email_header_logo_url', 'https://crresumes.com/files/customization/logos/stale-email-logo-url.png', 'text', 'email');

        $resolvedLogo = email_header_logo();

        $this->assertStringContainsString('/files/customization/logos/current-site-logo.png', (string) $resolvedLogo);
        $this->assertStringNotContainsString('stale-email-logo.png', (string) $resolvedLogo);
        $this->assertStringNotContainsString('stale-email-logo-url.png', (string) $resolvedLogo);
    }

    public function test_dynamic_template_render_uses_the_active_site_logo_in_the_header(): void
    {
        CustomizationSetting::setValue('site_logo', 'customization/logos/current-site-logo.png', 'image', 'branding');
        CustomizationSetting::setValue('email_header_logo', 'customization/email-branding/stale-email-logo.png', 'image', 'email');
        CustomizationSetting::setValue('email_header_logo_url', 'https://crresumes.com/files/customization/logos/stale-email-logo-url.png', 'text', 'email');

        $service = new EmailTemplateService();
        $rendered = $service->render(
            'welcome_client',
            ['site_name' => 'Crresumes'],
            'Welcome to Working For Happiness',
            '<p>Hello Rakib,</p>'
        );

        $this->assertStringContainsString('/files/customization/logos/current-site-logo.png', $rendered['body']);
        $this->assertStringNotContainsString('stale-email-logo.png', $rendered['body']);
        $this->assertStringNotContainsString('stale-email-logo-url.png', $rendered['body']);
    }
}
