<?php

namespace Tests\Feature\SupportTickets;

use App\Models\AgentClientAssignment;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SupportTicketVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_agents_see_their_direct_tickets_and_unassigned_tickets_for_actively_assigned_clients(): void
    {
        $service = app(SupportTicketService::class);

        $admin = User::factory()->create([
            'role_id' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $assignedAgent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $otherAgent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        AgentClientAssignment::create([
            'agent_id' => $assignedAgent->id,
            'client_id' => $client->id,
            'assigned_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $assignedTicket = SupportTicket::create([
            'client_id' => $client->id,
            'agent_id' => $assignedAgent->id,
            'created_by' => $client->id,
            'subject' => 'Assigned support request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $fallbackTicket = SupportTicket::create([
            'client_id' => $client->id,
            'agent_id' => null,
            'created_by' => $client->id,
            'subject' => 'Fallback support request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $otherClient = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $unassignedTicket = SupportTicket::create([
            'client_id' => $otherClient->id,
            'agent_id' => null,
            'created_by' => $otherClient->id,
            'subject' => 'Unassigned support request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $this->assertSame(
            [$assignedTicket->id, $fallbackTicket->id, $unassignedTicket->id],
            $service->queryFor($admin)->orderBy('id')->pluck('id')->all()
        );

        $this->assertSame(
            [$assignedTicket->id, $fallbackTicket->id],
            $service->queryFor($assignedAgent)->orderBy('id')->pluck('id')->all()
        );

        $this->assertSame(
            [],
            $service->queryFor($otherAgent)->orderBy('id')->pluck('id')->all()
        );
    }

    public function test_agents_can_open_unassigned_tickets_for_actively_assigned_clients_only(): void
    {
        $service = app(SupportTicketService::class);

        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $assignedAgent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $otherAgent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        AgentClientAssignment::create([
            'agent_id' => $assignedAgent->id,
            'client_id' => $client->id,
            'assigned_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $assignedTicket = SupportTicket::create([
            'client_id' => $client->id,
            'agent_id' => $assignedAgent->id,
            'created_by' => $client->id,
            'subject' => 'Assigned support request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $fallbackTicket = SupportTicket::create([
            'client_id' => $client->id,
            'agent_id' => null,
            'created_by' => $client->id,
            'subject' => 'Fallback support request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $otherClient = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $trulyUnassignedTicket = SupportTicket::create([
            'client_id' => $otherClient->id,
            'agent_id' => null,
            'created_by' => $otherClient->id,
            'subject' => 'Truly unassigned support request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $service->authorize($assignedTicket, $assignedAgent);
        $service->authorize($fallbackTicket, $assignedAgent);

        try {
            $service->authorize($assignedTicket, $otherAgent);
            $this->fail('Expected a 403 when another agent opens the ticket.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        try {
            $service->authorize($fallbackTicket, $otherAgent);
            $this->fail('Expected a 403 when another agent opens the client fallback ticket.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        try {
            $service->authorize($trulyUnassignedTicket, $assignedAgent);
            $this->fail('Expected a 403 when an agent opens a ticket without an active client assignment.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_clients_can_close_their_own_support_tickets(): void
    {
        $service = app(SupportTicketService::class);

        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $ticket = SupportTicket::create([
            'client_id' => $client->id,
            'agent_id' => null,
            'created_by' => $client->id,
            'subject' => 'Client close request',
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $service->close($client, $ticket);

        $ticket->refresh();

        $this->assertSame(SupportTicket::STATUS_CLOSED, $ticket->status);
        $this->assertSame($client->id, $ticket->closed_by);
        $this->assertNotNull($ticket->closed_at);
    }
}
