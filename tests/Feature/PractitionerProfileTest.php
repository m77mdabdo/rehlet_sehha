<?php

declare(strict_types=1);

use App\Filament\Pages\PractitionerProfilePage;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Support\ClinicSettings;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/*
|------------------------------------------------------------------------------
| The practitioner's details belong to the clinic
|------------------------------------------------------------------------------
|
| All of this used to live in config/clinic.php, so correcting a licence year
| meant a code change and a deploy. The screen overlays config through
| ClinicSettings, which is why every existing config('clinic.…') call site
| keeps working and the file becomes the fallback rather than the source.
|
| The two things these tests are really defending:
|
|   The allow-list. This writes into application configuration, so a key
|   outside EDITABLE must be impossible to store — otherwise a settings screen
|   is a remote-configuration vulnerability with a nice form on it.
|
|   The name reaching everything together. Her name appears on the about page,
|   under thirteen articles, and inside the structured data search engines
|   read. Changing it in one place and not the others is worse than not being
|   able to change it at all.
*/
beforeEach(function () {
    Cache::flush();
    ClinicSettings::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
});

function profileUser(string $role): User
{
    $user = User::factory()->create();
    $user->syncRoles([$role]);

    return $user->fresh();
}

/*
|------------------------------------------------------------------------------
| Who may reach it
|------------------------------------------------------------------------------
*/

it('lets the doctor and an administrator in, and nobody else', function (string $role, bool $allowed) {
    $this->actingAs(profileUser($role));

    expect(PractitionerProfilePage::canAccess())->toBe($allowed);
})->with([
    ['doctor', true],
    ['admin', true],
    // Reception answers the phone. A licence number is a professional claim on
    // a public page, not day-to-day scheduling.
    ['receptionist', false],
]);

it('refuses to render for a receptionist', function () {
    // canAccess() governs the navigation item; this is the page itself, which
    // has to refuse independently or the screen is reachable by URL.
    $this->actingAs(profileUser('receptionist'));

    Livewire::test(PractitionerProfilePage::class)->assertForbidden();
});

/*
|------------------------------------------------------------------------------
| Saving
|------------------------------------------------------------------------------
*/

it('saves every field and puts it into live config', function () {
    $this->actingAs(profileUser('doctor'));

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm([
            'clinic__practitioner__name_ar' => 'أ. اسم جديد',
            'clinic__practitioner__display_name_en' => 'Dr New Name',
            'clinic__practitioner__title_ar' => 'لقب جديد',
            'clinic__practitioner__title_en' => 'New Title',
            'clinic__practitioner__degree_ar' => 'مؤهل جديد',
            'clinic__practitioner__licence_body_ar' => 'نقابة جديدة',
            'clinic__practitioner__licence_number' => '123456',
            'clinic__practitioner__licence_year' => 2024,
            'clinic__training' => [
                ['institution_ar' => 'جهة', 'programme_ar' => 'برنامج', 'hours' => 40, 'year' => 2023],
            ],
            'clinic__platforms' => ['zoom'],
            'clinic__contact__area_served' => 'EG',
        ])
        ->call('save')
        ->assertHasNoErrors();

    // Stored, and then overlaid onto config the way every call site reads it.
    ClinicSettings::apply();

    expect(config('clinic.practitioner.name_ar'))->toBe('أ. اسم جديد');
    expect(config('clinic.practitioner.display_name_en'))->toBe('Dr New Name');
    expect(config('clinic.practitioner.licence_number'))->toBe('123456');
    expect(config('clinic.contact.area_served'))->toBe('EG');
    expect(config('clinic.platforms'))->toBe(['zoom']);
    expect(config('clinic.training')[0]['institution_ar'])->toBe('جهة');
});

it('shows the saved name on the public about page', function () {
    $this->actingAs(profileUser('doctor'));

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm(['clinic__practitioner__name_ar' => 'أ. اسم ظاهر'])
        ->call('save')
        ->assertHasNoErrors();

    /*
     * ClinicSettings::apply() runs from AppServiceProvider on BOOT, and a test
     * boots the application once rather than per request — so the overlay has
     * to be re-applied by hand here. In production every request boots and
     * picks the stored value up on its own; this line is a test artefact, not
     * a step the site performs.
     */
    ClinicSettings::apply();

    $this->get('/ar/about')->assertOk()->assertSee('أ. اسم ظاهر', false);
});

it('busts the cache so the change is not a day late', function () {
    $this->actingAs(profileUser('doctor'));

    /*
     * Warm the settings cache the way the application does — through all(),
     * which is what apply() calls at boot. A page request will not do it here:
     * the only boot in this test happened before beforeEach() flushed.
     */
    ClinicSettings::all();
    $this->get('/ar/about')->assertOk();

    expect(Cache::has('clinic-settings'))->toBeTrue();

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm(['clinic__practitioner__name_ar' => 'أ. اسم بعد المسح'])
        ->call('save');

    expect(Cache::has('clinic-settings'))->toBeFalse('The settings cache survived a save.');

    // See the note in the test above: the overlay is a boot-time step.
    ClinicSettings::apply();

    $this->get('/ar/about')->assertOk()->assertSee('أ. اسم بعد المسح', false);
});

