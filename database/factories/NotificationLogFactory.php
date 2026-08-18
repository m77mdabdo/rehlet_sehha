<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Models\Appointment;
use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<NotificationLog>
 */
class NotificationLogFactory extends Factory
{
    protected $model = NotificationLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'recipient' => fake('en_US')->safeEmail(),
            'template' => fake()->randomElement([
                'appointment.confirmed',
                'appointment.reminder',
                'appointment.cancelled',
            ]),
            'status' => 'sent',
            'error' => null,
            'sent_at' => Carbon::now()->subMinutes(fake()->numberBetween(1, 6000)),
        ];
    }

    public function failed(): self
    {
        return $this->state(fn (): array => [
            'status' => 'failed',
            'error' => 'Gateway timeout after 30s',
            'sent_at' => null,
        ]);
    }

    public function queued(): self
    {
        return $this->state(fn (): array => [
            'status' => 'queued',
            'sent_at' => null,
        ]);
    }
}
