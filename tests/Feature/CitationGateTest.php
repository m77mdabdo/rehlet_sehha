<?php

declare(strict_types=1);

use App\Enums\CitationConfidence;
use App\Models\Category;
use App\Models\Citation;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * THE THIRD GATE.
 *
 * These articles were drafted without internet access, from memory, and they
 * report what the ADA, WHO, NICE, ESHRE, AAP and Cochrane say. That is a
 * legitimate way to draft. It is an indefensible way to publish: a fabricated
 * reference in a medical article under a licensed practitioner's name is worse
 * than no article at all, because it borrows an institution's authority to
 * make a claim that institution never made.
 *
 * "Somebody will check them before we go live" is not a control. This file is
 * the test for the thing that is.
 *
 * TWO SEPARATE FAILURES, TESTED SEPARATELY, because the remedies differ:
 *   - LOW confidence means the draft was not sure the source exists. The fix
 *     is to delete it and the sentence it supports.
 *   - UNVERIFIED means nobody has opened the document yet. The fix is to open
 *     it.
 *
 * And FOUR ORDERS OF OPERATIONS, because a gate that only fires on one of them
 * is a gate with a door beside it.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
});

function citedArticle(array $attributes = []): Post
{
    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();

    return Post::factory()->create(array_merge([
        'category_id' => Category::query()->firstOrFail()->id,
        'reviewed_by' => $doctor->id,
        'reviewed_at' => now()->subDay(),
        'published_at' => null,
    ], $attributes));
}

/**
 * @param  array<string, mixed>  $attributes
 */
function attachCitation(Post $post, array $attributes = []): Citation
{
    return Citation::create(array_merge([
        'post_id' => $post->id,
        'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
        'title' => ['ar' => 'صحيفة وقائع', 'en' => 'Fact sheet'],
        'year' => 2020,
        'confidence' => CitationConfidence::High,
        'note' => 'Supports a claim in the article.',
    ], $attributes));
}

function verifierId(): int
{
    return (int) User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail()->id;
}

/*
|------------------------------------------------------------------------------
| Publishing on an unchecked reference
|------------------------------------------------------------------------------
*/

it('refuses to publish an article carrying a citation nobody has verified', function () {
    $post = citedArticle();
    attachCitation($post);

    $post->published_at = now()->subHour();

    expect(fn () => $post->save())
        ->toThrow(LogicException::class, 'citations nobody has checked');
});

it('publishes once every citation has been verified', function () {
    $post = citedArticle();
    $citation = attachCitation($post);

    $citation->update(['verified_by' => verifierId(), 'verified_at' => now()]);

    $post->published_at = now()->subHour();
    $post->save();

    expect($post->fresh()->published_at)->not->toBeNull();
    expect(Post::published()->whereKey($post->id)->exists())->toBeTrue();
});

it('refuses when only some of the citations are verified', function () {
    /*
     * The realistic failure. Somebody checks four references, gets called
     * away, and comes back a week later certain the article is done.
     */
    $post = citedArticle();

    attachCitation($post)->update(['verified_by' => verifierId(), 'verified_at' => now()]);
    attachCitation($post, ['organisation' => ['ar' => 'NICE', 'en' => 'NICE']]);

    $post->published_at = now()->subHour();

    expect(fn () => $post->save())->toThrow(LogicException::class);
});

/*
|------------------------------------------------------------------------------
| Publishing on a reference the draft was not sure exists
|------------------------------------------------------------------------------
*/

it('refuses to publish an article carrying a low-confidence citation, even a verified one', function () {
    /*
     * VERIFIED IS NOT ENOUGH HERE, and that is the point of testing it
     * separately. LOW means the draft could not be confident the document
     * exists at all — it was never eligible to be checked, and somebody
     * ticking it off does not make it eligible. It should be deleted, along
     * with the sentence it was propping up.
     */
    $post = citedArticle();

    attachCitation($post, [
        'confidence' => CitationConfidence::Low,
        'verified_by' => verifierId(),
        'verified_at' => now(),
    ]);

    $post->published_at = now()->subHour();

    expect(fn () => $post->save())->toThrow(LogicException::class, 'was not sure exists');
});

