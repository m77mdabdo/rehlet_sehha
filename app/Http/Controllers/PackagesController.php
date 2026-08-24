<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class PackagesController extends Controller
{
    /**
     * The packages page.
     *
     * The homepage section is the summary: four cards, four prices. This is
     * where somebody actually decides, so it carries what the cards leave out —
     * a full comparison, what happens in the weeks between sessions, how
     * payment works, and what happens when a session has to move.
     *
     * TWO QUERIES COLD, ZERO WARM. Both sets come from the same cache the
     * homepage uses, so a visitor who arrives here from a search pays for them
     * once and a visitor who came via the homepage pays nothing. Neither set is
     * re-filtered in the view.
     */
    public function __invoke(): View
    {
        $services = PublicContent::services();

        return view('pages.packages', [
            'services' => $services,
            // The footer's service list is the same collection, so passing it
            // explicitly avoids the layout reaching for a second copy.
            'footerServices' => $services,
            // Buying questions, not the homepage's general ones. Someone on
            // this page has already decided the clinic does what she needs.
            'faqs' => PublicContent::buyingFaqs(),
        ]);
    }
}
