<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Support\Photo;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * WHAT A LINK TO THIS SITE LOOKS LIKE WHEN SOMEBODY SENDS IT.
 *
 * This site's own share button is a wa.me link, and WhatsApp is how almost
 * everything here will actually reach a second person. A page with no og:image
 * arrives as a grey rectangle with a URL underneath, which from a clinic reads
 * as a link the sender was not sure about.
 *
 * THE FAILURE THIS FILE EXISTS FOR IS NOT THE MISSING TAG. It is the tag that
 * points at a file which is not there. WhatsApp and Facebook cache the FAILED
 * fetch, so a broken preview outlives the fix by days and there is nothing on
 * our side to clear. The tags were deliberately absent for most of this build
 * for exactly that reason; now that they are present, the file has to be too,
 * and that is what most of this checks.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
});

it('has a card on disk for every locale, at the size it announces', function (string $locale) {
    $path = public_path("brand/og-{$locale}.png");

    expect(is_file($path))->toBeTrue(
        "brand/og-{$locale}.png is missing, and every page in {$locale} is telling WhatsApp it exists. "
        .'Export it from docs/og-image.html.'
    );

    [$width, $height] = getimagesize($path);

    /*
     * 1200x630 is not decoration. It is the ratio that produces a large card
     * rather than a square thumbnail, and the markup declares those exact
     * numbers — a file of any other size makes the declaration a lie and the
     * card reflow when the real image lands.
     */
    expect($width)->toBe(1200, "brand/og-{$locale}.png is {$width}px wide; the markup says 1200.");
    expect($height)->toBe(630, "brand/og-{$locale}.png is {$height}px tall; the markup says 630.");

    // PNG or JPEG. WebP previews are not reliably rendered by every consumer,
    // and this is the one image on the site whose consumer is not a browser.
    expect(pathinfo($path, PATHINFO_EXTENSION))->toBeIn(['png', 'jpg', 'jpeg']);
})->with(['ar', 'en']);

it('advertises an absolute image on every public page', function (string $locale) {
    /*
     * ABSOLUTE. A relative og:image is ignored by every consumer that matters,
     * silently, and the page looks correct in every browser while previewing
     * as nothing.
     */
    foreach (['', '/services', '/packages', '/about', '/articles', '/faq', '/contact', '/booking'] as $path) {
        $html = $this->get("/{$locale}{$path}")->assertOk()->getContent();

        preg_match('~<meta property="og:image" content="([^"]*)"~', $html, $match);

        expect($match)->not->toBeEmpty("/{$locale}{$path} has no og:image.");
        /*
         * str_starts_with / str_contains rather than toStartWith / toContain.
         *
         * Pest reads a second argument to toContain() as ANOTHER NEEDLE, not
         * as a failure message — so the obvious spelling asserts that the URL
         * contains the message text as well, and then fails saying so. And
         * toStartWith() takes one argument and silently discards a second, so
         * the message never appears at all.
         *
         * ExpectationMessagesTest scans the suite for both shapes.
         */
        expect(str_starts_with($match[1], 'http'))
            ->toBeTrue("/{$locale}{$path} has a relative og:image: {$match[1]}");

        expect(str_contains($match[1], "og-{$locale}.png"))
            ->toBeTrue("/{$locale}{$path} advertises the wrong locale's card: {$match[1]}");
    }
})->with(['ar', 'en']);

it('sends the locale that matches the page', function () {
    /*
     * An Arabic page previewing with the English card is the kind of thing
     * nobody notices until a patient forwards it to her mother.
     */
    expect($this->get('/ar')->getContent())->toContain('og-ar.png');
    expect($this->get('/en')->getContent())->toContain('og-en.png');
});

it('asks for a large card, since the image is a large card', function () {
    /*
     * summary with a 1.91:1 image gets centre-cropped to a square thumbnail,
     * which on this design throws away the mark and half the words.
     */
    $html = $this->get('/ar')->assertOk()->getContent();

    expect($html)->toContain('name="twitter:card" content="summary_large_image"');
    expect($html)->toContain('property="og:image:width" content="1200"');
    expect($html)->toContain('property="og:image:height" content="630"');
});

it('previews an article as its own cover, at the cover\'s real size', function () {
    /*
     * Fourteen articles all previewing as the same brand card is fourteen
     * identical rectangles in a thread.
     *
     * The dimensions matter as much as the image: announcing 1200x630 for a
     * file that is not 1200x630 makes consumers lay the card out from the
     * declared ratio and reflow when the real one arrives, which is the
     * visible version of this bug.
     */
    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();

    $post = Post::factory()->create([
        'slug' => 'a-published-article',
        'category_id' => Category::query()->firstOrFail()->id,
        'cover_path' => 'kitchen-hands-herbs',
        'reviewed_by' => $doctor->id,
        'reviewed_at' => now()->subDay(),
        'published_at' => now()->subDay(),
    ]);

    $html = $this->get(route('posts.show', ['locale' => 'ar', 'slug' => $post->slug]))
        ->assertOk()
        ->getContent();

    $variant = Photo::largest('kitchen-hands-herbs');
    $size = Photo::get('kitchen-hands-herbs')['variants'][$variant];

    expect($html)->toContain(Photo::url('kitchen-hands-herbs', $variant));
    expect($html)->not->toContain('og-ar.png');
    expect($html)->toContain('property="og:image:width" content="'.$size['width'].'"');
    expect($html)->toContain('property="og:image:height" content="'.$size['height'].'"');
});

it('never advertises a token page', function () {
    /*
     * The rule the whole preview block sits under. og:url, canonical and
     * hreflang all echo the current URL, and on the appointment page that URL
     * IS the credential — publishing it to a link-preview fetcher hands a
     * working cancellation link to whatever renders the chat.
     *
     * Asserted here as well as in TokenUrlHygieneTest because adding og:image
     * meant touching this block, and this is the property that must not have
     * been loosened while it was open.
     */
    $appointment = Appointment::factory()->create();

    $html = $this->get(route('appointment.manage', [
        'locale' => 'ar',
        'token' => $appointment->cancel_token,
    ]))->assertOk()->getContent();

    expect(str_contains($html, 'og:url'))->toBeFalse('A token page publishes its own URL to link previews.');
    expect(str_contains($html, $appointment->cancel_token))
        ->toBeTrue('Sanity check: the token should be in the page body, just not in a meta tag.');

    preg_match_all('~<meta[^>]*>~', $html, $metas);

    foreach ($metas[0] as $meta) {
        expect(str_contains($meta, $appointment->cancel_token))
            ->toBeFalse("A meta tag carries the appointment token: {$meta}");
    }
});

it('describes the card to somebody who cannot see it', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('~<meta property="og:image:alt" content="([^"]*)"~', $html, $match);

    expect($match)->not->toBeEmpty("/{$locale} has no og:image:alt.");
    expect(mb_strlen(html_entity_decode($match[1])))->toBeGreaterThan(20);
})->with(['ar', 'en']);
