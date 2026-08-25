<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\ClinicSchema;
use App\Support\PublicContent;
use App\Support\Reviews;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * The whole homepage in one method.
     *
     * Every set comes from PublicContent, which caches it under an explicit
     * key and is busted by the models themselves on save. On a warm cache this
     * action issues no database queries at all; cold, it issues exactly eight —
     * one per set, none of them in a loop, none of them lazy. HomePageQueryTest
     * pins both numbers so a stray relationship access in a Blade file shows up
     * as a failing test rather than as a slow page nobody profiles.
     *
     * Nothing is paginated because nothing here is long: four packages, eight
     * specialties, three stories, three articles and a handful of FAQs. If any
     * of those grows past a screenful the answer is a dedicated page, not an
     * infinite homepage.
     */
    public function __invoke(): View
    {
        return view('pages.home', [
            /*
             * Built here rather than as an inline expression on the layout
             * component. Blade evaluates an anonymous component's attribute
             * expressions more than once, so `:schema="ClinicSchema::toJson()"`
             * in the view built the whole document twice per request — one
             * wasted cache read that nothing in the output revealed.
             */
            'schema' => ClinicSchema::toJson(),
            'services' => PublicContent::services(),
            'specialties' => PublicContent::specialties(),
            /*
             * Real reviews, and only if there are enough of them. Below three
             * approved the section does not render at all — see the section
             * component and App\Support\Reviews for why an almost-empty
             * testimonials block is worse than none.
             */
            'reviews' => Reviews::shouldDisplay() ? Reviews::published(3) : null,
            'reviewAggregate' => Reviews::aggregate(),
            'faqs' => PublicContent::faqs(),
            'posts' => PublicContent::latestPosts(3),
            'videos' => PublicContent::videos(),
            'plateFoods' => PublicContent::plateFoods(),
        ]);
    }
}
