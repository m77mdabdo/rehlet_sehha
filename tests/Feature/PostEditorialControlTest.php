<?php

declare(strict_types=1);

use App\Enums\CitationConfidence;
use App\Enums\PostStatus;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Category;
use App\Models\Citation;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

/**
 * SHE RUNS THE BLOG HERSELF, FROM THE LIST.
 *
 * The thing that happens most often on this screen is not writing. It is
 * looking at fourteen rows, deciding which are ready, and occasionally taking
 * one down in a hurry. Both have to work without opening a form, and the
 * second has to work when something is wrong.
 *
 * THE GATES ARE NOT BYPASSED HERE — THEY ARE EXPLAINED HERE. Post::booted()
 * still throws, and that is deliberate: an exception cannot be forgotten and
 * applies to seeders, imports and tinker as much as to a button. But an
 * exception thrown into a Livewire request is a 500, and in production, with
 * APP_DEBUG off, that is a bare "Server Error" page telling her nothing. So
 * every gate is also readable ahead of time as a sentence she can act on.
 */
beforeEach(function () {
    Cache::flush();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
});

/**
 * A user holding exactly one role.
 *
 * The receptionist is created here rather than seeded: RoleSeeder creates the
 * role, and no seeder creates a person holding it. A test that assumed one
 * existed would pass or fail depending on what happened to be in the database.
 */
function editorial(string $role): User
{
    $existing = User::query()->whereHas('roles', fn ($q) => $q->where('name', $role))->first();

    if ($existing !== null) {
        return $existing;
    }

    $user = User::factory()->create(['email' => $role.'@editorial.test']);
    $user->syncRoles([$role]);

    return $user->fresh();
}

/**
 * An article with nothing standing in its way.
 */
function readyArticle(array $attributes = []): Post
{
    $doctor = editorial('doctor');

    $post = Post::factory()->create(array_merge([
        'category_id' => Category::query()->firstOrFail()->id,
        'reviewed_by' => $doctor->id,
        'reviewed_at' => now()->subDay(),
        'published_at' => null,
    ], $attributes));

    Citation::create([
        'post_id' => $post->id,
        'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
        'title' => ['ar' => 'صحيفة وقائع', 'en' => 'Fact sheet'],
        'confidence' => CitationConfidence::High,
        'note' => 'Supports a claim in the article.',
        'verified_by' => $doctor->id,
        'verified_at' => now(),
    ]);

    return $post->fresh();
}

/*
|------------------------------------------------------------------------------
| What the list is showing her
|------------------------------------------------------------------------------
*/

it('derives a status rather than storing one', function () {
    /*
     * There is no status column and there must not be one — published_at
     * already carries the whole answer, and a stored status is a second copy
     * that can disagree with it. "Published" beside a null date is a row the
     * site refuses to serve while the admin insists it is live.
     */
    expect(readyArticle(['published_at' => null])->status())->toBe(PostStatus::Draft);
    expect(readyArticle(['published_at' => now()->subDay()])->status())->toBe(PostStatus::Published);
    expect(readyArticle(['published_at' => now()->addWeek()])->status())->toBe(PostStatus::Scheduled);
});

