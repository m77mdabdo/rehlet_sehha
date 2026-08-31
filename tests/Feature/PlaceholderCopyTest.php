<?php

declare(strict_types=1);

/**
 * TODO_COPY must never reach production.
 *
 * The practitioner section shipped with placeholder text because its content
 * was a set of claims about a real person — a degree, a syndicate registration
 * number, and her own account of how she works. None of it was ours to invent,
 * so the structure was built and the words were left marked.
 *
 * THE SITE'S OWN COPY IS NOW COMPLETE. That is exactly why this file changed
 * shape: two of these tests used to prove the gate by pointing at the real
 * about page and asserting a marker was there, which worked right up until
 * somebody finished the copy — at which point the tests protecting the gate
 * failed BECAUSE the thing they protect had succeeded.
 *
 * A test that goes red when the project goes right is a test that gets deleted,
 * and the gate would have gone with it. So the gate is now exercised against
 * synthetic translation files in a temporary directory, in both directions,
 * and it stays meaningful for the next placeholder somebody adds.
 *
 * The failure mode it guards against is mundane and completely plausible: a
 * section looks finished in review, everyone reads past the placeholder twice,
 * and a clinic publishes a page reading "TODO_COPY — the doctor's full name"
 * underneath a heading that says About the practitioner.
 *
 * The marker is a single greppable token on purpose. `grep -rn TODO_COPY lang/`
 * is the whole handover.
 */
use App\Console\Commands\VerifyPlaceholderCopy;

/**
 * Point the translation loader at a throwaway directory holding one file.
 *
 * @param  array<string, string>  $strings
 */
function withProbeCopy(array $strings, callable $body): void
{
    $temporary = lang_path('__gate_probe_'.bin2hex(random_bytes(4)));

    mkdir($temporary.'/ar', 0755, true);
    file_put_contents(
        $temporary.'/ar/about.php',
        '<?php return '.var_export($strings, true).';'
    );

    config()->set('app.supported_locales', ['ar']);
    app()->useLangPath($temporary);

    try {
        $body();
    } finally {
        unlink($temporary.'/ar/about.php');
        rmdir($temporary.'/ar');
        rmdir($temporary);
    }
}

it('blocks a production deploy while placeholder copy remains', function () {
    /*
     * The guard that matters. clinic:verify-copy is a deploy gate rather than
     * an always-red test: a suite that is expected to fail teaches people to
     * ignore it, and this only becomes a real problem at the moment of
     * publishing. Same pattern as clinic:verify-key.
     *
     * Exercised against a synthetic placeholder rather than against the real
     * about page. It used to assert the real page still held a marker, which
     * made this test a hostage to the copy being unfinished — see the note at
     * the top of this file.
     */
    app()->detectEnvironment(fn (): string => 'production');

    expect(app()->isProduction())->toBeTrue();

    withProbeCopy(['name' => VerifyPlaceholderCopy::MARKER.' — the practitioner\'s full name'], function (): void {
        expect(VerifyPlaceholderCopy::outstanding())->not->toBeEmpty();

        $this->artisan('clinic:verify-copy')
            ->expectsOutputToContain('DEPLOY BLOCKED')
            ->assertFailed();
    });
});

it('has no placeholder copy left on the site itself', function () {
    /*
     * The state of the real thing, asserted in the direction that stays true.
     *
     * This is what the blocking test above used to be, inverted: the gate is
     * proved against a synthetic placeholder, and the actual site is asserted
     * to be clean. Both facts matter and neither is hostage to the other.
     */
    app()->detectEnvironment(fn (): string => 'production');

    expect(VerifyPlaceholderCopy::outstanding())->toBeEmpty(
        'Placeholder copy is back on the site: '
        .implode(', ', array_keys(VerifyPlaceholderCopy::outstanding()))
    );

    $this->artisan('clinic:verify-copy --strict')
        ->expectsOutputToContain('Safe to publish')
        ->assertSuccessful();
});

it('passes the gate once the placeholders are filled in', function () {
    // Proves the gate can actually go green — a check that only ever fails is
    // indistinguishable from a check that is hard-coded to fail.
    app()->detectEnvironment(fn (): string => 'production');

    $temporary = lang_path('__gate_probe');

    expect(VerifyPlaceholderCopy::MARKER)->toBe('TODO_COPY');

    // Point the locale allow-list at a directory with clean copy in it.
    mkdir($temporary.'/ar', 0755, true);
    file_put_contents($temporary.'/ar/about.php', "<?php return ['name' => 'د. رنا سالم'];");

    config()->set('app.supported_locales', ['ar']);
    app()->useLangPath($temporary);

    try {
        expect(VerifyPlaceholderCopy::outstanding())->toBeEmpty();

        $this->artisan('clinic:verify-copy')
            ->expectsOutputToContain('Safe to publish')
            ->assertSuccessful();
    } finally {
        unlink($temporary.'/ar/about.php');
        rmdir($temporary.'/ar');
        rmdir($temporary);
    }
});

it('only warns outside production', function () {
    // A placeholder is meant to be visible while a section is being built.
    // Failing locally would just train people to pass --no-interaction to
    // everything. --strict is the opt-in that makes it fatal anyway.
    expect(app()->isProduction())->toBeFalse();

    withProbeCopy(['name' => VerifyPlaceholderCopy::MARKER.' — not yet answered'], function (): void {
        $this->artisan('clinic:verify-copy')->assertSuccessful();

        $this->artisan('clinic:verify-copy --strict')->assertFailed();
    });
});

it('never renders placeholder copy once the gate is satisfied', function (string $locale) {
    // Belt and braces: if the gate is ever bypassed, this documents exactly
    // what the visitor would have seen.
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    $marker = VerifyPlaceholderCopy::MARKER;

    expect(str_contains($content, $marker))->toBeFalse(
        'A placeholder marker is being rendered to a visitor on the homepage.'
    );
})->with(['ar', 'en']);

it('keeps the about section rendering even while its copy is a placeholder', function (string $locale) {
    // The structure is real even though the words are not. A section that
    // collapsed without copy could not be reviewed, which is the point of
    // building it now.
    $response = $this->get("/{$locale}")->assertOk();

    $response->assertSee('id="about"', false);
    $response->assertSee(__('about.eyebrow', [], $locale), false);
    $response->assertSee(__('about.credentials_heading', [], $locale), false);
})->with(['ar', 'en']);

it('shows the mark rather than a broken image when there is no portrait', function () {
    $content = $this->get('/ar')->assertOk()->getContent();

    // No <img> in the about section at all — an empty src or a placeholder
    // service URL would render a broken image on a clinic's own page, and a
    // stock photograph would be a claim about who treats you.
    expect($content)->not->toContain('src=""');

    $about = substr($content, (int) strpos($content, 'id="about"'));
    $about = substr($about, 0, (int) strpos($about, '</section>'));

    expect($about)->not->toContain('<img');
    expect($about)->toContain('aria-hidden="true"');
});
