<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\WorkUpdate;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class WorkUpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $agent;
    protected $client;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $agentRole = Role::create(['name' => 'agent', 'display_name' => 'Agent']);
        $clientRole = Role::create(['name' => 'client', 'display_name' => 'Client']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        
        // Create permissions
        $permissions = [
            'create-work-updates',
            'view-work-updates', 
            'approve-work-updates',
            'manage-notifications',
            'view-activity-logs'
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Assign permissions to roles
        $agentRole->permissions()->attach(Permission::whereIn('name', ['create-work-updates', 'view-work-updates'])->pluck('id'));
        $adminRole->permissions()->attach(Permission::all()->pluck('id'));
        
        // Create users
        $this->agent = User::factory()->create();
        $this->client = User::factory()->create();
        $this->admin = User::factory()->create();
        
        // Assign roles
        $this->agent->roles()->attach($agentRole);
        $this->client->roles()->attach($clientRole);
        $this->admin->roles()->attach($adminRole);
    }

    public function test_work_update_can_be_created()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'agent_id' => $this->agent->id,
            'client_id' => $this->client->id,
            'status' => WorkUpdate::STATUS_DRAFT
        ]);

        $this->assertDatabaseHas('work_updates', [
            'id' => $workUpdate->id,
            'agent_id' => $this->agent->id,
            'client_id' => $this->client->id,
            'status' => WorkUpdate::STATUS_DRAFT
        ]);
    }

    public function test_work_update_belongs_to_agent()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'agent_id' => $this->agent->id
        ]);

        $this->assertInstanceOf(User::class, $workUpdate->agent);
        $this->assertEquals($this->agent->id, $workUpdate->agent->id);
    }

    public function test_work_update_belongs_to_client()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'client_id' => $this->client->id
        ]);

        $this->assertInstanceOf(User::class, $workUpdate->client);
        $this->assertEquals($this->client->id, $workUpdate->client->id);
    }

    public function test_work_update_can_be_submitted()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_DRAFT,
            'agent_id' => $this->agent->id
        ]);

        $this->assertTrue($workUpdate->canBeSubmitted());
        
        $result = $workUpdate->submit();
        
        $this->assertTrue($result);
        $this->assertEquals(WorkUpdate::STATUS_SUBMITTED, $workUpdate->fresh()->status);
        
        // Check activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'submitted',
            'subject_type' => WorkUpdate::class,
            'subject_id' => $workUpdate->id,
            'user_id' => $this->agent->id
        ]);
    }

    public function test_work_update_can_be_approved()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_SUBMITTED,
            'agent_id' => $this->agent->id
        ]);

        $this->assertTrue($workUpdate->canBeApproved());
        
        $result = $workUpdate->approve($this->admin->id, 'Good work!');
        
        $this->assertTrue($result);
        
        $freshUpdate = $workUpdate->fresh();
        $this->assertEquals(WorkUpdate::STATUS_APPROVED, $freshUpdate->status);
        $this->assertEquals($this->admin->id, $freshUpdate->approved_by);
        $this->assertEquals('Good work!', $freshUpdate->remarks);
        $this->assertNotNull($freshUpdate->approved_at);
        
        // Check activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'approved',
            'subject_type' => WorkUpdate::class,
            'subject_id' => $workUpdate->id,
            'user_id' => $this->admin->id
        ]);
        
        // Check notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->agent->id,
            'type' => Notification::TYPE_APPROVAL,
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => $workUpdate->id
        ]);
    }

    public function test_work_update_can_be_rejected()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_SUBMITTED,
            'agent_id' => $this->agent->id
        ]);

        $this->assertTrue($workUpdate->canBeRejected());
        
        $result = $workUpdate->reject($this->admin->id, 'Needs more details');
        
        $this->assertTrue($result);
        
        $freshUpdate = $workUpdate->fresh();
        $this->assertEquals(WorkUpdate::STATUS_REJECTED, $freshUpdate->status);
        $this->assertEquals($this->admin->id, $freshUpdate->approved_by);
        $this->assertEquals('Needs more details', $freshUpdate->rejection_reason);
        $this->assertNotNull($freshUpdate->approved_at);
        
        // Check activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'rejected',
            'subject_type' => WorkUpdate::class,
            'subject_id' => $workUpdate->id,
            'user_id' => $this->admin->id
        ]);
        
        // Check notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->agent->id,
            'type' => Notification::TYPE_REJECTION,
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => $workUpdate->id
        ]);
    }

    public function test_draft_work_update_cannot_be_approved()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_DRAFT
        ]);

        $this->assertFalse($workUpdate->canBeApproved());
        $this->assertFalse($workUpdate->approve($this->admin->id));
    }

    public function test_approved_work_update_cannot_be_submitted()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_APPROVED
        ]);

        $this->assertFalse($workUpdate->canBeSubmitted());
        $this->assertFalse($workUpdate->submit());
    }

    public function test_work_update_search_scope()
    {
        $workUpdate1 = WorkUpdate::factory()->create([
            'job_title' => 'Software Engineer',
            'company' => 'Tech Corp'
        ]);
        
        $workUpdate2 = WorkUpdate::factory()->create([
            'job_title' => 'Data Analyst', 
            'company' => 'Analytics Inc'
        ]);

        $results = WorkUpdate::search('Software')->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals($workUpdate1->id, $results->first()->id);
    }

    public function test_work_update_status_scopes()
    {
        WorkUpdate::factory()->create(['status' => WorkUpdate::STATUS_DRAFT]);
        WorkUpdate::factory()->create(['status' => WorkUpdate::STATUS_SUBMITTED]);
        WorkUpdate::factory()->create(['status' => WorkUpdate::STATUS_APPROVED]);

        $this->assertCount(1, WorkUpdate::withStatus(WorkUpdate::STATUS_DRAFT)->get());
        $this->assertCount(1, WorkUpdate::pendingApproval()->get());
    }

    public function test_work_update_date_range_scope()
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        
        WorkUpdate::factory()->create(['applied_date' => $today]);
        WorkUpdate::factory()->create(['applied_date' => $yesterday]);

        $results = WorkUpdate::dateRange($today, $today)->get();
        
        $this->assertCount(1, $results);
    }

    public function test_work_update_status_labels()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_APPROVED
        ]);

        $this->assertEquals('Approved', $workUpdate->getStatusLabel());
        $this->assertEquals('badge-success', $workUpdate->getStatusBadgeClass());
    }

    public function test_work_update_applied_method_label()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'applied_method' => WorkUpdate::METHOD_LINKEDIN
        ]);

        $this->assertEquals('LinkedIn', $workUpdate->getAppliedMethodLabel());
    }

    public function test_work_update_can_be_edited_when_draft()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_DRAFT
        ]);

        $this->assertTrue($workUpdate->canBeEdited());
    }

    public function test_work_update_cannot_be_edited_when_approved()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_APPROVED
        ]);

        $this->assertFalse($workUpdate->canBeEdited());
    }

    public function test_work_update_revision_request()
    {
        $workUpdate = WorkUpdate::factory()->create([
            'status' => WorkUpdate::STATUS_SUBMITTED,
            'agent_id' => $this->agent->id
        ]);

        $result = $workUpdate->requestRevision($this->admin->id, 'Please add more details');
        
        $this->assertTrue($result);
        $this->assertEquals(WorkUpdate::STATUS_REQUIRES_REVISION, $workUpdate->fresh()->status);
        $this->assertEquals('Please add more details', $workUpdate->fresh()->rejection_reason);
    }
}