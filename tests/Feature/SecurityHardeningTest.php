<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\NotificationLog;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * The security posture, asserted rather than assumed.
 *
 * Everything here has been true at some point and could stop being true
 * without anybody noticing: a cast removed during a refactor, a policy
 * loosened to fix a bug, a header dropped when middleware was reordered.
 * None of it is visible on screen.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(CategorySeeder::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

/*
|------------------------------------------------------------------------------
| Encryption at rest
|------------------------------------------------------------------------------
*/

it('stores every clinical field encrypted and still reads it back', function () {
    /*
     * END TO END, against the real column. A cast that is removed leaves the
     * application working perfectly — values write and read exactly as before
     * — and every medical history in the table becomes plain text. Nothing on
     * screen changes, so only this can catch it.
     */
    $appointment = Appointment::factory()->create();

    $written = [
        'medications' => 'ميتفورمين ٥٠٠',
        'conditions' => 'تكيس مبايض ونقص فيتامين د',
        'avoid_foods' => 'حساسية من المكسرات',
        'note' => 'بتشتغل ورديات ليلية',
    ];

    $intake = IntakeForm::factory()->create($written + [
        'appointment_id' => $appointment->id,
        'goal' => 'weight_management',
    ]);

    $raw = DB::table('intake_forms')->where('id', $intake->id)->first();

    foreach ($written as $column => $value) {
        expect($raw->{$column})->not->toBe(
            $value,
            "intake_forms.{$column} is stored in plain text. Every medical history in this table is readable to anybody who reaches the database or a backup of it."
        );

        expect($intake->fresh()->{$column})->toBe($value, "{$column} does not decrypt back to what was written.");
    }
});

it('leaves goal readable on purpose, and says so', function () {
    /*
     * The deliberate exception. goal is a coarse category the clinic filters
     * and reports on, so it is stored readable — and the reasoning is written
     * into the model. Pinned here so that if somebody encrypts it as a tidy-up,
     * they meet the argument rather than silently breaking the filters.
     */
    $intake = IntakeForm::factory()->create([
        'appointment_id' => Appointment::factory()->create()->id,
        'goal' => 'weight_management',
    ]);

    $raw = DB::table('intake_forms')->where('id', $intake->id)->first();

    expect($raw->goal)->toBe('weight_management');

    $source = file_get_contents(app_path('Models/IntakeForm.php'));

    expect(str_contains($source, 'stored unencrypted'))->toBeTrue(
        'The reason goal is readable is no longer written down beside it.'
    );
});

it('keeps a notification recipient out of plain text', function () {
    $log = NotificationLog::factory()->create(['recipient' => 'patient@example.com']);

    $raw = DB::table('notification_logs')->where('id', $log->id)->first();

    expect($raw->recipient)->not->toContain('patient@example.com');
    expect($log->fresh()->recipient)->toBe('patient@example.com');
});

/*
|------------------------------------------------------------------------------
| Policies, checked in the payload
|------------------------------------------------------------------------------
*/

it('gives each role exactly the panel resources it should have', function (string $role, array $allowed, array $denied) {
    $user = User::factory()->create();
    $user->assignRole($role);

    foreach ($allowed as $path) {
        expect($this->actingAs($user)->get($path)->status())->toBe(
            200,
            "A {$role} cannot reach {$path} and should be able to."
        );
    }

    foreach ($denied as $path) {
        expect($this->actingAs($user)->get($path)->status())->toBe(
            403,
            "A {$role} CAN reach {$path} and must not."
        );
    }
})->with([
    ['admin', ['/admin/appointments', '/admin/patients', '/admin/posts', '/admin/users', '/admin/working-hours', '/admin/categories', '/admin/tags'], []],
    ['doctor', ['/admin/appointments', '/admin/patients', '/admin/posts', '/admin/working-hours', '/admin/categories', '/admin/tags'], ['/admin/users']],
    ['receptionist', ['/admin/appointments', '/admin/patients'], ['/admin/posts', '/admin/users', '/admin/working-hours', '/admin/categories', '/admin/tags']],
]);

it('keeps clinical content out of a receptionist response body, not merely off her screen', function () {
    /*
     * THE PAYLOAD, NOT THE UI. A relation manager hidden with a CSS class or
     * an @if in a Blade template still ships the data to the browser, where
     * anybody can read it in devtools. This asserts the bytes.
     */
    $appointment = Appointment::factory()->create();

    IntakeForm::factory()->create([
        'appointment_id' => $appointment->id,
        'conditions' => 'تكيس مبايض ونقص فيتامين د',
        'medications' => 'ميتفورمين',
    ]);

    $reception = User::factory()->create();
    $reception->assignRole('receptionist');

    $body = $this->actingAs($reception)
        ->get('/admin/appointments/'.$appointment->id.'/edit')
        ->assertOk()
        ->getContent();

    foreach (['تكيس مبايض', 'ميتفورمين'] as $clinical) {
        expect(str_contains($body, $clinical))->toBeFalse(
            "A receptionist's page carries «{$clinical}» in the response body."
        );
    }

});

it('gates the clinical record on the policy, and the relation manager checks it', function () {
    /*
     * THE AUTHORISATION ITSELF, not the rendered bytes.
     *
     * An earlier version of this asserted the clinical text was present in the
     * doctor's page body and absent from the receptionist's. The absence half
     * passed for the wrong reason: the intake sits in a Filament relation
     * manager, which hydrates over a later Livewire request, so it is in
     * NEITHER initial payload. A test that both roles pass identically proves
     * nothing about either.
     *
     * So the guard is asserted where it lives — the policy — plus the fact
     * that the relation manager consults it before mounting. The end-to-end
     * comparison was driven in a real browser in Task 8.14 F: same URL, same
     * needle, doctor true / receptionist false.
     */
    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();

    $reception = User::factory()->create();
    $reception->assignRole('receptionist');

    expect($doctor->can('viewAny', IntakeForm::class))->toBeTrue('A doctor cannot read a clinical record.');
    expect($reception->can('viewAny', IntakeForm::class))->toBeFalse('A receptionist CAN read a clinical record.');

    $manager = file_get_contents(
        app_path('Filament/Resources/Appointments/RelationManagers/IntakeRelationManager.php')
    );

    /*
     * canViewForRecord runs BEFORE the panel mounts the relation manager, so a
     * denied user never receives the markup at all — rather than receiving it
     * and having it hidden, which ships the data to the browser.
     */
    expect(str_contains($manager, 'canViewForRecord'))->toBeTrue(
        'The relation manager no longer gates itself, so its markup would be sent to anybody who can open the appointment.'
    );

    expect(str_contains($manager, "can('viewAny', IntakeForm::class)"))->toBeTrue(
        'The relation manager gate no longer consults the policy.'
    );
});

/*
|------------------------------------------------------------------------------
| Headers
|------------------------------------------------------------------------------
*/

it('sends the security headers on every public response', function (string $path) {
    $response = $this->get($path)->assertOk();

    foreach ([
        'Content-Security-Policy',
        'X-Frame-Options',
        'X-Content-Type-Options',
        'Referrer-Policy',
        'Permissions-Policy',
    ] as $header) {
        expect($response->headers->get($header))->not->toBeEmpty("{$path} has no {$header}.");
    }

    $csp = (string) $response->headers->get('Content-Security-Policy');

    // The public site carries one inline script and it is nonced. Allowing
    // unsafe-inline here would let an injected <script> run.
    expect($csp)->toContain("script-src 'self' 'nonce-");
    expect($csp)->not->toContain("script-src 'self' 'unsafe-inline'");

    expect($csp)->toContain("frame-ancestors 'none'");
    expect($csp)->toContain("object-src 'none'");
    expect($csp)->toContain("form-action 'self'");
})->with(['/ar', '/en', '/ar/articles', '/ar/booking', '/ar/contact']);

it('loads nothing from a third party except the video facade', function (string $path) {
    /*
     * The whole site is one origin: fonts self-hosted, no analytics, no tag
     * manager, no CDN. That was briefly untrue — the admin panel was linking
     * fonts.bunny.net, found by this policy blocking it — so it is asserted
     * rather than assumed.
     */
    $html = $this->get($path)->assertOk()->getContent();

    preg_match_all('#https?://([a-z0-9.-]+)#i', $html, $hosts);

    $allowed = ['schema.org', 'www.w3.org', 'wa.me', 'www.youtube-nocookie.com', 'localhost', '127.0.0.1'];

    foreach (array_unique($hosts[1]) as $host) {
        expect(in_array($host, $allowed, true))->toBeTrue(
            "{$path} references a third-party host: {$host}"
        );
    }
})->with(['/ar', '/en', '/ar/articles', '/ar/booking']);

it('never sends HSTS off production', function () {
    // Sent from a developer's machine it pins the whole domain — every other
    // project on localhost included — to https for a year.
    expect($this->get('/ar')->headers->get('Strict-Transport-Security'))->toBeNull();
});

/*
|------------------------------------------------------------------------------
| The policy the browser actually receives
|------------------------------------------------------------------------------
|
| Production overwrites the Content-Security-Policy HEADER on every response
| with a bare `upgrade-insecure-requests` — a LiteSpeed-level directive this
| account does not own and cannot see. Measured 2026-09-08: the origin does it
| too, so it is not the CDN, and it is in no .htaccess under the account.
|
| So the header passing its assertions above proves nothing about what a
| patient's browser enforces. The meta element is the copy that survives, and
| these tests guard it. If they are ever deleted as duplicates of the header
| tests, the site silently loses its entire CSP.
*/

/** Pull the meta policy out of a rendered page, entity-decoded. */
function metaPolicy(string $html): ?string
{
    if (! preg_match('#<meta http-equiv="Content-Security-Policy" content="([^"]*)">#i', $html, $m)) {
        return null;
    }

    return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
}

it('publishes the policy as a meta tag, because the host overwrites the header', function (string $path) {
    $response = $this->get($path)->assertOk();
    $meta = metaPolicy($response->getContent());

    expect($meta)->not->toBeNull("{$path} sends no meta Content-Security-Policy.");

    // Same policy as the header, directive for directive, minus the one a meta
    // element is not allowed to carry.
    $header = (string) $response->headers->get('Content-Security-Policy');
    $expected = str_replace("; frame-ancestors 'none'", '', $header);

    expect($meta)->toBe($expected, "{$path}: meta policy has drifted from the header policy.");
})->with(['/ar', '/en', '/ar/articles', '/ar/booking', '/ar/contact']);

it('carries the same nonce in the meta policy as on the inline script', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('#<script nonce="([^"]+)"#', $html, $script);
    expect($script[1] ?? null)->not->toBeNull('The inline script lost its nonce.');

    expect(str_contains(metaPolicy($html) ?? '', "'nonce-{$script[1]}'"))->toBeTrue(
        'The meta policy does not authorise the nonce the page actually used — '
        .'the motion bootstrap would be blocked in production.'
    );
});

