<?php

declare(strict_types=1);

use App\Support\Locales;

/**
 * The locale lives in the URL, and the URL is user input.
 *
 * Two things are being protected here. The first is SEO: one canonical URL per
 * page per language, with hreflang tying them together, so Google can rank the
 * Arabic and English pages separately instead of picking one and discarding the
 * other. The second is the allow-list — Laravel resolves translation files by
 * locale name, so an unvalidated segment is a filesystem lookup driven by the
 * path, and that has to be closed by construction rather than by convention.
 */
it('redirects the bare root to the default locale', function () {
    $this->get('/')
        ->assertRedirect('/'.Locales::DEFAULT);
});

it('serves both supported locales', function (string $locale) {
    $this->get("/{$locale}")->assertOk();
})->with(['ar', 'en']);

it('rejects a locale that is not on the allow-list', function (string $segment) {
    $this->get('/'.$segment)->assertNotFound();
})->with([
    'unsupported language' => 'fr',
    'uppercase' => 'AR',
    'regional variant' => 'ar-EG',
    'traversal attempt' => '..%2F..%2Fconfig',
    'empty-ish' => '-',
]);

it('sets the document language and direction from the locale', function () {
    $this->get('/ar')
        ->assertSee('<html lang="ar" dir="rtl"', false);

    $this->get('/en')
        ->assertSee('<html lang="en" dir="ltr"', false);
});

it('publishes hreflang alternates for every locale plus x-default', function () {
    $response = $this->get('/ar');

    expect(Locales::all())->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (Locales::all() as $locale) {
        $response->assertSee('hreflang="'.$locale.'"', false);
    }

    $response->assertSee('hreflang="x-default"', false);
});

it('makes the locale implicit in generated urls', function () {
    // URL::defaults() is what lets every route() call in a Blade file omit the
    // {locale} parameter. If it were not applied, route('home') would throw a
    // missing-parameter error rather than quietly producing a wrong URL — so
    // this asserts the generated path, not merely that the page rendered.
    $this->get('/en')
        ->assertOk()
        ->assertSee('href="'.url('/en').'"', false);
});

it('offers the language switcher the same page in the other locale', function () {
    // The classic bilingual bug is a switcher that goes home. There is only one
    // route so far, so this asserts the mechanism directly: a request on a
    // parameterised path must keep its parameters when the locale swaps.
    expect(Locales::swapLocaleInPath('/ar/services/keto-plan?ref=fb', 'en'))
        ->toBe('/en/services/keto-plan?ref=fb');

    expect(Locales::swapLocaleInPath('/en/articles/page/2', 'ar'))
        ->toBe('/ar/articles/page/2');

    // A path with no locale segment at all gains one rather than losing its way.
    expect(Locales::swapLocaleInPath('/articles', 'ar'))->toBe('/ar/articles');
});

it('renders no untranslated keys on the home page', function (string $locale) {
    $response = $this->get("/{$locale}")->assertOk();

    // A missing translation renders as its own dotted name. Catch it here as
    // well as in the parity test: parity proves the keys line up, this proves
    // the page is actually reading them.
    expect($response->getContent())
        ->not->toMatch('/>\s*(nav|common|home|footer|booking)\.[a-z_.]+\s*</');
})->with(['ar', 'en']);
