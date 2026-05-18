<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspendedUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_is_redirected_to_the_suspension_screen(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_SUSPENDED,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertRedirect(route('account.suspended'));
    }

    public function test_suspended_user_can_view_the_suspension_screen(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_SUSPENDED,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('account.suspended'));

        $response
            ->assertOk()
            ->assertSeeText('User Suspended');
    }

    public function test_active_user_is_redirected_away_from_the_suspension_screen(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('account.suspended'));

        $response->assertRedirect(route('dashboard'));
    }
}