it('names every reason an article cannot go out, in Arabic', function () {
    $post = Post::factory()->create([
        'category_id' => Category::query()->firstOrFail()->id,
        'reviewed_by' => null,
        'reviewed_at' => null,
        'published_at' => null,
        'body' => [
            'ar' => "مقدمة.\n\n".Post::CLINICAL_MARKER.": سؤال.\n\n".Post::PRACTITIONER_MARKER.': جملة.',
            'en' => "Intro.\n\n".Post::CLINICAL_MARKER.': a question.',
        ],
    ]);

    Citation::create([
        'post_id' => $post->id,
        'organisation' => ['ar' => 'جهة', 'en' => 'A body'],
        'title' => ['ar' => 'وثيقة', 'en' => 'A document'],
        'confidence' => CitationConfidence::Medium,
        'note' => 'Supports something.',
    ]);

    $blockers = $post->fresh()->publishBlockers();

    /*
     * Every one of these corresponds to a rule in Post::booted(). A rule added
     * there and not here shows up as a button that fails with no explanation,
     * which is the failure this method exists to prevent — so the coverage is
     * asserted by naming what must appear, not by counting.
     */
    $joined = implode(' ', $blockers);

    expect($joined)->toContain('مراجعة إكلينيكية');
    expect($joined)->toContain(Post::CLINICAL_MARKER);
    expect($joined)->toContain(Post::PRACTITIONER_MARKER);
    expect($joined)->toContain('مصدر');

    // Both languages are reported separately: an article finished in Arabic
    // and forgotten in English is the likely shape of the mistake.
    expect($joined)->toContain('العربي');
    expect($joined)->toContain('الإنجليزي');

    foreach ($blockers as $blocker) {
        expect(preg_match('/\p{Arabic}/u', $blocker))
            ->toBe(1, "A blocker is not in Arabic: «{$blocker}»");
    }
});

it('says nothing is missing when nothing is', function () {
    $post = readyArticle();

    expect($post->publishBlockers())->toBe([]);
    expect($post->isReadyToPublish())->toBeTrue();
    expect($post->missingLocales())->toBe([]);
});

it('spots an article finished in only one language', function () {
    $post = readyArticle(['body' => ['ar' => 'نص عربي كامل.', 'en' => '']]);

    expect($post->missingLocales())->toBe(['en']);
    expect($post->isReadyToPublish())->toBeFalse();
});

/*
|------------------------------------------------------------------------------
| One click, from the list
|------------------------------------------------------------------------------
*/

it('publishes a ready article from the list without opening a form', function () {
    $post = readyArticle();

    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->callTableAction('togglePublished', $post)
        ->assertHasNoErrors();

    expect($post->fresh()->status())->toBe(PostStatus::Published);
});

it('refuses to publish a gated article and says which gate', function () {
    /*
     * THE FAILURE THIS PREVENTS IS A 500. Post::booted() throws a
     * LogicException, which reaching Livewire unhandled produces a stack trace
     * locally and a bare "Server Error" in production — she would learn only
     * that publishing did not work.
     */
    $post = Post::factory()->create([
        'category_id' => Category::query()->firstOrFail()->id,
        'reviewed_by' => null,
        'reviewed_at' => null,
        'published_at' => null,
    ]);

    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->callTableAction('togglePublished', $post)
        ->assertHasNoErrors()
        ->assertNotified();

    expect($post->fresh()->published_at)->toBeNull('A gated article was published from the list.');
});

it('takes an article down in one click, with nothing in the way', function () {
    /*
     * THE RULE THIS WHOLE SCREEN IS BUILT AROUND. If an article is wrong, the
     * fastest path off the site must be one click — because otherwise the
     * fastest path becomes deleting the row, and the record of what was
     * published goes with it.
     *
     * Asserted on an article that could NOT be re-published: taking down must
     * not be gated by the rules that govern putting up.
     */
    $post = readyArticle(['published_at' => now()->subDay()]);

    $post->citations()->update(['verified_at' => null]);

    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->callTableAction('togglePublished', $post)
        ->assertHasNoErrors();

    expect($post->fresh()->published_at)->toBeNull();
    expect(Post::query()->whereKey($post->id)->exists())->toBeTrue('Unpublishing deleted the row.');
});

/*
|------------------------------------------------------------------------------
| In bulk
|------------------------------------------------------------------------------
*/

