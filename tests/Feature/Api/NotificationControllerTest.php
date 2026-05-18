<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Notification;
use App\Models\WorkUpdate;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $userRole = Role::create(['name' => 'agent', 'display_name' => 'Agent']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        
        $manageNotificationsPerm = Permission::create(['name' => 'manage-notifications']);
        $adminRole->permissions()->attach($manageNotificationsPerm);
        
        // Create users
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
        
        // Assign roles
        $this->user->roles()->attach($userRole);
        $this->admin->roles()->attach($adminRole);
    }

    public function test_user_can_get_their_notifications()
    {
        Sanctum::actingAs($this->user);
        
        Notification::factory()->count(3)->create(['user_id' => $this->user->id]);
        Notification::factory()->count(2)->create(); // Other users' notifications

        $response = $this->getJson('/api/notifications');

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'message',
                            'type',
                            'priority',
                            'read_at',
                            'created_at'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'unread_count'
                    ]
                ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_filter_notifications_by_type()
    {
        Sanctum::actingAs($this->user);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => Notification::TYPE_SUCCESS
        ]);
        
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => Notification::TYPE_ERROR
        ]);

        $response = $this->getJson('/api/notifications?type=' . Notification::TYPE_SUCCESS);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(Notification::TYPE_SUCCESS, $response->json('data.0.type'));
    }

    public function test_user_can_filter_notifications_by_read_status()
    {
        Sanctum::actingAs($this->user);
        
        Notification::factory()->unread()->create(['user_id' => $this->user->id]);
        Notification::factory()->read()->create(['user_id' => $this->user->id]);

        // Get unread notifications
        $response = $this->getJson('/api/notifications?read=false');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertNull($response->json('data.0.read_at'));

        // Get read notifications
        $response = $this->getJson('/api/notifications?read=true');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertNotNull($response->json('data.0.read_at'));
    }

    public function test_user_can_get_unread_count()
    {
        Sanctum::actingAs($this->user);
        
        Notification::factory()->count(3)->unread()->create(['user_id' => $this->user->id]);
        Notification::factory()->count(2)->read()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'unread_count' => 3
                    ]
                ]);
    }

    public function test_user_can_get_recent_notifications()
    {
        Sanctum::actingAs($this->user);
        
        Notification::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/notifications/recent?limit=10');

        $response->assertOk();
        $this->assertCount(10, $response->json('data'));
        $this->assertArrayHasKey('unread_count', $response->json('meta'));
    }

    public function test_user_can_mark_notification_as_read()
    {
        Sanctum::actingAs($this->user);
        
        $notification = Notification::factory()->unread()->create(['user_id' => $this->user->id]);

        $response = $this->postJson("/api/notifications/{$notification->id}/mark-read");

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read()
    {
        Sanctum::actingAs($this->user);
        
        $otherNotification = Notification::factory()->create();

        $response = $this->postJson("/api/notifications/{$otherNotification->id}/mark-read");

        $response->assertNotFound();
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        Sanctum::actingAs($this->user);
        
        Notification::factory()->count(5)->unread()->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'marked_count' => 5,
                        'unread_count' => 0
                    ]
                ]);

        $this->assertEquals(0, Notification::where('user_id', $this->user->id)->unread()->count());
    }

    public function test_user_can_delete_their_notification()
    {
        Sanctum::actingAs($this->user);
        
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Notification deleted'
                ]);

        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    public function test_user_cannot_delete_other_users_notification()
    {
        Sanctum::actingAs($this->user);
        
        $otherNotification = Notification::factory()->create();

        $response = $this->deleteJson("/api/notifications/{$otherNotification->id}");

        $response->assertNotFound();
    }

    public function test_admin_can_view_notification_statistics()
    {
        Sanctum::actingAs($this->admin);
        
        Notification::factory()->count(5)->create();
        Notification::factory()->count(3)->unread()->create();

        $response = $this->getJson('/api/notifications/statistics');

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_notifications',
                        'unread_notifications',
                        'notifications_by_type',
                        'notifications_by_priority'
                    ]
                ]);
    }

    public function test_admin_can_send_system_notification()
    {
        Sanctum::actingAs($this->admin);
        
        $users = User::factory()->count(3)->create();
        
        $response = $this->postJson('/api/notifications/send-system', [
            'title' => 'System Maintenance',
            'message' => 'The system will be under maintenance tonight.',
            'user_ids' => $users->pluck('id')->toArray(),
            'priority' => 'high'
        ]);

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'sent_count' => 3
                    ]
                ]);

        $this->assertDatabaseHas('notifications', [
            'title' => 'System Maintenance',
            'type' => Notification::TYPE_SYSTEM,
            'priority' => 'high'
        ]);
    }

    public function test_non_admin_cannot_send_system_notification()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/notifications/send-system', [
            'title' => 'Test',
            'message' => 'Test message'
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_cleanup_old_notifications()
    {
        Sanctum::actingAs($this->admin);
        
        // Create old read notifications
        Notification::factory()->count(3)->read()->create([
            'created_at' => now()->subDays(35)
        ]);
        
        // Create recent notifications
        Notification::factory()->count(2)->create();

        $response = $this->postJson('/api/notifications/cleanup', [
            'days' => 30
        ]);

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'deleted_old',
                        'deleted_expired',
                        'total_deleted'
                    ]
                ]);
    }

    public function test_test_notification_endpoint_works_in_development()
    {
        $this->app['env'] = 'local';
        
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/notifications/test');

        $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Test notification sent'
                ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Test Notification'
        ]);
    }

    public function test_test_notification_endpoint_blocked_in_production()
    {
        $this->app['env'] = 'production';
        
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/notifications/test');

        $response->assertForbidden();
    }
}