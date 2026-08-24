<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class ArticlesController extends Controller
{
    public function __invoke(): View
    {
        /*
         * A LIST, NOT A FILTERED PAGINATED INDEX. There are three published
         * articles. Building category filters and pagination over three items
         * is scaffolding for content that does not exist, and it advertises
         * the emptiness rather than hiding it. When the blog is real this page
         * grows the controls it needs; today it does not need them.
         */
        return view('pages.articles', [
            'posts' => PublicContent::latestPosts(50),
            'footerServices' => PublicContent::services(),
        ]);
    }
}
