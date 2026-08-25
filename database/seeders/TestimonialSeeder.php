<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DELIBERATELY EMPTY.
 *
 * This used to seed three testimonials that read like patients and were
 * written by nobody. They are gone, and nothing replaces them, because the
 * only acceptable replacement is a review a patient actually wrote.
 *
 * HOW REAL ONES ARRIVE:
 *
 *   1. An appointment is marked completed.
 *   2. Three days later `clinic:send-review-requests` invites that patient,
 *      with a one-time token link.
 *   3. She writes a rating and a comment, and separately decides whether it
 *      may be published. The consent box is unticked and stays unticked
 *      unless she ticks it.
 *   4. Somebody at the clinic reads it and approves or rejects it in the
 *      admin. Nothing publishes itself.
 *   5. The homepage shows them only once THREE have been approved, and shows
 *      an average rating only once TEN have.
 *
 * If this file ever grows rows again, the site is lying about its patients.
 * The testimonials table itself is left in place for now because the old model
 * still references it; it holds no rows and nothing renders from it.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // Nothing. See the note above.
    }
}
