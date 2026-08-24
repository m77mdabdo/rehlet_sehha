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
         * ITS COPY IS STILL TODO_COPY AND MUST STAY THAT WAY. Credentials,
         * training and registration are claims about a licensed professional;
         * inventing them would be inventing a person's qualifications. The
         * structure is built so real copy and a real photograph drop straight
         * in, and clinic:verify-copy blocks production until they do.
         */
        return view('pages.about', [
            'footerServices' => PublicContent::services(),
        ]);
    }
}
