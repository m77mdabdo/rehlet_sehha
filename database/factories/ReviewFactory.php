<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * An INVITED review by default — sent, not yet answered.
     *
     * The states below step it forward one gate at a time, so a test has to
     * say out loud which gates it is opening. A factory that produced an
     * approved, consented review by default would let a test about publishing
     * pass without ever exercising consent.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointment = Appointment::factory()->create();

        return [
            'token' => Review::newToken(),
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'display_name' => 'رنا م.',
            'invited_at' => Carbon::now()->subDays(3),
        ];
    }

    /** She answered, but said nothing about publishing. */
    public function submitted(int $rating = 5, string $comment = 'المتابعة كانت منظمة والخطة كانت واقعية.'): self
    {
        return $this->state(fn (): array => [
            'rating' => $rating,
            'comment' => $comment,
            'submitted_at' => Carbon::now(),
        ]);
    }

    /** She ticked the box. Still not published — the clinic has not read it. */
    public function consented(): self
    {
        return $this->state(fn (): array => ['consented_at' => Carbon::now()]);
    }

    /** Both gates open: she consented and the clinic approved. */
    public function approved(): self
    {
        return $this->state(fn (): array => [
            'consented_at' => Carbon::now(),
            'approved_at' => Carbon::now(),
        ]);
    }
}
