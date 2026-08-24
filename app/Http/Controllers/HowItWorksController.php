<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class HowItWorksController extends Controller
{
    public function __invoke(): View
    {
        /*
         * The four steps at length, plus the part the homepage omits entirely:
         * what happens in the weeks BETWEEN sessions, which is where a plan
         * either survives or does not.
         */
        return view('pages.how-it-works', [
            'footerServices' => PublicContent::services(),
        ]);
    }
}
