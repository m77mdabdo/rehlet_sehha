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
     * RELATED POSTS ARE A REAL QUERY NOW. They used to be filtered in PHP
     * over the cached "latest posts" list, because `category` was a translated
     * JSON column that SQL could not match on. Two things were wrong with it:
     * the list was capped, so an older article in the same category could
     * never surface, and comparing JSON blobs matched only when two posts had
     * identical text in BOTH languages. Post::relatedPosts() replaces it.
     */
    public function show(string $slug): View
    {
        /*
         * Reviewer, category, tags and citations eager-loaded: the byline
         * names the reviewer, the header shows the category, the footer lists
         * the tags, and the references section lists the citations. All four
         * are certain to be touched on every article page.
         */
        $post = Post::published()
            ->with(['reviewer', 'category', 'tags', 'citations'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.post', [
            'post' => $post,
            'footerServices' => PublicContent::services(),
            'related' => $post->relatedPosts(2),
        ]);
    }
}
