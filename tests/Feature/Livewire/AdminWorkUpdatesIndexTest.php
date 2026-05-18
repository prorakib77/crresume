<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\WorkUpdatesIndex;
use App\Models\User;
use App\Models\WorkUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminWorkUpdatesIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagination_can_move_to_next_page(): void
    {
        $agent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        foreach (range(1, 20) as $index) {
            WorkUpdate::factory()->create([
                'agent_id' => $agent->id,
                'client_id' => $client->id,
                'job_title' => 'Admin Job ' . $index,
                'company' => 'Company ' . $index,
                'status' => WorkUpdate::STATUS_SUBMITTED,
                'application_status' => WorkUpdate::APPLICATION_STATUS_APPLIED,
                'applied_date' => now()->subDays($index),
                'created_at' => now()->subMinutes($index),
                'updated_at' => now()->subMinutes($index),
            ]);
        }

        Livewire::test(WorkUpdatesIndex::class)
            ->call('setPage', 2)
            ->assertViewHas('workUpdates', fn ($workUpdates) => $workUpdates->currentPage() === 2);
    }

    public function test_filters_reset_pagination_to_first_page(): void
    {
        $agent = User::factory()->create([
            'role_id' => User::ROLE_AGENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $client = User::factory()->create([
            'role_id' => User::ROLE_CLIENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        foreach (range(1, 20) as $index) {
            WorkUpdate::factory()->create([
                'agent_id' => $agent->id,
                'client_id' => $client->id,
                'job_title' => 'Filter Job ' . $index,
                'company' => 'Filter Company ' . $index,
                'status' => WorkUpdate::STATUS_SUBMITTED,
                'application_status' => WorkUpdate::APPLICATION_STATUS_APPLIED,
                'applied_date' => now()->subDays($index),
                'created_at' => now()->subMinutes($index),
                'updated_at' => now()->subMinutes($index),
            ]);
        }

        Livewire::test(WorkUpdatesIndex::class)
            ->call('setPage', 2)
            ->set('search', 'Filter Job 1')
            ->assertViewHas('workUpdates', fn ($workUpdates) => $workUpdates->currentPage() === 1);
    }
}
