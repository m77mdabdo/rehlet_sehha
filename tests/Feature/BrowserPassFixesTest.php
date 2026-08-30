<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Models\WorkingHour;
use App\Support\OpeningHours;
use App\Support\PublicContent;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * The defects the end-to-end browser pass found, pinned so they cannot return.
 *
 * Every one of these was invisible to the suite and obvious within seconds of
 * driving the application. They are pinned here rather than left to the next
 * manual pass, because a manual pass is not a guarantee.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
});

/*
|------------------------------------------------------------------------------
| Retired appointment modes stay editable
|------------------------------------------------------------------------------
*/

it('offers every mode the admin form might have to display, not just the bookable ones', function () {
    /*
     * The form built its options from bookableValues() — the modes a patient
     * may choose today, which is ['online'] since the practice went
     * online-only. Half the appointments in the table were booked as 'clinic'
     * and every one became unsaveable: the field showed the raw string and
     * refused with "The selected نوع الاستشارة is invalid", on a field nobody
     * had touched.
     */
    $options = AppointmentMode::options();

    foreach (AppointmentMode::cases() as $case) {
        // array_key_exists, not toHaveKey: Pest reads a second argument to
        // toHaveKey as the EXPECTED VALUE, not as a failure message.
        expect(array_key_exists($case->value, $options))->toBeTrue(
            "AppointmentMode::options() omits {$case->value}, so an appointment "
            .'booked under that mode cannot be saved in the admin.'
        );

        expect($options[$case->value])->not->toBe($case->value, "{$case->value} has no label; the form would show the raw value.");
    }

    $form = file_get_contents(app_path('Filament/Resources/Appointments/Schemas/AppointmentForm.php'));

    expect(str_contains($form, 'AppointmentMode::options()'))->toBeTrue(
        'The appointment form no longer offers every mode.'
    );

    /*
     * Comments in that file legitimately mention bookableValues() while
     * explaining why it is the wrong helper here, so the check is on the CODE:
     * the options() call must not be a bookableValues() call.
     */
    $withoutComments = (string) preg_replace('#/\*.*?\*/|//[^\n]*#s', '', $form);

    expect(str_contains($withoutComments, 'bookableValues()'))->toBeFalse(
        'The appointment form is back on bookableValues(). That restriction belongs '
        .'in the booking wizard, not in the screen the clinic edits old records with.'
    );
});

/*
|------------------------------------------------------------------------------
| The footer's opening hours are derived
|------------------------------------------------------------------------------
*/

it('builds the footer hours from working_hours rather than a typed string', function () {
    /*
     * The footer carried a hand-written sentence, a few lines under a comment
     * claiming the hours could not be "right on the page and wrong in the
     * structured data". Only the JSON-LD was derived. The moment the schedule
     * became editable, Saturday moved to 18:00, the markup followed, and the
     * footer went on saying 8pm.
     */
    $before = OpeningHours::summary('ar');

    expect($before)->not->toBeEmpty();
    expect(implode(' ', $before))->toContain('٨م');

    WorkingHour::query()->where('day_of_week', 6)->update(['end_time' => '18:00:00']);
    PublicContent::flush();

    $after = OpeningHours::summary('ar');

    expect($after)->not->toBe($before, 'The footer summary did not follow a schedule change.');

    // Saturday now stands alone at 6pm; the rest of the week keeps 8pm.
    expect(implode(' ', $after))->toContain('٦م');

    // And it reaches the page.
    $html = $this->get('/ar')->assertOk()->getContent();

    foreach ($after as $line) {
        expect(str_contains($html, $line))->toBeTrue("The footer does not render: {$line}");
    }
});

it('contracts the arabic preposition in a day range', function () {
    // لِ + الخميس is للخميس, not لـالخميس, which is not a word.
    $summary = implode(' ', OpeningHours::summary('ar'));

    expect($summary)->toContain('للخميس');
    expect($summary)->not->toContain('لـالخميس');
});

it('names the closed days instead of assuming friday', function () {
    WorkingHour::query()->where('day_of_week', 0)->update(['is_active' => false]);
    PublicContent::flush();

    $summary = implode(' ', OpeningHours::summary('ar'));

    expect($summary)->toContain('الأحد');
    expect($summary)->toContain('الجمعة');
});

