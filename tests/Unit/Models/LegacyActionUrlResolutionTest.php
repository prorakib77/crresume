<?php

namespace Tests\Unit\Models;

use App\Models\Notice;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LegacyActionUrlResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://wfh.crresumes.com',
            'app.internal_action_legacy_hosts' => [
                'amtrakib.com',
                'crresumes.com',
                'full-service.crresumes.com',
            ],
        ]);

        URL::forceRootUrl('https://wfh.crresumes.com');
        URL::forceScheme('https');

        Role::insert([
            ['id' => User::ROLE_SUPER_ADMIN, 'name' => 'super-admin', 'display_name' => 'Super Admin'],
            ['id' => User::ROLE_ADMIN, 'name' => 'admin', 'display_name' => 'Admin'],
            ['id' => User::ROLE_AGENT, 'name' => 'agent', 'display_name' => 'Agent'],
            ['id' => User::ROLE_CLIENT, 'name' => 'client', 'display_name' => 'Client'],
        ]);
    }

    public function test_notification_resolves_legacy_support_ticket_url_to_current_host(): void
    {
        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
        ]);

        $notification = Notification::factory()->make([
            'user_id' => $client->id,
            'action_url' => 'https://crresumes.com/client/support-tickets/ST-9752108',
        ]);

        $notification->setRelation('user', $client);

        $this->assertSame(
            'https://wfh.crresumes.com/client/support-tickets/ST-9752108',
            $notification->resolved_action_url
        );
    }

    public function test_notice_resolves_legacy_onboarding_url_to_current_host(): void
    {
        $notice = new Notice([
            'action_url' => 'https://amtrakib.com/client/onboarding',
        ]);

        $this->assertSame(
            'https://wfh.crresumes.com/client/onboarding',
            $notice->resolved_action_url
        );
    }
}
