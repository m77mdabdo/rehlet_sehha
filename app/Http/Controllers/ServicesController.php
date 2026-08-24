<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class ServicesController extends Controller
{
    public function __invoke(): View
    {
        /*
         * All eight clinical areas at length. The homepage shows eight cards
         * with a line each; this is where each one earns a paragraph, a "who
         * this is for" list, and a route into its own landing page.
         */
        return view('pages.services', [
            'specialties' => PublicContent::specialties(),
            'footerServices' => PublicContent::services(),
        ]);
    }
}