it('publishes in bulk and publishes only what is ready', function () {
    $ready = readyArticle();
    $gated = Post::factory()->create([
        'category_id' => Category::query()->firstOrFail()->id,
        'reviewed_by' => null,
        'reviewed_at' => null,
        'published_at' => null,
    ]);

    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->callTableBulkAction('publishSelected', [$ready, $gated])
        ->assertHasNoErrors();

    /*
     * PARTIAL SUCCESS IS THE POINT. A bulk action that stops at the first
     * refusal leaves the rest unpublished for no reason; one that reports
     * "done" after publishing half is how somebody believes an article is
     * live for a fortnight.
     */
    expect($ready->fresh()->published_at)->not->toBeNull('The ready article was not published.');
    expect($gated->fresh()->published_at)->toBeNull('A gated article was published in bulk.');
});

it('unpublishes in bulk with no gate at all', function () {
    $first = readyArticle(['published_at' => now()->subDay()]);
    $second = readyArticle(['published_at' => now()->subDay()]);

    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->callTableBulkAction('unpublishSelected', [$first, $second])
        ->assertHasNoErrors();

    expect($first->fresh()->published_at)->toBeNull();
    expect($second->fresh()->published_at)->toBeNull();
});

/*
|------------------------------------------------------------------------------
| Who may do any of it
|------------------------------------------------------------------------------
*/

it('shows the publish control to nobody but a doctor or an admin', function () {
    /*
     * Publishing an article is signing it off under a licensed practitioner's
     * name. Reception may see the list; it may not decide what the clinic
     * says about PCOS.
     *
     * Hidden rather than disabled: a disabled button still tells somebody that
     * this is a thing she is nearly allowed to do, and the next step is asking
     * a colleague to click it.
     */
    $post = readyArticle();

    /*
     * RECEPTION CANNOT REACH THE SCREEN AT ALL, which is stronger than a
     * hidden button and is what PostPolicy::viewAny already enforces. The
     * assertion is written against the HTTP route rather than the Livewire
     * component because that is where the refusal happens — mounting the
     * component as a receptionist aborts before there is an instance to
     * inspect, which is the correct behaviour and an unhelpful thing to
     * assert on.
     */
    $this->actingAs(editorial('receptionist'))
        ->get('/admin/posts')
        ->assertForbidden();

    /*
     * And the doctor can, which the component test below proves by using the
     * screen rather than by fetching it — the HTTP route for a Filament page
     * returns a Livewire redirect during two-factor enrolment, and asserting
     * a 200 on it would be asserting the enrolment state rather than the
     * permission.
     *
     * Within the screen, the control is gated on the review permission rather
     * than on merely being able to see the list, so an account that could one
     * day read articles without signing them off still could not publish.
     */
    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->assertTableActionVisible('togglePublished', $post);
});

/*
|------------------------------------------------------------------------------
| Filters
|------------------------------------------------------------------------------
*/

it('filters by status, including the scheduled state that has no column', function () {
    $draft = readyArticle(['published_at' => null]);
    $live = readyArticle(['published_at' => now()->subDay()]);
    $scheduled = readyArticle(['published_at' => now()->addWeek()]);

    $list = Livewire::actingAs(editorial('doctor'))->test(ListPosts::class);

    $list->filterTable('status', PostStatus::Scheduled->value)
        ->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$draft, $live]);

    $list->filterTable('status', PostStatus::Draft->value)
        ->assertCanSeeTableRecords([$draft])
        ->assertCanNotSeeTableRecords([$live, $scheduled]);
});

it('filters incomplete translations in SQL rather than on the page', function () {
    /*
     * Filtering a page of results in PHP filters the PAGE, not the table: the
     * rows paginated away are never examined, so the filter appears to work
     * while quietly lying about everything past row ten.
     */
    $complete = readyArticle();
    $missing = readyArticle(['body' => ['ar' => 'نص عربي.', 'en' => '']]);

    Livewire::actingAs(editorial('doctor'))
        ->test(ListPosts::class)
        ->filterTable('locale_complete', false)
        ->assertCanSeeTableRecords([$missing])
        ->assertCanNotSeeTableRecords([$complete]);
});