/*
|------------------------------------------------------------------------------
| The other order of operations
|------------------------------------------------------------------------------
*/

it('refuses to attach an unverified citation to an already published article', function () {
    /*
     * The hole a save-time-only gate would leave wide open: publish a clean
     * article on Monday, attach an unchecked reference on Tuesday, and the
     * article never saves again — so nothing ever re-checks it, and the
     * reference appears on a live page having passed nothing.
     */
    $post = citedArticle();
    attachCitation($post)->update(['verified_by' => verifierId(), 'verified_at' => now()]);

    $post->published_at = now()->subHour();
    $post->save();

    expect(fn () => attachCitation($post->fresh(), ['organisation' => ['ar' => 'ADA', 'en' => 'ADA']]))
        ->toThrow(LogicException::class, 'must be verified first');
});

it('refuses to downgrade a citation on a published article to low confidence', function () {
    $post = citedArticle();
    $citation = attachCitation($post);
    $citation->update(['verified_by' => verifierId(), 'verified_at' => now()]);

    $post->published_at = now()->subHour();
    $post->save();

    expect(fn () => $citation->fresh()->update(['confidence' => CitationConfidence::Low]))
        ->toThrow(LogicException::class);
});

it('still allows a draft to carry anything at all', function () {
    /*
     * Drafting is unrestricted. The gate is on PUBLISHING, exactly as the
     * clinical-prompt gate is — otherwise the only way to record an uncertain
     * reference would be to leave it out, and leaving it out is how it stops
     * being checked.
     */
    $post = citedArticle(['published_at' => null]);

    attachCitation($post, ['confidence' => CitationConfidence::Low]);
    attachCitation($post, ['confidence' => CitationConfidence::Medium]);

    expect($post->citations()->count())->toBe(2);
    expect(fn () => $post->save())->not->toThrow(LogicException::class);
});

/*
|------------------------------------------------------------------------------
| The read side
|------------------------------------------------------------------------------
*/

it('refuses to serve a row that reached the table another way', function () {
    /*
     * The write hook governs writes. This governs reads. A raw insert, a
     * restored backup taken before this rule existed, or a migration that
     * copied rows would all bypass the model entirely — and the site still
     * must not serve the result.
     */
    $post = citedArticle();
    attachCitation($post)->update(['verified_by' => verifierId(), 'verified_at' => now()]);

    $post->published_at = now()->subHour();
    $post->save();

    expect(Post::published()->whereKey($post->id)->exists())->toBeTrue();

    // Straight past the model.
    DB::table('citations')->where('post_id', $post->id)->update(['verified_at' => null]);

    expect(Post::published()->whereKey($post->id)->exists())
        ->toBeFalse('An article with an unverified reference is being served.');

    $this->get(route('posts.show', ['locale' => 'ar', 'slug' => $post->slug]))->assertNotFound();
});

/*
|------------------------------------------------------------------------------
| What a reader sees, and what she does not
|------------------------------------------------------------------------------
*/

