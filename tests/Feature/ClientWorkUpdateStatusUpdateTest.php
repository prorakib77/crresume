<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\WorkUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWorkUpdateStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_update_their_work_update_status(): void
    {
        Role::query()->create([
            'id' => User::ROLE_AGENT,
            'name' => 'agent',
            'display_name' => 'Agent',
            'is_active' => true,
            'hierarchy_level' => 4,
        ]);

        Role::query()->create([
            'id' => User::ROLE_CLIENT,
            'name' => 'client',
            'display_name' => 'Client',
            'is_active' => true,
            'hierarchy_level' => 5,
        ]);

        $agent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $workUpdate = WorkUpdate::query()->create([
            'agent_id' => $agent->id,
            'client_id' => $client->id,
            'job_title' => 'Customer Support Specialist',
            'company' => 'Example Corp',
            'applied_date' => now()->subDay()->toDateString(),
            'job_link' => 'https://example.com/job',
            'job_success_link' => 'https://example.com/success',
            'applied_method' => WorkUpdate::METHOD_WEB,
            'application_status' => WorkUpdate::APPLICATION_STATUS_APPLIED,
            'note' => 'Initial application submitted.',
            'status' => WorkUpdate::STATUS_APPROVED,
            'approved_at' => now()->subHours(2),
        ]);

        $response = $this
            ->actingAs($client)
            ->put(route('client.work-updates.update', $workUpdate->id), [
                'application_status' => WorkUpdate::APPLICATION_STATUS_INTERVIEW,
            ]);

        $response->assertRedirect(route('client.work-updates.edit', $workUpdate->fresh()->id));

        $this->assertDatabaseHas('work_updates', [
            'id' => $workUpdate->id,
            'application_status' => WorkUpdate::APPLICATION_STATUS_INTERVIEW,
        ]);
    }
}
