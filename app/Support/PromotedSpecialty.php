<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Specialty;
use Illuminate\Support\Collection;

/**
 * Which clinical area the grid promotes.
 *
 * ONE PLACE, and computed rather than stored, for the same two reasons
 * FeaturedPackage is: two places would eventually disagree, and an
 * `is_promoted` column would be a second thing to keep in step with
 * `sort_order` that drifts the first time somebody reorders the list.
 *
 * WHAT PROMOTION MEANS HERE, AND WHAT IT MUST NOT. On the packages table the
 * raised column is a recommendation, and a recommendation about money has to
 * be defensible — which is why FeaturedPackage deliberately errs to the
 * cheaper of two middles. Nothing on this grid has a price and nothing links
 * to checkout, so this is not a recommendation at all: it is typographic
 * hierarchy on a list of eight things that would otherwise read as eight
 * identical tiles.
 *
 * It therefore takes the FIRST area in the clinic's own display order. That is
 * the one the practice already chose to lead with, in the admin, by dragging
 * it there. Picking a middle index would be inventing an editorial judgement
 * the data does not contain; picking at random would make the page different
 * on every load for no reason a visitor could ever understand.
 */
final class PromotedSpecialty
{
    /**
     * @param  Collection<int, Specialty>  $specialties  in display order
     */
    public static function indexIn(Collection $specialties): ?int
    {
        return $specialties->isEmpty() ? null : 0;
    }

    /**
     * @param  Collection<int, Specialty>  $specialties  in display order
     */
    public static function slugIn(Collection $specialties): ?string
    {
        $index = self::indexIn($specialties);

        return $index === null ? null : $specialties->values()[$index]->slug;
    }
}
