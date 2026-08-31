<?php

declare(strict_types=1);

use App\Models\Citation;
use App\Models\Post;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * THE TWO DOCUMENTS SHE WORKS FROM MUST DESCRIBE THE ARTICLES THAT EXIST.
 *
 * Both are generated, and a generated file committed to git has exactly one
 * failure mode: somebody edits the source and forgets to regenerate. It fails
 * silently and in the worst direction — a prompt list missing the three
 * questions added last week looks complete, so the questions are never asked,
 * and an article sits unpublishable for a month while everybody believes it is
 * waiting on something else.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
    $this->seed(PostSeeder::class);
});

it('has both review documents committed and current', function () {
    $this->artisan('clinic:export-review-docs --check')->assertSuccessful();
});

it('asks every question in the prompt list', function () {
    /*
     * Independent of the generator: counted from the article bodies, compared
     * against the committed file. If the exporter itself ever silently drops a
     * category of prompt, --check would still pass — it compares the file to
     * the generator, not the generator to reality.
     */
    $document = file_get_contents(base_path('docs/content/clinical-prompts.md'));

    expect($document)->not->toBeFalse();

    foreach (Post::all() as $post) {
        foreach ([Post::CLINICAL_MARKER, Post::PRACTITIONER_MARKER] as $marker) {
            $body = (string) $post->getTranslation('body', 'ar', false);

            preg_match_all('/^'.$marker.':\s*(.+)$/mu', $body, $matches);

            foreach ($matches[1] as $question) {
                expect(str_contains((string) $document, trim($question)))
                    ->toBeTrue("«{$post->slug}» asks a question that is not in clinical-prompts.md:\n  ".trim($question));
            }
        }
    }
});

it('lists every citation with the claim it supports', function () {
    $document = (string) file_get_contents(base_path('docs/content/citations-to-verify.md'));

    foreach (Citation::query()->with('post')->get() as $citation) {
        $organisation = (string) $citation->getTranslation('organisation', 'en', false);
        $note = (string) $citation->note;

        expect(str_contains($document, $organisation))
            ->toBeTrue("citations-to-verify.md is missing «{$organisation}» from {$citation->post?->slug}.");

        /*
         * The note matters more than the name. "Does this document exist" is
         * the easy half of verification; "does it say what we said it says" is
         * the half that catches a real error, and it cannot be answered
         * without the claim in front of you.
         */
        expect(str_contains($document, $note))
            ->toBeTrue("citations-to-verify.md does not say what «{$organisation}» is being cited for.");
    }
});

it('never puts a confidence rating in front of a reader', function () {
    /*
     * These documents are internal and say so, but they live in a public
     * repository, and the failure worth guarding is the other direction: the
     * confidence field appearing in anything the SITE renders. Checked here
     * because this is the file that thinks about the field at all.
     */
    foreach (['ar', 'en'] as $locale) {
        $html = $this->get("/{$locale}/articles")->assertOk()->getContent();

        foreach (['Needs confirming', 'محتاج تأكيد', 'مايتنشرش', 'Not publishable'] as $label) {
            expect(str_contains($html, $label))->toBeFalse("The articles index shows «{$label}».");
        }
    }
});

it('tells the reader of the citation list what to do when a source cannot be found', function () {
    /*
     * The one instruction that decides whether this whole exercise was worth
     * anything. Somebody who cannot find a reference at 5pm on a Thursday will
     * take the path the document offers her — and "soften the sentence, keep
     * the reference" is the outcome that leaves a claim on the site with a
     * citation that does not support it.
     */
    $document = (string) file_get_contents(base_path('docs/content/citations-to-verify.md'));

    expect($document)->toContain('Delete the citation');
    expect($document)->toContain('the sentence it supports');
    expect($document)->toContain('makes things worse');
});
