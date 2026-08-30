<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\PublicContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    /**
     * Below this many published articles, the index stays a plain list.
     *
     * Nine is three rows of three, and it is the point at which a reader
     * genuinely cannot see everything at once. Under it, a category filter is
     * scaffolding for content that does not exist and a dropdown with two
     * entries announces the emptiness far more loudly than the list does.
     *
     * The threshold governs the CONTROLS, not the data: categories and tags
     * exist and have their own pages from the first article. What appears at
     * nine is the filter bar and pagination on this page.
     */
    public const CONTROLS_APPEAR_AT = 9;

    private const PER_PAGE = 9;

    public function __invoke(Request $request): View
    {
        /*
         * Paginated either way, and the THRESHOLD IS READ OFF THE PAGINATOR.
         *
         * An earlier version ran a count() first to decide. That was a second
         * query asking a question the paginator answers for free, on a page
         * whose query budget is asserted.
         */
        $posts = Post::published()
            ->with(['category', 'tags'])
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $paginated = $posts->total() >= self::CONTROLS_APPEAR_AT;

        return view('pages.articles', [
            'posts' => $posts,
            'paginated' => $paginated,
            'categories' => $paginated
                ? Category::query()->active()->withCount(['posts' => fn ($q) => $q->published()])->get()
                : collect(),
            'activeCategory' => null,
            'footerServices' => PublicContent::services(),
        ]);
    }

    /**
     * One category's articles.
     *
     * A real page with its own title and meta description rather than a
     * query string on the index, because this is what somebody searching
     * "تغذية الحمل" should be able to land on.
     */
    public function category(string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('pages.article-taxonomy', [
            'heading' => $category->name,
            'lead' => $category->description,
            'metaDescription' => $category->meta_description ?: $category->description,
            'posts' => $category->posts()->published()->with(['category', 'tags'])->paginate(self::PER_PAGE),
            'crumb' => $category->name,
            'footerServices' => PublicContent::services(),
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();

        return view('pages.article-taxonomy', [
            'heading' => $tag->name,

            /*
             * A tag has no description by design — see the model. The lead is
             * generated rather than authored, because inventing a sentence for
             * every tag is how a tag index fills with empty prose.
             */
            'lead' => __('articles.tag_lead', ['tag' => $tag->name]),
            'metaDescription' => __('articles.tag_lead', ['tag' => $tag->name]),
            'posts' => $tag->posts()->published()->with(['category', 'tags'])->paginate(self::PER_PAGE),
            'crumb' => $tag->name,
            'footerServices' => PublicContent::services(),
        ]);
    }
}
