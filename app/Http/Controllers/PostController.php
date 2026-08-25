<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    /**
     * A single article.
     *
     * published() rather than findOrFail on the slug alone: a scheduled post
     * with a future published_at must 404 like any unpublished draft, or the
     * embargo is only as good as nobody guessing the URL.
     *
     * RELATED POSTS ARE FILTERED IN PHP, NOT IN SQL. category is a translated
     * JSON column, so `where('category', ...)` would compare against the whole
     * JSON blob and match nothing. The set is three rows and already cached
     * for every other page, so filtering it in memory costs nothing and one
     * query less than doing it properly in SQL would.
     */
    public function show(string $slug): View
    {
        /*
         * The reviewer is eager-loaded because the byline names them on every
         * article page. Left lazy it is one extra query per article view, for
         * a relation the template is guaranteed to touch.
         */
        $post = Post::published()->with('reviewer')->where('slug', $slug)->firstOrFail();

        $published = PublicContent::latestPosts(50);

        return view('pages.post', [
            'post' => $post,
            'footerServices' => PublicContent::services(),
            'related' => $published
                ->reject(fn (Post $other): bool => $other->id === $post->id)
                ->filter(fn (Post $other): bool => $other->category === $post->category)
                ->take(2)
                ->values(),
        ]);
    }
}
