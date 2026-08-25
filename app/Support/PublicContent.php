<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Faq;
use App\Models\PlateFood;
use App\Models\Post;
use App\Models\Review;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Testimonial;
use App\Models\Video;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The homepage's content, read once and cached.
 *
 * These five sets change monthly at most — a new package, a new article, an
 * FAQ reworded — and are read on every single page view. Caching them turns
 * the homepage from five queries into zero on a warm cache.
 *
 * EXPLICIT KEYS, NOT TAGS. Tagged cache entries need a store that can index
 * them, which means Redis or Memcached; this application runs on the `file`
 * store (see the note in .env.example for the measurements behind that), where
 * Cache::tags() throws. Explicit keys work in every store, and the cost of that
 * choice is that invalidation has to name what it clears — which is what
 * flush() below does, and what the FlushesPublicContentCache trait calls from
 * each model's save and delete events. If the store ever becomes Redis, none of
 * this needs to change; tags would then be available as a simplification.
 *
 * Nothing here is locale-dependent: the models store both languages in one JSON
 * column and pick a locale at render time, so one cache entry serves both the
 * Arabic and English pages. That is the payoff for the JSON-column decision
 * made back in the schema.
 */
final class PublicContent
{
    /**
     * A day is deliberately generous. Every write path already busts these
     * keys, so the TTL is not the invalidation mechanism — it is the backstop
     * for a row changed by something that bypasses Eloquent events, like a
     * manual SQL fix during an incident.
     */
    private const TTL_SECONDS = 86400;

    private const KEY_PREFIX = 'public-content:';

    /**
     * Every key this class owns. flush() clears exactly this list, so a new
     * cached set must be added here or it will quietly go stale forever.
     *
     * @var list<string>
     */
    private const KEYS = [
        'services',
        'specialties',
        'testimonials',
        'faqs',
        'faqs-buying',
        'faqs-all',
        'reviews-approved',
        'latest-posts',
        'opening-hours',
        'videos',
        'plate-foods',
    ];

    /**
     * The video library, featured first.
     *
     * Ordered so the featured video is the one the gallery renders large,
     * without the view having to sort or partition anything itself.
     *
     * @return Collection<int, Video>
     */
    public static function videos(): Collection
    {
        return self::remember('videos', fn (): Collection => Video::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get());
    }

    /**
     * The foods a patient can put on the plate.
     *
     * @return Collection<int, PlateFood>
     */
    public static function plateFoods(): Collection
    {
        return self::remember('plate-foods', fn (): Collection => PlateFood::query()
            ->active()
            ->get());
    }

    /**
     * The bookable packages, cheapest-first by sort_order.
     *
     * @return Collection<int, Service>
     */
    public static function services(): Collection
    {
        return self::remember('services', fn (): Collection => Service::active()->get());
    }

    /**
     * The clinical areas the practice covers. Not bookable — see Specialty.
     *
     * @return Collection<int, Specialty>
     */
    public static function specialties(): Collection
    {
        return self::remember('specialties', fn (): Collection => Specialty::active()->get());
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public static function testimonials(int $limit = 3): Collection
    {
        /** @var Collection<int, Testimonial> $all */
        $all = self::remember('testimonials', fn (): Collection => Testimonial::active()->get());

        return $all->take($limit);
    }

    /**
     * @return Collection<int, Faq>
     */
    public static function faqs(): Collection
    {
        /*
         * GENERAL questions only, which is what the homepage has always shown.
         *
         * The scope is not cosmetic. Before FAQs had categories this returned
         * every active row, so seeding the packages page's buying questions
         * into the same table would have silently added six answers about
         * refunds and payment to a homepage section that is meant to be
         * untouched by that work — and to a visitor who has not yet decided
         * the clinic does what she needs.
         */
        return self::remember(
            'faqs',
            fn (): Collection => Faq::active()->category(Faq::CATEGORY_GENERAL)->get(),
        );
    }

    /**
     * Every active FAQ, grouped by category, for the standalone FAQ page.
     *
     * Grouped here rather than in the view so the page cannot accidentally
     * drop a category by listing them by hand — a new category appears on the
     * page the moment a row uses it.
     *
     * @return \Illuminate\Support\Collection<string, Collection<int, Faq>>
     */
    public static function faqsByCategory(): \Illuminate\Support\Collection
    {
        return self::remember('faqs-all', fn (): Collection => Faq::active()->get())
            ->groupBy('category');
    }

    /**
     * The questions somebody asks with a card in her hand.
     *
     * Its own cache entry rather than a filter over the general set, because
     * the two are read by different pages and neither should pay to load the
     * other's rows.
     *
     * @return Collection<int, Faq>
     */
    public static function buyingFaqs(): Collection
    {
        return self::remember(
            'faqs-buying',
            fn (): Collection => Faq::active()->category(Faq::CATEGORY_BUYING)->get(),
        );
    }

    /**
     * The newest published articles.
     *
     * Cached at a fixed depth rather than per-requested-limit, so a page asking
     * for three and a page asking for two share one entry instead of creating a
     * key each. published() already orders newest-first.
     *
     * @return Collection<int, Post>
     */
    public static function latestPosts(int $limit = 3): Collection
    {
        /** @var Collection<int, Post> $posts */
        $posts = self::remember(
            'latest-posts',
            fn (): Collection => Post::published()->take(6)->get(),
        );

        return $posts->take($limit);
    }

    /**
     * Reviews a patient consented to publish AND the clinic approved.
     *
     * Both conditions live in the model's approved() scope, so no caller can
     * accidentally read a set that satisfies only one of them.
     *
     * @return Collection<int, Review>
     */
    public static function approvedReviews(): Collection
    {
        return self::remember('reviews-approved', fn (): Collection => Review::approved()->get());
    }

    /**
     * The clinic's active schedule, for the JSON-LD opening hours.
     *
     * Cached with the rest because it is read on every page render and changes
     * about as often as the practice moves premises. It is also the one set
     * whose consumer is a machine: nobody looks at the structured data, so a
     * query nobody notices would sit in every request forever.
     *
     * @return Collection<int, WorkingHour>
     */
    public static function openingHours(): Collection
    {
        return self::remember(
            'opening-hours',
            fn (): Collection => WorkingHour::query()
                ->where('is_active', true)
                ->orderBy('day_of_week')
                ->get(),
        );
    }

    /**
     * Drop every cached set.
     *
     * Called from the model events rather than being scheduled: content that
     * changes monthly should appear the moment someone saves it, not on the
     * next TTL boundary. Clearing all five on any one write is deliberate —
     * five key deletes are cheaper than the bookkeeping needed to know which
     * model touched which key, and this runs a handful of times a month.
     */
    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget(self::KEY_PREFIX.$key);
        }
    }

    /**
     * @template TValue
     *
     * @param  \Closure(): TValue  $callback
     * @return TValue
     */
    private static function remember(string $key, \Closure $callback): mixed
    {
        return Cache::remember(self::KEY_PREFIX.$key, self::TTL_SECONDS, $callback);
    }
}
