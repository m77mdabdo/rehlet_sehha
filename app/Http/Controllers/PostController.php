<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    /**
     * A single article.
     *
     * Deliberately minimal — the articles section of the homepage links here,
     * and linking the homepage at a guaranteed 404 would be a worse outcome
     * than a plain but complete page. Typography, related posts and sharing
     * belong to whichever task owns the blog.
     *
     * published() rather than findOrFail on the slug alone: a scheduled post
     * with a future published_at must 404 like any unpublished draft, or the
     * embargo is only as good as nobody guessing the URL.
     */
    public function show(string $slug): View
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return view('pages.post', ['post' => $post]);
    }
}