it('puts the meta policy above everything it governs', function (string $path) {
    /*
     * A policy in a meta element applies only to what the parser meets AFTER
     * it. A script or stylesheet above the tag is outside the policy entirely,
     * which is the failure mode this whole approach has: it does not error, it
     * just quietly does not cover the thing that was moved.
     */
    $html = $this->get($path)->assertOk()->getContent();

    $meta = stripos($html, '<meta http-equiv="Content-Security-Policy"');
    expect($meta)->not->toBeFalse("{$path} sends no meta Content-Security-Policy.");

    $checked = 0;

    foreach (['<script', '<style', '<link'] as $tag) {
        $first = stripos($html, $tag);

        if ($first === false) {
            continue;
        }

        $checked++;
        expect($first)->toBeGreaterThan($meta, "{$path}: a {$tag} tag sits ABOVE the meta policy and is not covered by it.");
    }

    // Without this the loop asserts nothing on a page that happens to contain
    // none of the three, and passes while proving it.
    expect($checked)->toBeGreaterThan(0, "{$path} has no script, style or link tag to check the ordering against.");
})->with(['/ar', '/en', '/ar/articles', '/ar/booking', '/ar/contact']);

it('leaves frame-ancestors out of the meta policy and covers it with X-Frame-Options', function () {
    /*
     * frame-ancestors is ignored in a meta element by specification, and
     * browsers warn about it on every page load. X-Frame-Options: DENY says
     * the same thing and — unlike CSP — production does not overwrite it.
     */
    $response = $this->get('/ar')->assertOk();

    expect(str_contains(metaPolicy($response->getContent()) ?? '', 'frame-ancestors'))->toBeFalse(
        'frame-ancestors in a meta policy is ignored and logs a console warning on every page load.'
    );

    expect($response->headers->get('X-Frame-Options'))->toBe('DENY');
});

it('gives the admin panel a meta policy too', function () {
    // Filament renders its own layout, so the public one cannot supply this.
    // The login page is enough: it is the same head, and needs no session.
    $meta = metaPolicy($this->get('/admin/login')->assertOk()->getContent());

    expect($meta)->not->toBeNull('The admin panel sends no meta Content-Security-Policy.');

    foreach (["default-src 'self'", "form-action 'self'", "base-uri 'self'", "object-src 'none'"] as $directive) {
        expect(str_contains($meta, $directive))->toBeTrue("The panel policy is missing {$directive}.");
    }
});
