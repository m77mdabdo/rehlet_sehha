<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function __invoke(): View
    {
        /*
         * NO CONTACT FORM, DELIBERATELY. Booking is the action this clinic
         * wants and a second form competes with it — a patient who fills in a
         * "get in touch" box has done something that feels like progress and
         * is not, and then waits. Every route here is one she controls:
         * booking, WhatsApp, the phone. See the view for the rest.
         *
         * No embedded map either. That is a third-party request on a site
         * built not to track its visitors, to show an address we can render as
         * text.
         */
        return view('pages.contact', [
            'hours' => PublicContent::openingHours(),
            'footerServices' => PublicContent::services(),
        ]);
    }
}
