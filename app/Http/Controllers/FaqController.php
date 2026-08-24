<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class FaqController extends Controller
{
    public function __invoke(): View
    {
        /*
         * Every question, grouped by category — not the homepage's general
         * set. Someone who came here came with a question.
         */
        return view('pages.faq', [
            'groups' => PublicContent::faqsByCategory(),
            'footerServices' => PublicContent::services(),
        ]);
    }
}
