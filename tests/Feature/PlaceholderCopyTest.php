<?php

declare(strict_types=1);

/**
 * TODO_COPY must never reach production.
 *
 * The practitioner section ships with placeholder text because its content is
 * a set of claims about a real person's qualifications — a degree, a
 * membership, a syndicate registration number. Those are not ours to invent,
 * so the structure was built and the words were left marked.
 *
 * The failure mode this guards against is mundane and completely plausible:
 * the section looks finished in review, everyone reads past the placeholder
 * twice, and a clinic publishes a page reading "TODO_COPY — the doctor's full
 * name" underneath a heading that says About the practitioner.
 *
 * The marker is a single greppable token on purpose. `grep -rn TODO_COPY lang/`
 * is the whole handover.
 */
use App\Console\Commands\VerifyPlaceholderCopy;

it('blocks a production deploy while placeholder copy remains', function () {
    /*
     * The guard that matters. clinic:verify-copy is a deploy gate rather than
     * an always-red test: a suite that is expected to fail teaches people to
     * ignore it, and this only becomes a real problem at the moment of
     * publishing. Same pattern as clinic:verify-key.
     */
    app()->detectEnvironment(fn (): string => 'production');

    expect(app()->isProduction())->toBeTrue();
    expect(VerifyPlaceholderCopy::outstanding())->not->toBeEmpty(
        'The about section still has placeholder copy, so this test should be '
        .'exercising a real blocked deploy. If the clinic has since sent its '
        .'copy, this whole file can go.'
    );

    $this->artisan('clinic:verify-copy')
        ->expectsOutputToContain('DEPLOY BLOCKED')
        ->assertFailed();
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
    // The placeholder is meant to be visible while the site is being built.
    // Failing locally would just train people to pass --no-interaction to
    // everything.
    expect(app()->isProduction())->toBeFalse();

    $this->artisan('clinic:verify-copy')->assertSuccessful();

    $this->artisan('clinic:verify-copy --strict')->assertFailed();
});

it('never renders placeholder copy once the gate is satisfied', function (string $locale) {
    // Belt and braces: if the gate is ever bypassed, this documents exactly
    // what the visitor would have seen.
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    $marker = VerifyPlaceholderCopy::MARKER;

    if (VerifyPlaceholderCopy::outstanding() === []) {
        expect(str_contains($content, $marker))->toBeFalse();
    } else {
        // Still outstanding — assert it is confined to the about section, so a
        // placeholder cannot spread into copy nobody is tracking.
        $about = substr($content, (int) strpos($content, 'id="about"'));
        $about = substr($about, 0, (int) strpos($about, '</section>'));

        expect(substr_count($content, $marker))->toBe(substr_count($about, $marker));
    }
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
