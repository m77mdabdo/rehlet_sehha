<?php

declare(strict_types=1);

/**
 * WHAT THE WEB SERVER IS ASKED TO DO WITH THE FILES WE SHIP.
 *
 * `.htaccess` is the only lever this project has on shared hosting, and it is
 * the one file the PHP test suite otherwise never looks at. Everything checked
 * here was verified against a real Apache before it was written down — the
 * numbers in the comments are measured, not estimated.
 *
 * THIS TEST CANNOT PROVE THE HOST HONOURS ANY OF IT. Apache only applies these
 * directives if the modules are loaded and AllowOverride permits them, and that
 * is a fact about the server rather than about this repository. Section C of
 * docs/deployment/hostinger.md checks it there with curl. What this file
 * protects is that the instructions do not silently disappear from the file.
 */
function htaccess(): string
{
    return (string) file_get_contents(base_path('public/.htaccess'));
}

it('asks for compression on text, and only on text', function () {
    $contents = htaccess();

    /*
     * Measured on this project's own assets through Apache 2.4:
     *   app.css        59,838 -> 10,621 bytes   (82% smaller)
     *   livewire.min   250,972 -> 82,802 bytes  (67% smaller)
     *   a rendered page 179,470 -> 19,369 bytes (89% smaller)
     *
     * The booking page is the one that matters: it carries Livewire's runtime,
     * and it is the page a patient has to reach to book anything.
     */
    foreach (['text/html', 'text/css', 'application/javascript'] as $type) {
        expect(str_contains($contents, $type))
            ->toBeTrue("public/.htaccess no longer compresses {$type}.");
    }

    expect(str_contains($contents, 'mod_deflate.c'))
        ->toBeTrue('The deflate fallback is gone. Brotli alone leaves hosts without it uncompressed.');

    /*
     * And NOT on formats that are already compressed. WebP, MP4 and WOFF2 come
     * out very slightly larger after deflate, having spent CPU to get there —
     * woff2 is Brotli-compressed internally already.
     */
    foreach (['image/webp', 'video/mp4', 'font/woff2'] as $type) {
        expect(preg_match('/(DEFLATE|BROTLI_COMPRESS)[^\n]*'.preg_quote($type, '/').'/', $contents))
            ->toBe(0, "public/.htaccess compresses {$type}, which is already compressed.");
    }
});

it('declares the three types this Apache did not know', function () {
    /*
     * NOT DEFENSIVE PROGRAMMING — MEASURED. On Apache 2.4.56, .webp, .woff2
     * and .mp4 all came back with no Content-Type header whatsoever.
     *
     * The video is the one with teeth: a player that cannot determine the type
     * may refuse the file, and it is the first thing on the homepage. The other
     * two are quieter and worse — every rule in this file that selects by TYPE
     * silently skips a response that has no type, so the caching policy was
     * missing exactly the files it was written for.
     */
    $contents = htaccess();

    foreach (['image/webp .webp', 'font/woff2 .woff2', 'video/mp4 .mp4'] as $declaration) {
        expect(str_contains($contents, 'AddType '.$declaration))
            ->toBeTrue("public/.htaccess no longer declares {$declaration}.");
    }
});

it('caches the hashed build for a year and the unhashed files for a month', function () {
    $contents = htaccess();

    /*
     * A year is safe for /build/ precisely BECAUSE the filenames carry a
     * content hash — app-BLlCntPy.css becomes a different URL when it changes,
     * so a stale copy is unreachable rather than merely unlikely.
     *
     * The photography and brand files carry no hash, so they get a month:
     * long enough to matter to a returning visitor, short enough that replacing
     * a photograph is visible within a sane window rather than never.
     */
    expect(str_contains($contents, 'max-age=31536000, immutable'))
        ->toBeTrue('The hashed build assets are no longer cached for a year.');

    expect(str_contains($contents, 'access plus 1 month'))
        ->toBeTrue('The unhashed images and video are no longer cached at all.');

    /*
     * HTML must NOT be in here. Every page carries live appointment
     * availability and article content, and the application sets its own
     * Cache-Control per response — including no-store on the health route and
     * on every token page.
     */
    expect(preg_match('/ExpiresByType\s+text\/html/', $contents))
        ->toBe(0, 'public/.htaccess is caching HTML. Availability would go stale in a proxy.');
});

it('still denies what the root file was written to deny', function () {
    /*
     * The root .htaccess is a second line of defence for a wrong document
     * root. Adding a performance block to public/.htaccess is exactly the kind
     * of edit that walks over an unrelated file, so the original guarantees
     * are asserted alongside the new ones.
     */
    $root = (string) file_get_contents(base_path('.htaccess'));

    expect(str_contains($root, 'public/$1'))->toBeTrue('The root rewrite into public/ is gone.');
    expect(str_contains($root, '<FilesMatch "^\.">'))->toBeTrue('Dotfiles are no longer denied at the root.');
    expect(preg_match('/\(env\|.*\)\$/', $root))->toBe(1, 'The .env deny rule is gone from the root .htaccess.');
});
