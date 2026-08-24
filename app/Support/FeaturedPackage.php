<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Service;
use Illuminate\Support\Collection;

/**
 * Which package the site recommends.
 *
 * ONE PLACE, because two places would eventually disagree. The homepage
 * highlights a card and the comparison table raises a column, and a patient
 * who sees the monthly package featured on the homepage and the three-month
 * one recommended on the packages page has been told the clinic does not know
 * its own mind.
 *
 * It is COMPUTED, not stored. A services.is_featured column would be a second
 * thing to keep in step with sort_order, and the two would disagree the first
 * time somebody reordered the list — which is the reason the homepage section
 * has always derived it rather than storing it.
 *
 * intdiv on count - 1 picks the LOWER middle for an even count, which puts the
 * recommendation on the cheaper of the two central packages. That is the
 * honest side to err on: a default answer that costs more is a sales tactic,
 * and this one has to survive a patient noticing what it is.
 */
final class FeaturedPackage
{
    /**
     * @param  Collection<int, Service>  $services  in display order
     */
    public static function indexIn(Collection $services): ?int
    {
        return $services->isEmpty() ? null : intdiv($services->count() - 1, 2);
    }

    /**
     * @param  Collection<int, Service>  $services  in display order
     */
    public static function slugIn(Collection $services): ?string
    {
        $index = self::indexIn($services);

        return $index === null ? null : $services->values()[$index]->slug;
    }
}
