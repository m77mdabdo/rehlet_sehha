<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use App\Support\Contact;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Symfony\Component\Finder\Finder;

/**
 * WhatsApp, and the promise we must not make.
 *
 * The clinic's entire WhatsApp integration is a wa.me hyperlink. It opens the
 * patient's own WhatsApp with text already typed; she sends it or she does
 * not. Nothing in this application can originate a WhatsApp message — there is
 * no Business API, no gateway, no third party holding a patient list.
 *
 * Two things therefore have to stay true, and both decay silently:
 *
 *   1. No interface copy or email may promise a WhatsApp confirmation or
 *      reminder. Copy is written by people who reasonably assume the clinic
 *      messages its patients, because the clinic's staff do — by hand, when
 *      they get to it. A page that says "your confirmation will arrive on
 *      WhatsApp" is a promise the software cannot keep.
 *
 *   2. The prefilled text must carry nothing clinical. It becomes a URL, and a
 *      URL survives in browser history, in a screenshot sent to a relative,
 *      and in the address bar during a screen share.
 */
beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', 'Africa/Cairo'));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    Carbon::setTestNow();
});

function whatsappAppointment(): Appointment
{
    $service = Service::active()->firstOrFail();

    $slot = app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(14)->utc(),
        null,
        $service,
    )->firstOrFail();

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
        'mode' => AppointmentMode::Online,
        'locale' => 'ar',
    ]);

    IntakeForm::factory()->create([
        'appointment_id' => $appointment->id,
        'goal' => 'weight_management',
        'medications' => 'ميتفورمين 500',
        'conditions' => 'تكيس مبايض',
        'consent_at' => now(),
        'consent_ip' => '203.0.113.4',
    ]);

    return $appointment->fresh();
}

/*
|------------------------------------------------------------------------------
| The link itself
|------------------------------------------------------------------------------
*/

it('builds a wa.me link with the message prefilled', function () {
    $href = Contact::whatsappMessageHref('أهلًا، رقم الحجز RS-ABC123.');

    expect($href)->toStartWith('https://wa.me/');
    expect($href)->toContain(config('clinic.contact.whatsapp'));

    // rawurlencode, not urlencode: WhatsApp renders a "+" literally, so a
    // urlencoded space arrives as a plus sign in the middle of the message.
    expect($href)->not->toContain('+');

    $query = [];
    parse_str((string) parse_url((string) $href, PHP_URL_QUERY), $query);

    expect($query['text'])->toBe('أهلًا، رقم الحجز RS-ABC123.');
});

it('offers a prefilled whatsapp link on the manage page carrying nothing clinical', function () {
    $appointment = whatsappAppointment();

    $content = $this->get('/ar/appointment/'.$appointment->cancel_token)
        ->assertOk()
        ->getContent();

    preg_match_all('#https://wa\.me/[^"\'\s<>]+#', $content, $links);

    expect($links[0])->not->toBeEmpty('The manage page should offer a way to message the clinic.');

    $prefilled = collect($links[0])->first(fn (string $link): bool => str_contains($link, 'text='));

    expect($prefilled)->not->toBeNull();

    $query = [];
    parse_str((string) parse_url(html_entity_decode($prefilled), PHP_URL_QUERY), $query);

    // The reference identifies the appointment to the clinic and means nothing
    // to anyone else, so it belongs here.
    expect($query['text'])->toContain($appointment->reference);

    // Nothing about her health does.
    foreach (['ميتفورمين', 'تكيس', 'مبايض', __('booking.goals.weight_management', locale: 'ar')] as $clinical) {
        expect($query['text'])->not->toContain($clinical);
    }
});

/*
|------------------------------------------------------------------------------
| The promise we do not make
|------------------------------------------------------------------------------
*/

