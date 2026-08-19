<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

/**
 * Which packages actually suit which clinical area.
 *
 * Pairing everything with everything would be the easy seed and a useless one:
 * a specialty page exists to narrow the choice for someone who has just
 * arrived from a search for their own condition, and a page listing all four
 * packages has narrowed nothing.
 *
 * The reasoning, so it can be argued with rather than guessed at:
 *
 *   - Lab review suits every area. It is the cheapest, shortest way in, and
 *     for several of these conditions the patient arrives holding results they
 *     do not understand.
 *   - The three-month programme belongs to CHRONIC work — PCOS, medical
 *     nutrition, weight management. These are conditions managed over months,
 *     and offering a one-off consultation as the headline for them would be
 *     selling the wrong thing.
 *   - Pregnancy is time-boxed by definition: a month of follow-up fits a
 *     trimester; three months usually outlives the question being asked.
 *   - Children and corporate work do not get the three-month programme. For
 *     children the follow-up cadence is different and set by growth; for
 *     corporate the buyer is an employer, and a personal programme is not what
 *     is being bought.
 *
 * Order within each area matters and is not the price order: it is the answer
 * to "where should someone with THIS start?".
 */
class ServiceSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, list<string>> $map specialty slug => service slugs, best first */
        $map = [
            'medical-nutrition' => [
                'single-consultation',
                'three-months-programme',
                'lab-review',
            ],
            'weight-management' => [
                'three-months-programme',
                'one-month-programme',
                'single-consultation',
            ],
            'pregnancy-nutrition' => [
                'single-consultation',
                'one-month-programme',
                'lab-review',
            ],
            'sports-nutrition' => [
                'one-month-programme',
                'single-consultation',
                'lab-review',
            ],
            'child-nutrition' => [
                'single-consultation',
                'one-month-programme',
                'lab-review',
            ],
            'pcos-hormonal' => [
                'three-months-programme',
                'lab-review',
                'single-consultation',
            ],
            'lab-review' => [
                'lab-review',
                'single-consultation',
            ],
            'corporate-wellness' => [
                'single-consultation',
                'one-month-programme',
            ],
        ];

        $services = Service::query()->pluck('id', 'slug');
        $specialties = Specialty::query()->pluck('id', 'slug');

        $missing = [];
        $attached = 0;

        foreach ($map as $specialtySlug => $serviceSlugs) {
            if (! $specialties->has($specialtySlug)) {
                $missing[] = "specialty:{$specialtySlug}";

                continue;
            }

            /** @var array<int, array{sort_order: int}> $pivot */
            $pivot = [];

            foreach ($serviceSlugs as $index => $serviceSlug) {
                if (! $services->has($serviceSlug)) {
                    $missing[] = "service:{$serviceSlug}";

                    continue;
                }

                $pivot[(int) $services[$serviceSlug]] = ['sort_order' => $index + 1];
                $attached++;
            }

            // sync rather than attach: re-running the seeder must converge on
            // this map, not stack duplicate pairings on top of the last run.
            Specialty::query()
                ->findOrFail($specialties[$specialtySlug])
                ->services()
                ->sync($pivot);
        }

        if ($missing !== []) {
            // Loudly, because a silently missing pairing is a specialty page
            // that renders with no packages and no error.
            $this->command?->warn('ServiceSpecialtySeeder could not resolve: '.implode(', ', array_unique($missing)));
        }

        $this->command?->info(sprintf('Paired %d service/specialty combinations.', $attached));
    }
}
