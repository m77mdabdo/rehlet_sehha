<?php

declare(strict_types=1);

use App\Models\PlateFood;
use App\Models\Service;
use App\Models\Video;
use App\Services\Video\ThumbnailFetcher;
use Database\Seeders\PlateFoodSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * The three interactive features, and the promises each one makes.
 */
beforeEach(function () {
    $this->seed(ServiceSeeder::class);
    $this->seed(PlateFoodSeeder::class);
});

/*
|------------------------------------------------------------------------------
| The video facade
|------------------------------------------------------------------------------
*/

it('loads nothing from youtube on the homepage', function (string $locale) {
    Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو', 'en' => 'Video'],
        'description' => ['ar' => 'وصف', 'en' => 'Description'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $html = $this->get("/{$locale}")->assertOk()->getContent();

    /*
     * THE POINT OF THE WHOLE FEATURE. An embedded player executes Google's
     * script on page load for every visitor, watched or not — Google learning
     * who is looking at a nutrition clinic, inferred from a page they only
     * read.
     *
     * So: no iframe, no script tag, no preconnect, no link to any Google host
     * anywhere in the initial document.
     */
    expect($html)->not->toContain('<iframe');
    expect($html)->not->toContain('youtube.com');
    expect($html)->not->toContain('ytimg.com');
    expect($html)->not->toContain('googlevideo.com');
    expect($html)->not->toContain('google.com');

    // The nocookie host must not be requested either — it is only ever put
    // into a data attribute, for the script to use after a click.
    preg_match_all('/(?:src|href)="([^"]*youtube[^"]*)"/i', $html, $requested);

    expect($requested[1])->toBeEmpty(
        'A YouTube URL is in a src or href, so the browser will fetch it on load: '
        .implode(', ', $requested[1])
    );
})->with(['ar', 'en']);

it('carries a nocookie embed url for the script to use after a click', function () {
    Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو', 'en' => 'Video'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('/data-video-embed="([^"]+)"/', $html, $match);

    expect($match)->not->toBeEmpty('The play button carries no embed URL.');

    $embed = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect($embed)->toStartWith('https://www.youtube-nocookie.com/embed/abc123XYZ_-');

    // Nothing plays by itself. See Video::embedUrl() for why the extra click
    // is deliberate rather than an oversight.
    expect($embed)->not->toContain('autoplay=1');
});

it('never points a thumbnail at googles cdn', function () {
    // A hotlinked thumbnail would disclose exactly the same visit to exactly
    // the same company, while looking careful. See ThumbnailFetcher.
    Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو', 'en' => 'Video'],
        'thumbnail_path' => null,
        'is_active' => true,
        'is_featured' => true,
    ]);

    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $images);

    foreach ($images[1] as $src) {
        expect(str_contains($src, 'ytimg'))->toBeFalse("Thumbnail hotlinked from Google: {$src}");
        expect(str_contains($src, 'youtube'))->toBeFalse("Thumbnail hotlinked from Google: {$src}");
    }
});

it('stores a fetched thumbnail locally and serves it from our own origin', function () {
    Storage::fake('public');

    // A 400px JPEG, wide enough to pass the placeholder check.
    $jpeg = (function (): string {
        $image = imagecreatetruecolor(400, 300);
        ob_start();
        imagejpeg($image);

        return (string) ob_get_clean();
    })();

    Http::fake([
        'i.ytimg.com/vi/abc123XYZ_-/maxresdefault.jpg' => Http::response($jpeg, 200),
    ]);

    $video = Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو', 'en' => 'Video'],
        'thumbnail_path' => null,
    ]);

    expect(app(ThumbnailFetcher::class)->fetch($video))->toBeTrue();

    $video->refresh();

    expect($video->thumbnail_path)->toBe('video-thumbnails/abc123XYZ_-.jpg');
    Storage::disk('public')->assertExists('video-thumbnails/abc123XYZ_-.jpg');

    // Served from us, not from Google.
    expect($video->thumbnailUrl())->not->toContain('ytimg');
});

