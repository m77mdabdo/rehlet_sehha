<?php

declare(strict_types=1);

use App\Models\WorkingHour;
use App\Support\ClinicSchema;
use App\Support\PublicContent;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * Structured data is the one thing on this site no human ever looks at, which
 * is exactly why it needs a test. A wrong opening hour in the JSON-LD does not
 * render badly — it renders perfectly, and puts the wrong hours in a Google
 * result for months.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    Cache::flush();
});

it('emits exactly one valid json-ld block on the page', function () {
    $content = $this->get('/ar')->assertOk()->getContent();

    expect(substr_count($content, 'application/ld+json'))->toBe(1);

    expect(preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $match))->toBe(1);

    $decoded = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);

    expect($decoded)->toBeArray()
        ->and($decoded['@context'])->toBe('https://schema.org')
        ->and($decoded['@type'])->toBe('MedicalClinic');
});

it('carries the fields a rich result needs', function () {
    $schema = ClinicSchema::build();

    foreach (['name', 'url', 'telephone', 'address', 'openingHoursSpecification'] as $field) {
        expect($schema)->toHaveKey($field);
    }

    expect($schema['telephone'])->toBe('+201004818303');
    expect($schema['address']['@type'])->toBe('PostalAddress');
    expect($schema['address']['addressCountry'])->toBe('EG');
});

it('groups the schedule instead of repeating a row per day', function () {
    $hours = ClinicSchema::build()['openingHoursSpecification'];

    // Six identical days must collapse to one specification, not six.
    expect($hours)->toHaveCount(1);

    expect($hours[0]['@type'])->toBe('OpeningHoursSpecification');
    expect($hours[0]['opens'])->toBe('10:00');
    expect($hours[0]['closes'])->toBe('20:00');

    // Carbon's 0=Sunday mapped to schema.org's English day names, which are a
    // machine vocabulary and must not be translated even on the Arabic page.
    expect($hours[0]['dayOfWeek'])->toBe([
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Saturday',
    ]);

    expect($hours[0]['dayOfWeek'])->not->toContain('Friday');
});

it('keeps schema day names in english on the arabic page', function () {
    app()->setLocale('ar');

    $json = ClinicSchema::toJson();

    expect($json)->toContain('"Saturday"');
    expect($json)->toContain('"MedicalClinic"');
});

it('splits the specification when days genuinely differ', function () {
    WorkingHour::query()->where('day_of_week', 6)->update(['end_time' => '16:00:00']);
    PublicContent::flush();

    $hours = ClinicSchema::build()['openingHoursSpecification'];

    expect($hours)->toHaveCount(2);

    $closingTimes = array_column($hours, 'closes');
    sort($closingTimes);

    expect($closingTimes)->toBe(['16:00', '20:00']);
});

it('omits opening hours entirely rather than inventing them', function () {
    WorkingHour::query()->update(['is_active' => false]);
    PublicContent::flush();

    // An empty openingHoursSpecification array would tell a search engine the
    // clinic is never open. Absent is the honest encoding of "we did not say".
    expect(ClinicSchema::build())->not->toHaveKey('openingHoursSpecification');
});

it('drops contact fields that are not configured', function () {
    config()->set('clinic.contact.phone', null);
    config()->set('clinic.contact.address', null);

    $schema = ClinicSchema::build();

    expect($schema)->not->toHaveKey('telephone');
    expect($schema)->not->toHaveKey('address');

    // The clinic is still identifiable — a partial record beats no record.
    expect($schema)->toHaveKey('name');
});

it('points at the current locale url', function (string $locale) {
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $match);
    $schema = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);

    expect($schema['url'])->toEndWith('/'.$locale);
    expect($schema['inLanguage'])->toBe($locale);
})->with(['ar', 'en']);
