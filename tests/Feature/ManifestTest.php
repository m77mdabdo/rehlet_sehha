<?php

declare(strict_types=1);

/**
 * The manifest is generated from config so the palette has one owner.
 *
 * The reason it is worth testing at all: nothing renders a manifest anywhere a
 * human would notice it was wrong. A stale theme colour or a missing maskable
 * icon shows up only on an installed home-screen app, weeks later, on someone
 * else's phone.
 */
it('serves a manifest for every locale', function (string $locale) {
    $this->get("/{$locale}/site.webmanifest")
        ->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json');
})->with(['ar', 'en']);

it('takes its colours from config, not from a literal', function () {
    config()->set('clinic.brand.ink', '#123456');
    config()->set('clinic.brand.paper', '#ABCDEF');

    $this->get('/ar/site.webmanifest')
        ->assertOk()
        ->assertJsonPath('theme_color', '#123456')
        ->assertJsonPath('background_color', '#ABCDEF');
});

it('lists all three icons with the maskable one marked', function () {
    $icons = $this->get('/ar/site.webmanifest')->assertOk()->json('icons');

    $sources = array_column($icons, 'src');

    expect($sources)->toContain('/brand/icon-192.png')
        ->toContain('/brand/icon-512.png')
        ->toContain('/brand/icon-maskable-512.png');

    $maskable = collect($icons)->firstWhere('src', '/brand/icon-maskable-512.png');

    // Without purpose:maskable, Android pads the icon itself and the mark ends
    // up small inside a white box on every launcher that crops.
    expect($maskable['purpose'])->toBe('maskable');
    expect($maskable['sizes'])->toBe('512x512');
});

it('describes itself in the locale it was requested in', function () {
    $ar = $this->get('/ar/site.webmanifest')->assertOk();
    $ar->assertJsonPath('lang', 'ar')
        ->assertJsonPath('dir', 'rtl')
        ->assertJsonPath('start_url', '/ar');

    $en = $this->get('/en/site.webmanifest')->assertOk();
    $en->assertJsonPath('lang', 'en')
        ->assertJsonPath('dir', 'ltr')
        ->assertJsonPath('start_url', '/en');

    // An English visitor installing the site must not get an Arabic app name.
    expect($en->json('short_name'))->not->toBe($ar->json('short_name'));
});

it('keeps arabic readable rather than escaping it', function () {
    $content = $this->get('/ar/site.webmanifest')->getContent();

    expect($content)->toContain('رحلة صحة');

    // JSON_UNESCAPED_UNICODE. Escaped to \u0631\u062d... the manifest is still
    // valid JSON, but unreadable in DevTools — which is the only place anyone
    // ever actually looks at one.
    expect($content)->not->toMatch('/\\\\u0[6-7][0-9a-fA-F]{2}/');
});

it('scopes the app to the root so a language switch stays inside it', function () {
    // scope "/ar" would eject a visitor into a browser tab the moment they
    // switched to English from an installed app.
    $this->get('/ar/site.webmanifest')->assertJsonPath('scope', '/');
});

it('is cacheable and revalidates with an etag', function () {
    $response = $this->get('/ar/site.webmanifest')->assertOk();

    expect($response->headers->get('Cache-Control'))->toContain('max-age=86400');
    expect($response->headers->get('ETag'))->not->toBeNull();
});

it('404s a manifest for a locale that does not exist', function () {
    // Same allow-list as every other locale-prefixed route.
    $this->get('/fr/site.webmanifest')->assertNotFound();
});

it('no longer ships a static manifest file', function () {
    // The whole point of the route is that the palette has one owner. A
    // leftover file would be served by the web server in preference to
    // nothing, and would quietly go stale.
    expect(file_exists(public_path('brand/site.webmanifest')))->toBeFalse();
    expect(file_exists(public_path('site.webmanifest')))->toBeFalse();
});