/*
|------------------------------------------------------------------------------
| The allow-list
|------------------------------------------------------------------------------
*/

it('refuses to write a key outside the allow-list', function () {
    /*
     * The one that matters. This writes into application configuration, so a
     * key nobody vetted must not be storable — app.debug on a production site
     * is the worst case and it is only one row away.
     */
    ClinicSettings::put([
        'app.debug' => true,
        'app.key' => 'base64:hijacked',
        'clinic.practitioner.name_ar' => 'أ. اسم مسموح',
    ]);

    expect(Setting::query()->where('key', 'app.debug')->exists())->toBeFalse();
    expect(Setting::query()->where('key', 'app.key')->exists())->toBeFalse();

    // And the permitted one in the same call still landed, so this is a filter
    // rather than the whole write being rejected.
    expect(Setting::query()->where('key', 'clinic.practitioner.name_ar')->exists())->toBeTrue();
});

it('ignores a stray key even if one reaches the table', function () {
    // Belt and braces: apply() filters on read as well as put() on write, so a
    // row inserted by some other route still cannot reach config.
    Setting::query()->create(['key' => 'app.debug', 'value' => ['value' => true]]);

    config()->set('app.debug', false);
    ClinicSettings::flush();
    ClinicSettings::apply();

    expect(config('app.debug'))->toBeFalse('A stray settings row reached application config.');
});

it('only offers fields that are on the allow-list', function () {
    expect(array_diff(PractitionerProfilePage::FIELDS, ClinicSettings::EDITABLE))->toBe([]);
});

/*
|------------------------------------------------------------------------------
| The name has to reach everything at once
|------------------------------------------------------------------------------
*/

it('changes the byline, the reviewer line and the structured data together', function (string $locale, string $field, string $name) {
    /*
     * The failure this prevents: she corrects her name on the settings screen,
     * the about page updates, and thirteen article bylines keep the old one —
     * along with the reviewedBy in the JSON-LD, which is the copy a search
     * engine keeps.
     *
     * They agree because they all come through Post::reviewerDisplayName().
     */
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
    $this->seed(PostSeeder::class);

    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();

    $post = Post::query()->firstOrFail();
    $post->forceFill([
        'reviewed_by' => $doctor->id,
        'reviewed_at' => now()->subDay(),
        'published_at' => now()->subDay(),
    ])->saveQuietly();

    /*
     * scopePublished() also refuses an article whose citations nobody has
     * checked — which is the whole point of that gate, and means a post is not
     * servable just because published_at is set. Without this the request 404s
     * and the test fails for a reason that has nothing to do with the name.
     */
    $post->citations()->update([
        'verified_by' => $doctor->id,
        'verified_at' => now()->subDay(),
    ]);

    $this->actingAs(profileUser('doctor'));

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm([$field => $name])
        ->call('save')
        ->assertHasNoErrors();

    // Boot-time overlay — see the note further up.
    ClinicSettings::apply();

    $html = $this->get("/{$locale}/articles/{$post->slug}")->assertOk()->getContent();

    // The visible byline.
    expect(str_contains($html, $name))->toBeTrue("The {$locale} byline does not show the new name.");

    // And the structured data, which is the half nobody looks at.
    preg_match('#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#s', $html, $m);
    $graph = json_decode(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'), true);
    $article = collect($graph['@graph'] ?? [$graph])->firstWhere('@type', 'Article');

    expect($article['reviewedBy']['name'] ?? null)->toBe($name, "{$locale} JSON-LD reviewedBy did not follow.");
    expect($article['author']['name'] ?? null)->toBe($name, "{$locale} JSON-LD author did not follow.");
})->with([
    ['ar', 'clinic__practitioner__name_ar', 'أ. اسم متغير'],
    ['en', 'clinic__practitioner__display_name_en', 'Dr Changed Name'],
]);

/*
|------------------------------------------------------------------------------
| Validation
|------------------------------------------------------------------------------
*/

it('will not accept an empty name in either locale', function (string $field) {
    $this->actingAs(profileUser('doctor'));

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm([$field => ''])
        ->call('save')
        ->assertHasFormErrors([$field => 'required']);
})->with([
    'clinic__practitioner__name_ar',
    'clinic__practitioner__display_name_en',
]);

it('requires a licence number once a licensing body is named', function () {
    /*
     * A body with no number behind it is a professional claim with nothing
     * supporting it, on a page a patient reads as credentials.
     */
    $this->actingAs(profileUser('doctor'));

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm([
            'clinic__practitioner__licence_body_ar' => 'نقابة المهن الزراعية',
            'clinic__practitioner__licence_number' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['clinic__practitioner__licence_number']);
});

it('allows no licence number when no body is claimed', function () {
    // The other half: not every practitioner is licensed by a syndicate, and
    // the rule is about the PAIR rather than about the number.
    $this->actingAs(profileUser('doctor'));

    Livewire::test(PractitionerProfilePage::class)
        ->fillForm([
            'clinic__practitioner__licence_body_ar' => '',
            'clinic__practitioner__licence_number' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});