it('shows the references and never the confidence', function (string $locale) {
    $post = citedArticle();

    attachCitation($post, [
        'organisation' => ['ar' => 'الجمعية الأمريكية للسكري', 'en' => 'American Diabetes Association'],
        'title' => ['ar' => 'معايير الرعاية في السكري', 'en' => 'Standards of Care in Diabetes'],
        'year' => 2025,
        'confidence' => CitationConfidence::Medium,
        'note' => 'Internal note that must never be rendered.',
    ])->update(['verified_by' => verifierId(), 'verified_at' => now()]);

    $post->published_at = now()->subHour();
    $post->save();

    $html = $this->get(route('posts.show', ['locale' => $locale, 'slug' => $post->slug]))
        ->assertOk()
        ->getContent();

    expect($html)->toContain(__('articles.references_heading', [], $locale));
    expect($html)->toContain($locale === 'ar' ? 'الجمعية الأمريكية للسكري' : 'American Diabetes Association');
    expect($html)->toContain('2025');

    /*
     * The confidence describes how the DRAFT was written and is for the person
     * doing the verification. A reader seeing "needs confirming" beside a
     * reference would reasonably conclude the clinic is unsure of its own
     * medicine. The note is internal for the same reason.
     */
    foreach (['Needs confirming', 'محتاج تأكيد', 'Internal note that must never be rendered'] as $leak) {
        expect(str_contains($html, $leak))->toBeFalse("The article page leaks «{$leak}» to a reader.");
    }

    /*
     * And the raw enum value, checked INSIDE THE REFERENCES SECTION rather
     * than across the whole page. "medium" appears in Tailwind class names
     * (font-medium) and in image `sizes` attributes on every page on the site,
     * so a document-wide search for it fails on markup that has nothing to do
     * with citations — which is a test that fails for the wrong reason, and
     * would eventually be deleted for being noisy.
     */
    preg_match('/<section[^>]*aria-labelledby="references-heading".*?<\/section>/su', $html, $match);

    expect($match)->not->toBeEmpty('The references section did not render.');

    foreach (CitationConfidence::cases() as $confidence) {
        expect(str_contains($match[0], $confidence->value))
            ->toBeFalse("The references section carries the «{$confidence->value}» confidence value.");
    }
})->with(['ar', 'en']);

it('formats a reference as organisation, title and year and nothing else', function () {
    /*
     * NO DOI, NO VOLUME, NO PAGE NUMBERS. Those are the fields a draft written
     * from memory invents most convincingly — a fabricated DOI looks more like
     * evidence of checking than anything else on the page. A named body, a
     * title and a year identify a guideline unambiguously and can be checked
     * with a search engine.
     */
    $post = citedArticle();
    $citation = attachCitation($post, [
        'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
        'title' => ['ar' => 'صحيفة وقائع: فقر الدم', 'en' => 'Fact sheet: Anaemia'],
        'year' => 2023,
    ]);

    expect($citation->reference('en'))->toBe('World Health Organization. Fact sheet: Anaemia. 2023.');

    // A source with no fixed year — a periodically revised fact sheet — omits
    // it rather than inventing one.
    $undated = attachCitation($post, [
        'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
        'title' => ['ar' => 'صحيفة وقائع', 'en' => 'Fact sheet'],
        'year' => null,
    ]);

    expect($undated->reference('en'))->toBe('World Health Organization. Fact sheet.');
});

/*
|------------------------------------------------------------------------------
| The seeded set
|------------------------------------------------------------------------------
*/

it('seeds no citation the draft was not confident about', function () {
    /*
     * LOW confidence means "should never have been written down". The gate
     * would catch one at publish time, but by then somebody has spent an
     * afternoon verifying references around it. None should exist at all.
     */
    $this->seed(PostSeeder::class);

    $low = Citation::query()->where('confidence', CitationConfidence::Low->value)->get();

    expect($low)->toBeEmpty(
        'The seeder wrote '.$low->count().' low-confidence citation(s). '
        .'A source the draft was not sure exists should have been left out, not recorded.'
    );
});

it('gives every seeded citation a note saying what it supports', function () {
    /*
     * The note is what makes verification possible. "Is this a real document"
     * is only half the question; "does it say what we said it says" is the
     * half that matters, and it cannot be answered without knowing which
     * sentence the reference is holding up.
     */
    $this->seed(PostSeeder::class);

    foreach (Citation::query()->with('post')->get() as $citation) {
        expect(trim((string) $citation->note))->not->toBeEmpty(
            "A citation on «{$citation->post?->slug}» has no note. "
            .'Nobody can verify what they have not been told is being claimed.'
        );

        expect(mb_strlen((string) $citation->note))->toBeGreaterThan(
            40,
            "The note on «{$citation->post?->slug}» is too short to name a claim."
        );
    }
});
