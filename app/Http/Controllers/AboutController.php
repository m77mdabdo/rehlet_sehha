<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        /*
         * The practitioner page.
         *
         * ITS COPY IS THE PRACTITIONER'S OWN, and was the last thing on the
         * site still marked TODO_COPY. Credentials, training and registration
         * are claims about a licensed professional, and the philosophy
         * paragraph is her account of how she works — none of it was ours to
         * invent, so the structure was built empty and clinic:verify-copy
         * blocked production until she answered.
         *
         * A photograph is still outstanding; the section falls back to the
         * mark rather than to stock, because a stock portrait on this page
         * would be a claim about who treats you.
         */
        return view('pages.about', [
            'footerServices' => PublicContent::services(),
        ]);
    }
}
