<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * The booking page. All of the behaviour lives in the BookingWizard
     * Livewire component; this only passes through the deep-link slug.
     *
     * The slug is NOT validated here — the component checks it against the
     * active services and silently ignores an unknown one, so a link from an
     * old price list opens the wizard at step 1 rather than 404ing someone who
     * was trying to give the clinic money.
     */
    public function __invoke(Request $request): View
    {
        return view('pages.booking', [
            'preselectedService' => $request->string('service')->toString() ?: null,
        ]);
    }
}
