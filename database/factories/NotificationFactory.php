<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Models\WorkUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement([
                Notification::TYPE_INFO,
                Notification::TYPE_SUCCESS,
                Notification::TYPE_WARNING,
                Notification::TYPE_ERROR,
                Notification::TYPE_WORK_UPDATE,
                Notification::TYPE_APPROVAL,
                Notification::TYPE_REJECTION,
                Notification::TYPE_SYSTEM
            ]),
            'priority' => $this->faker->randomElement([
                Notification::PRIORITY_LOW,
                Notification::PRIORITY_NORMAL,
                Notification::PRIORITY_HIGH,
                Notification::PRIORITY_URGENT
            ]),
            'data' => $this->faker->optional()->randomElement([
                ['key' => 'value'],
                ['action' => 'test', 'user_id' => 1],
                []
            ]),
            'notifiable_type' => null,
            'notifiable_id' => null,
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-7 days', 'now'),
            'action_url' => $this->faker->optional()->url(),
            'expires_at' => $this->faker->optional(0.2)->dateTimeBetween('now', '+30 days'),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is of info type.
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_INFO,
        ]);
    }

    /**
     * Indicate that the notification is of success type.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_SUCCESS,
        ]);
    }

    /**
     * Indicate that the notification is of warning type.
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_WARNING,
        ]);
    }

    /**
     * Indicate that the notification is of error type.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_ERROR,
        ]);
    }

    /**
     * Indicate that the notification is work update related.
     */
    public function workUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_WORK_UPDATE,
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => WorkUpdate::factory(),
        ]);
    }

    /**
     * Indicate that the notification is an approval.
     */
    public function approval(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_APPROVAL,
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => WorkUpdate::factory(),
        ]);
    }

    /**
     * Indicate that the notification is a rejection.
     */
    public function rejection(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_REJECTION,
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => WorkUpdate::factory(),
        ]);
    }

    /**
     * Indicate that the notification is a system notification.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Notification::TYPE_SYSTEM,
        ]);
    }

    /**
     * Indicate that the notification has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Notification::PRIORITY_LOW,
        ]);
    }

    /**
     * Indicate that the notification has normal priority.
     */
    public function normalPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Notification::PRIORITY_NORMAL,
        ]);
    }

    /**
     * Indicate that the notification has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Notification::PRIORITY_HIGH,
        ]);
    }

    /**
     * Indicate that the notification has urgent priority.
     */
    public function urgentPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Notification::PRIORITY_URGENT,
        ]);
    }

    /**
     * Indicate that the notification is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-7 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the notification will expire soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('now', '+2 days'),
        ]);
    }
}