/*
|------------------------------------------------------------------------------
| Publishing refuses in words, not with a 500
|------------------------------------------------------------------------------
*/

it('states the reviewer requirement as validation rather than leaving it to the exception', function () {
    /*
     * The model's LogicException is the thing that actually protects the site
     * and it stays. What was wrong is that it reached Filament unhandled: a
     * stack trace locally, and in production a bare "Server Error" page that
     * told Rana nothing about what to do.
     */
    $form = file_get_contents(app_path('Filament/Resources/Posts/Schemas/PostForm.php'));

    expect(str_contains($form, '$fail('))->toBeTrue(
        'published_at has no validation rule, so an unreviewed publish reaches the model raw.'
    );

    foreach (['Pages/EditPost.php', 'Pages/CreatePost.php'] as $page) {
        $source = file_get_contents(app_path('Filament/Resources/Posts/'.$page));

        expect(str_contains($source, 'catch (LogicException'))->toBeTrue(
            "{$page} does not catch the model's guard, so any route to it that bypasses "
            .'the form validation still produces a 500.'
        );

        // Halt is what $this->halt() throws; thrown directly so the method
        // provably never falls through.
        // Pint normalises `new Halt()` to `new Halt`, so match the class.
        expect(preg_match('/throw new Halt\b/', $source))->toBe(1,
            "{$page} catches the exception but does not halt the save."
        );
    }
});

/*
|------------------------------------------------------------------------------
| Small ones
|------------------------------------------------------------------------------
*/

it('transliterates the practitioner name rather than translating it', function () {
    expect(config('clinic.practitioner.display_name_en'))->not->toBeEmpty();

    // A transliteration, not a translation: it must not be the Arabic string.
    expect(config('clinic.practitioner.display_name_en'))
        ->not->toBe(config('clinic.practitioner.name_ar'));

    expect(config('clinic.practitioner.display_name_en'))->toMatch('/^[\x20-\x7E]+$/');
});

it('centres the video dialog instead of pinning it to a corner', function () {
    /*
     * <dialog>[open] centres itself through the UA rule
     * `position: fixed; inset: 0; margin: auto`. Tailwind's preflight resets
     * margin to 0 on everything, which killed the third part and left the
     * panel in the top-right over the header in RTL.
     */
    $markup = file_get_contents(resource_path('views/components/sections/videos.blade.php'));

    /*
     * Blade comments are stripped first. The comment explaining the fix
     * contains the literal text "<dialog>", and a non-greedy match found that
     * instead of the real element.
     */
    $withoutComments = (string) preg_replace('/\{\{--.*?--\}\}/s', '', $markup);

    expect(preg_match('/<dialog\b[^>]*>/s', $withoutComments, $tag))->toBe(1, 'No <dialog> found.');

    expect($tag[0])->toContain('m-auto');
});

it('moves focus into the mobile menu when it opens', function () {
    $script = file_get_contents(resource_path('js/menu.js'));

    expect(str_contains($script, 'panel.querySelector'))->toBeTrue(
        'Opening the menu no longer moves focus into it, so a keyboard user '
        .'opens a menu they are not in.'
    );
});

it('marks the direction of a phone number in the admin tables', function () {
    $appointments = file_get_contents(app_path('Filament/Resources/Appointments/Tables/AppointmentsTable.php'));
    $patients = file_get_contents(app_path('Filament/Resources/Patients/Tables/PatientsTable.php'));

    // A "+" leading an E.164 number is reordered to the far end by the bidi
    // algorithm inside an RTL paragraph: +201004818303 displayed as 201004818303+.
    /*
     * The mark is written as a PHP escape in the source, so the file contains
     * the six characters \u{200E} rather than the character itself. Both are
     * accepted: what matters is that the direction is forced somehow.
     */
    $marksDirection = str_contains($appointments, 'u{200E}')
        || str_contains($appointments, "\u{200E}")
        || str_contains($appointments, "'dir' => 'ltr'");

    expect($marksDirection)->toBeTrue(
        'The appointments table no longer marks the phone number LTR.'
    );

    expect(str_contains($patients, "'dir' => 'ltr'"))->toBeTrue(
        'The patients table no longer marks the phone column LTR.'
    );
});
