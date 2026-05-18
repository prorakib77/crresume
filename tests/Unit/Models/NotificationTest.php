<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkUpdate;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class NotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $workUpdate;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::create(['name' => 'agent', 'display_name' => 'Agent']);
        $this->user = User::factory()->create();
        $this->user->roles()->attach($role);
        
        $this->workUpdate = WorkUpdate::factory()->create();
    }

    public function test_notification_can_be_created()
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'type' => Notification::TYPE_INFO
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'type' => Notification::TYPE_INFO
        ]);
    }

    public function test_notification_belongs_to_user()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($this->user->id, $notification->user->id);
    }

    public function test_notification_can_have_notifiable_model()
    {
        $notification = Notification::factory()->create([
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => $this->workUpdate->id
        ]);

        $this->assertInstanceOf(WorkUpdate::class, $notification->notifiable);
        $this->assertEquals($this->workUpdate->id, $notification->notifiable->id);
    }

    public function test_notification_can_be_marked_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);

        $this->assertTrue($notification->isUnread());
        $this->assertFalse($notification->isRead());

        $result = $notification->markAsRead();

        $this->assertTrue($result);
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertTrue($notification->fresh()->isRead());
        $this->assertFalse($notification->fresh()->isUnread());
    }

    public function test_notification_can_be_marked_as_unread()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => now()
        ]);

        $this->assertTrue($notification->isRead());

        $result = $notification->markAsUnread();

        $this->assertTrue($result);
        $this->assertNull($notification->fresh()->read_at);
        $this->assertTrue($notification->fresh()->isUnread());
    }

    public function test_notification_scopes()
    {
        $readNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => now()
        ]);
        
        $unreadNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);

        $this->assertCount(1, Notification::read()->get());
        $this->assertCount(1, Notification::unread()->get());
        $this->assertEquals($readNotification->id, Notification::read()->first()->id);
        $this->assertEquals($unreadNotification->id, Notification::unread()->first()->id);
    }

    public function test_notification_type_scope()
    {
        Notification::factory()->create(['type' => Notification::TYPE_INFO]);
        Notification::factory()->create(['type' => Notification::TYPE_SUCCESS]);
        Notification::factory()->create(['type' => Notification::TYPE_ERROR]);

        $this->assertCount(1, Notification::ofType(Notification::TYPE_INFO)->get());
        $this->assertCount(1, Notification::ofType(Notification::TYPE_SUCCESS)->get());
    }

    public function test_notification_priority_scope()
    {
        Notification::factory()->create(['priority' => Notification::PRIORITY_LOW]);
        Notification::factory()->create(['priority' => Notification::PRIORITY_HIGH]);
        Notification::factory()->create(['priority' => Notification::PRIORITY_URGENT]);

        $this->assertCount(1, Notification::withPriority(Notification::PRIORITY_HIGH)->get());
        $this->assertCount(1, Notification::withPriority(Notification::PRIORITY_URGENT)->get());
    }

    public function test_notification_active_scope()
    {
        $activeNotification = Notification::factory()->create([
            'expires_at' => now()->addDay()
        ]);
        
        $expiredNotification = Notification::factory()->create([
            'expires_at' => now()->subDay()
        ]);
        
        $noExpiryNotification = Notification::factory()->create([
            'expires_at' => null
        ]);

        $activeNotifications = Notification::active()->get();
        
        $this->assertCount(2, $activeNotifications);
        $this->assertTrue($activeNotifications->contains($activeNotification));
        $this->assertTrue($activeNotifications->contains($noExpiryNotification));
        $this->assertFalse($activeNotifications->contains($expiredNotification));
    }

    public function test_notification_is_expired()
    {
        $expiredNotification = Notification::factory()->create([
            'expires_at' => now()->subDay()
        ]);
        
        $activeNotification = Notification::factory()->create([
            'expires_at' => now()->addDay()
        ]);
        
        $noExpiryNotification = Notification::factory()->create([
            'expires_at' => null
        ]);

        $this->assertTrue($expiredNotification->isExpired());
        $this->assertFalse($activeNotification->isExpired());
        $this->assertFalse($noExpiryNotification->isExpired());
    }

    public function test_notification_type_class_attribute()
    {
        $successNotification = Notification::factory()->create(['type' => Notification::TYPE_SUCCESS]);
        $errorNotification = Notification::factory()->create(['type' => Notification::TYPE_ERROR]);
        $warningNotification = Notification::factory()->create(['type' => Notification::TYPE_WARNING]);

        $this->assertEquals('bg-green-100 text-green-800', $successNotification->type_class);
        $this->assertEquals('bg-red-100 text-red-800', $errorNotification->type_class);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $warningNotification->type_class);
    }

    public function test_notification_type_icon_attribute()
    {
        $successNotification = Notification::factory()->create(['type' => Notification::TYPE_SUCCESS]);
        $errorNotification = Notification::factory()->create(['type' => Notification::TYPE_ERROR]);
        $workUpdateNotification = Notification::factory()->create(['type' => Notification::TYPE_WORK_UPDATE]);

        $this->assertEquals('check-circle', $successNotification->type_icon);
        $this->assertEquals('x-circle', $errorNotification->type_icon);
        $this->assertEquals('document-text', $workUpdateNotification->type_icon);
    }

    public function test_notification_priority_class_attribute()
    {
        $lowNotification = Notification::factory()->create(['priority' => Notification::PRIORITY_LOW]);
        $highNotification = Notification::factory()->create(['priority' => Notification::PRIORITY_HIGH]);
        $urgentNotification = Notification::factory()->create(['priority' => Notification::PRIORITY_URGENT]);

        $this->assertEquals('border-l-gray-400', $lowNotification->priority_class);
        $this->assertEquals('border-l-yellow-400', $highNotification->priority_class);
        $this->assertEquals('border-l-red-400', $urgentNotification->priority_class);
    }

    public function test_create_work_update_notification()
    {
        $notification = Notification::createWorkUpdateNotification(
            $this->user,
            $this->workUpdate,
            'submitted'
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($this->user->id, $notification->user_id);
        $this->assertEquals('Work Update Submitted', $notification->title);
        $this->assertEquals(Notification::TYPE_WORK_UPDATE, $notification->type);
        $this->assertEquals(WorkUpdate::class, $notification->notifiable_type);
        $this->assertEquals($this->workUpdate->id, $notification->notifiable_id);
    }

    public function test_create_system_notification()
    {
        $notification = Notification::createSystemNotification(
            $this->user,
            'System Maintenance',
            'The system will be under maintenance from 10 PM to 2 AM',
            ['maintenance_window' => '10 PM - 2 AM'],
            Notification::PRIORITY_HIGH
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($this->user->id, $notification->user_id);
        $this->assertEquals('System Maintenance', $notification->title);
        $this->assertEquals(Notification::TYPE_SYSTEM, $notification->type);
        $this->assertEquals(Notification::PRIORITY_HIGH, $notification->priority);
        $this->assertEquals(['maintenance_window' => '10 PM - 2 AM'], $notification->data);
    }

    public function test_mark_all_as_read()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'read_at' => null
        ]);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => now()
        ]);

        $count = Notification::markAllAsRead($this->user);

        $this->assertEquals(3, $count);
        $this->assertEquals(0, Notification::where('user_id', $this->user->id)->unread()->count());
    }

    public function test_cleanup_expired()
    {
        Notification::factory()->create(['expires_at' => now()->subDay()]);
        Notification::factory()->create(['expires_at' => now()->addDay()]);
        Notification::factory()->create(['expires_at' => null]);

        $count = Notification::cleanupExpired();

        $this->assertEquals(1, $count);
        $this->assertEquals(2, Notification::count());
    }
}