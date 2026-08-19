<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * A deliberately thin homepage for now: enough real data to prove the
     * design tokens, fonts, RTL flip, translations and brand components all
     * work together. The full page is Task 3.
     */
    public function __invoke(): View
    {
        return view('pages.home', [
            'services' => Service::active()->get(),
        ]);
    }
}