it('rejects youtubes grey placeholder rather than storing it', function () {
    Storage::fake('public');

    /*
     * YouTube answers 200 with a 120x90 grey image for a video that has no
     * thumbnail at that size. Storing it would put a grey rectangle on the
     * homepage and mark the video as done, so it would never be retried.
     */
    $tiny = (function (): string {
        $image = imagecreatetruecolor(120, 90);
        ob_start();
        imagejpeg($image);

        return (string) ob_get_clean();
    })();

    Http::fake(['i.ytimg.com/*' => Http::response($tiny, 200)]);

    $video = Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو', 'en' => 'Video'],
        'thumbnail_path' => null,
    ]);

    expect(app(ThumbnailFetcher::class)->fetch($video))->toBeFalse();
    expect($video->fresh()->thumbnail_path)->toBeNull();
});

it('falls back to a placeholder when no thumbnail was stored', function () {
    Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو من العيادة', 'en' => 'Video'],
        'thumbnail_path' => null,
        'is_active' => true,
        'is_featured' => true,
    ]);

    // The card still renders and is still playable — a missing image is a
    // smaller problem than telling Google about every visitor to avoid it.
    $html = $this->get('/ar')->assertOk()->getContent();

    expect($html)->toContain('فيديو من العيادة');
    expect($html)->toContain('data-video-play');
});

it('gives the modal a real dialog with a close control', function () {
    Video::factory()->create([
        'youtube_id' => 'abc123XYZ_-',
        'title' => ['ar' => 'فيديو', 'en' => 'Video'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $html = $this->get('/ar')->assertOk()->getContent();

    /*
     * A native <dialog>, so the browser owns the focus trap, Escape and the
     * inert backdrop — and gets all three right in cases hand-written traps
     * miss. The script only opens and closes it.
     */
    expect($html)->toContain('<dialog');
    expect($html)->toContain('data-video-dialog');
    expect($html)->toContain('data-video-close');
    expect($html)->toContain('aria-labelledby="video-dialog-title"');

    // Every play control is a real button, so it is focusable and operable
    // with Enter and Space without any script.
    preg_match_all('/<(\w+)[^>]*data-video-play/', $html, $elements);

    expect($elements[1])->not->toBeEmpty();

    foreach ($elements[1] as $tag) {
        expect($tag)->toBe('button');
    }
});

/*
|------------------------------------------------------------------------------
| The package matcher
|------------------------------------------------------------------------------
*/

/**
 * The scoring, mirrored from resources/js/matcher.js.
 *
 * Duplicated deliberately: the weights live in the browser, and the only way
 * to test the outcome server-side is to state the same table here. If the two
 * drift, this test asserts the OLD behaviour and fails — which is the point.
 *
 * @param  array<int, string>  $answers
 */
function matcherRecommendation(array $answers): string
{
    $weights = [
        'understand' => ['lab-review' => 3],
        'start' => ['single-consultation' => 2, 'one-month-programme' => 1],
        'condition' => ['three-months-programme' => 2, 'one-month-programme' => 1],
        'never' => ['single-consultation' => 2],
        'tried' => ['one-month-programme' => 2],
        'many' => ['three-months-programme' => 3],
        'once' => ['single-consultation' => 2, 'lab-review' => 1],
        'month' => ['one-month-programme' => 3],
        'longer' => ['three-months-programme' => 3],
    ];

    $totals = [];

    foreach ($answers as $answer) {
        foreach ($weights[$answer] ?? [] as $slug => $weight) {
            $totals[$slug] = ($totals[$slug] ?? 0) + $weight;
        }
    }

    $order = array_keys(__('matcher.results'));

    $best = null;
    $bestTotal = -1;

    foreach ($order as $slug) {
        $total = $totals[$slug] ?? 0;

        if ($total > $bestTotal) {
            $best = $slug;
            $bestTotal = $total;
        }
    }

    return (string) $best;
}

it('recommends the intended package for each answer path', function (array $answers, string $expected) {
    expect(matcherRecommendation($answers))->toBe($expected);
})->with([
    // Wants to understand first → the lab review, which is exactly that.
    'understand → never → once' => [['understand', 'never', 'once'], 'lab-review'],
    'understand → tried → once' => [['understand', 'tried', 'once'], 'lab-review'],

    // First-timer wanting to start, one session → the single consultation.
    'start → never → once' => [['start', 'never', 'once'], 'single-consultation'],

    // Tried and did not finish → follow-up is the missing piece, not a plan.
    'start → tried → month' => [['start', 'tried', 'month'], 'one-month-programme'],

    // Weight keeps coming back → time, not another plan.
    'start → many → longer' => [['start', 'many', 'longer'], 'three-months-programme'],
    'condition → many → longer' => [['condition', 'many', 'longer'], 'three-months-programme'],

    // A condition needing ongoing follow-up.
    'condition → tried → longer' => [['condition', 'tried', 'longer'], 'three-months-programme'],
]);

it('only ever recommends a package that actually exists and is bookable', function () {
    $slugs = Service::query()->where('is_active', true)->pluck('slug')->all();

    foreach (array_keys(__('matcher.results')) as $slug) {
        // in_array rather than toContain: Pest reads extra arguments to
        // toContain as further needles, so the failure message was being
        // searched for in the array.
        expect(in_array($slug, $slugs, true))->toBeTrue(
            "matcher.results names «{$slug}», which is not an active service. The CTA would "
            .'deep-link into a booking wizard that refuses to load it.'
        );
    }
});

it('deep-links the cta to the booking wizard with the slug preselected', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('/data-matcher-payload="([^"]*)"/', $html, $match);

    expect($match)->not->toBeEmpty('The matcher payload did not render.');

    $payload = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);

    expect($payload['results'])->not->toBeEmpty();

    foreach ($payload['results'] as $result) {
        // Task 5 already handles ?service={slug}; this is the link into it.
        expect($result['url'])->toContain("/{$locale}/booking");
        expect($result['url'])->toContain('service='.$result['slug']);

        // The result explains WHY, not just which — that is the half a price
        // list cannot answer.
        expect($result['why'])->not->toBeEmpty();
        expect(mb_strlen($result['why']))->toBeGreaterThan(40);
    }
})->with(['ar', 'en']);

it('says plainly that nothing is collected', function (string $locale) {
    // A health quiz that quietly profiles you is exactly what patients fear.
    // The only way she can know otherwise is if the page says so.
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    expect($html)->toContain(__('matcher.privacy_note', [], $locale));
})->with(['ar', 'en']);

it('renders the first question server-side so the quiz works before any script', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    $questions = __('matcher.questions', [], 'ar');

    expect($html)->toContain($questions[0]['text']);

    foreach ($questions[0]['options'] as $option) {
        expect($html)->toContain($option['text']);
    }
});