it('never promises a whatsapp message the application cannot send', function () {
    /*
     * Scans every translation string in every locale.
     *
     * The rule is narrow on purpose: mentioning WhatsApp is fine, and there
     * are several legitimate mentions — a footer link, "message us on
     * WhatsApp and we will answer". Those describe the PATIENT contacting the
     * clinic, which a wa.me link genuinely does.
     *
     * What is forbidden is the clinic promising to send something. A string
     * that pairs a first-person-plural sending verb with WhatsApp is the
     * clinic committing to deliver a message no code exists to deliver.
     */
    $forbidden = [
        // Arabic: "we will send you… WhatsApp", "…arrives on WhatsApp",
        // "we will confirm… on WhatsApp", "we will remind you… on WhatsApp".
        '/(هنبعت|هنبعتلك|بنبعت|هنأكد|هنأكدلك|هنفكرك|بنفكرك|بييجي|وييجيلك|هيوصلك)[^.،]{0,60}واتساب/u',
        // English equivalents.
        '/\b(we will send|we\'ll send|we will confirm|we\'ll confirm|we will remind|we\'ll remind)\b[^.]{0,60}\bwhatsapp\b/i',
        '/\b(confirmation|reminder)\b[^.]{0,40}\b(arrives?|will arrive)\b[^.]{0,30}\bwhatsapp\b/i',
    ];

    $offenders = [];

    foreach (Finder::create()->files()->in(lang_path())->name('*.php') as $file) {
        $strings = require $file->getRealPath();

        array_walk_recursive($strings, function ($value, $key) use (&$offenders, $forbidden, $file): void {
            if (! is_string($value)) {
                return;
            }

            foreach ($forbidden as $pattern) {
                if (preg_match($pattern, $value) === 1) {
                    $offenders[] = sprintf(
                        '%s [%s]: %s',
                        str_replace(base_path().'/', '', $file->getRealPath()),
                        $key,
                        $value,
                    );
                }
            }
        });
    }

    expect($offenders)->toBeEmpty(
        "Copy promises a WhatsApp message that nothing in this application sends.\n\n"
        ."The clinic has no WhatsApp API. wa.me links let a PATIENT message the\n"
        ."clinic; they cannot deliver anything to her. Say email, or say nothing\n"
        ."about the channel.\n\n"
        .implode("\n", $offenders)."\n"
    );
});

it('detects a whatsapp promise when one is reintroduced', function () {
    // Guards the guard: the patterns above are the whole protection, and a
    // regex that silently stopped matching would leave the scan green forever.
    $shouldFlag = [
        'حجزك اتسجّل، وهنبعتلك تأكيد على واتساب.',
        'هنأكدلك الميعاد على واتساب خلال ساعات العمل.',
        'التأكيد بييجي على واتساب خلال ساعات المتابعة.',
        'Your booking is recorded, and your confirmation will arrive on WhatsApp.',
        'We will send your confirmation on WhatsApp.',
        'We will remind you on WhatsApp the day before.',
    ];

    $shouldNotFlag = [
        // The patient contacting the clinic — exactly what a wa.me link does.
        'لو سؤالك مش هنا، ابعتيلنا على واتساب ونرد عليكِ.',
        'كلمينا أو ابعتيلنا على واتساب ونحجزلك.',
        'If yours is not here, send us a message on WhatsApp and we will answer it.',
        'Message the clinic on WhatsApp',
        'واتساب',
        // Email promises are fine; that channel exists.
        'هنبعتلك تأكيد بالإيميل.',
        'We will send your confirmation by email.',
    ];

    $forbidden = [
        '/(هنبعت|هنبعتلك|بنبعت|هنأكد|هنأكدلك|هنفكرك|بنفكرك|بييجي|وييجيلك|هيوصلك)[^.،]{0,60}واتساب/u',
        '/\b(we will send|we\'ll send|we will confirm|we\'ll confirm|we will remind|we\'ll remind)\b[^.]{0,60}\bwhatsapp\b/i',
        '/\b(confirmation|reminder)\b[^.]{0,40}\b(arrives?|will arrive)\b[^.]{0,30}\bwhatsapp\b/i',
    ];

    $matches = function (string $sample) use ($forbidden): bool {
        foreach ($forbidden as $pattern) {
            if (preg_match($pattern, $sample) === 1) {
                return true;
            }
        }

        return false;
    };

    foreach ($shouldFlag as $sample) {
        expect($matches($sample))->toBeTrue("expected to flag: {$sample}");
    }

    foreach ($shouldNotFlag as $sample) {
        expect($matches($sample))->toBeFalse("false positive on: {$sample}");
    }
});

it('never promises a whatsapp message from an email template either', function () {
    $offenders = [];

    foreach (Finder::create()->files()->in(resource_path('views/emails'))->name('*.blade.php') as $file) {
        $source = $file->getContents();

        if (stripos($source, 'whatsapp') !== false || str_contains($source, 'واتساب')) {
            $offenders[] = str_replace(base_path().'/', '', $file->getRealPath());
        }
    }

    /*
     * No mail template mentions WhatsApp at all. An email saying "we will also
     * message you on WhatsApp" would be the worst version of this promise: it
     * arrives in writing, it is kept, and it is re-read by someone waiting for
     * a message that is never coming.
     */
    expect($offenders)->toBeEmpty(
        "A mail template mentions WhatsApp:\n".implode("\n", $offenders)
    );
});
