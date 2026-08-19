<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * The booking page — a placeholder until Task 5 builds the form.
     *
     * It exists now because the packages section deep-links to it with a
     * service preselected, and a homepage that links to a 404 is worse than
     * one that links to a page saying "this is coming". The ?service= slug is
     * validated against the active services here rather than in Task 5, so the
     * deep links can be proven to work today.
     */
    public function __invoke(Request $request): View
    {
        $slug = $request->string('service')->toString();

        // Matched against the cached active services, so an unknown or
        // tampered slug simply preselects nothing instead of 404ing a visitor
        // who followed a link from an old price list.
        $selected = PublicContent::services()
            ->firstWhere('slug', $slug);

        return view('pages.booking', [
            'services' => PublicContent::services(),
            'selected' => $selected,
        ]);
    }
}