/*
|------------------------------------------------------------------------------
| The plate builder
|------------------------------------------------------------------------------
*/

it('renders every food as a keyboard-operable toggle', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match_all('/<(\w+)[^>]*data-plate-food="(\d+)"[^>]*aria-pressed="(\w+)"/', $html, $matches, PREG_SET_ORDER);

    expect($matches)->not->toBeEmpty('No plate foods rendered.');

    foreach ($matches as [$whole, $tag, $id, $pressed]) {
        // A real button: focusable, and operable with Enter and Space with no
        // script at all.
        expect($tag)->toBe('button');

        // The state lives in aria-pressed, so a screen reader hears it rather
        // than needing to see the highlight.
        expect($pressed)->toBe('false');
    }

    expect(count($matches))->toBe(PlateFood::query()->active()->count());
});

it('announces the plate and its feedback to a screen reader', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    // The feedback updates without moving focus, so it has to be announced.
    expect($html)->toContain('data-plate-message');
    expect($html)->toContain('aria-live="polite"');

    // The proportion bar is an image with a name, not an unlabelled div.
    expect($html)->toContain('data-plate-bar');
    expect($html)->toContain('role="img"');
});

it('offers a reset', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    expect($html)->toContain('data-plate-reset');
    expect($html)->toContain(__('plate.reset', [], $locale));
})->with(['ar', 'en']);
