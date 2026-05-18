<?php

namespace Database\Factories;

use App\Models\WorkUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkUpdate>
 */
class WorkUpdateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkUpdate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => User::factory(),
            'client_id' => User::factory(),
            'batch_id' => null,
            'job_title' => $this->faker->jobTitle(),
            'company' => $this->faker->company(),
            'applied_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'job_link' => $this->faker->url(),
            'applied_method' => $this->faker->randomElement([
                WorkUpdate::METHOD_WEB,
                WorkUpdate::METHOD_LINKEDIN,
                WorkUpdate::METHOD_REFERRAL,
                WorkUpdate::METHOD_DIRECT,
                WorkUpdate::METHOD_EMAIL,
                WorkUpdate::METHOD_OTHER
            ]),
            'note' => $this->faker->optional()->paragraph(),
            'remarks' => $this->faker->optional()->sentence(),
            'applied_proof' => $this->faker->optional()->url(),
            'status' => $this->faker->randomElement([
                WorkUpdate::STATUS_DRAFT,
                WorkUpdate::STATUS_SUBMITTED,
                WorkUpdate::STATUS_UNDER_REVIEW,
                WorkUpdate::STATUS_APPROVED,
                WorkUpdate::STATUS_REJECTED,
                WorkUpdate::STATUS_REQUIRES_REVISION
            ]),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the work update is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkUpdate::STATUS_DRAFT,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the work update is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkUpdate::STATUS_SUBMITTED,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the work update is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkUpdate::STATUS_APPROVED,
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the work update is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkUpdate::STATUS_REJECTED,
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the work update requires revision.
     */
    public function requiresRevision(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkUpdate::STATUS_REQUIRES_REVISION,
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the work update was applied via LinkedIn.
     */
    public function linkedin(): static
    {
        return $this->state(fn (array $attributes) => [
            'applied_method' => WorkUpdate::METHOD_LINKEDIN,
        ]);
    }

    /**
     * Indicate that the work update was applied via company website.
     */
    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'applied_method' => WorkUpdate::METHOD_WEB,
        ]);
    }

    /**
     * Indicate that the work update was applied via referral.
     */
    public function referral(): static
    {
        return $this->state(fn (array $attributes) => [
            'applied_method' => WorkUpdate::METHOD_REFERRAL,
        ]);
    }
}