<?php

declare(strict_types=1);

use App\Models\Service;
use App\Models\Specialty;
use App\Support\PublicContent;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\ServiceSpecialtySeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(ServiceSpecialtySeeder::class);

    Cache::flush();
});

it('serves a page for every active specialty in both locales', function (string $locale) {
    foreach (Specialty::active()->get() as $specialty) {
        $this->get("/{$locale}/specialties/{$specialty->slug}")
            ->assertOk()
            // Escaped, not raw: "Pregnancy & Breastfeeding" renders its
            // ampersand as &amp;, which is correct output and would fail a
            // raw comparison.
            ->assertSee($specialty->getTranslation('name', $locale));
    }
})->with(['ar', 'en']);

it('gives the homepage cards a working link, not a dead end', function () {
    // The bug this whole feature exists to fix: eight cards a visitor could
    // read and then do nothing with.
    $content = $this->get('/ar')->assertOk()->getContent();

    foreach (Specialty::active()->get() as $specialty) {
        expect(str_contains($content, '/ar/specialties/'.$specialty->slug))->toBeTrue(
            "The homepage does not link the {$specialty->slug} card anywhere."
        );
    }
});

it('shows only the packages that suit the specialty', function () {
    $pcos = Specialty::query()->where('slug', 'pcos-hormonal')->firstOrFail();

    $content = $this->get('/ar/specialties/pcos-hormonal')->assertOk()->getContent();

    $suited = $pcos->services;
    expect($suited)->not->toBeEmpty();

    foreach ($suited as $service) {
        expect(str_contains($content, 'booking?service='.$service->slug))->toBeTrue();
    }

    // And crucially NOT the ones that do not: a page listing every package has
    // narrowed nothing, which was the entire point of the pivot.
    $unsuited = Service::active()->get()->reject(
        fn (Service $service): bool => $suited->contains('id', $service->id)
    );

    expect($unsuited)->not->toBeEmpty('Test is vacuous if every service suits every specialty.');

    foreach ($unsuited as $service) {
        expect(str_contains($content, 'booking?service='.$service->slug))->toBeFalse(
            "PCOS page offers {$service->slug}, which is not paired with it."
        );
    }
});

it('recommends the first package by pivot order, not by price', function () {
    $specialty = Specialty::query()->where('slug', 'pcos-hormonal')->firstOrFail();

    $first = $specialty->services->first();

    // Chronic condition: the three-month programme leads, even though it is
    // the most expensive. Ordering by price would put lab review first and
    // recommend the wrong thing.
    expect($first->slug)->toBe('three-months-programme');

    $content = $this->get('/ar/specialties/pcos-hormonal')->getContent();

    $recommendedBlock = substr($content, (int) strpos($content, __('specialties.packages.recommended')));

    expect($recommendedBlock)->toContain($first->getTranslation('name', 'ar'));
});

it('orders the same package differently for a different specialty', function () {
    // Proves sort_order is per-pivot and not a property of the service.
    $chronic = Specialty::query()->where('slug', 'pcos-hormonal')->firstOrFail();
    $oneOff = Specialty::query()->where('slug', 'child-nutrition')->firstOrFail();

    expect($chronic->services->first()->slug)->toBe('three-months-programme');
    expect($oneOff->services->first()->slug)->toBe('single-consultation');
});

it('never offers a withdrawn package on a specialty page', function () {
    $service = Service::query()->where('slug', 'lab-review')->firstOrFail();
    $service->update(['is_active' => false]);

    PublicContent::flush();

    $content = $this->get('/ar/specialties/pcos-hormonal')->assertOk()->getContent();

    expect($content)->not->toContain('booking?service=lab-review');
});

it('404s an unknown or deactivated specialty', function () {
    $this->get('/ar/specialties/does-not-exist')->assertNotFound();

    $specialty = Specialty::query()->where('slug', 'sports-nutrition')->firstOrFail();
    $specialty->update(['is_active' => false]);

    $this->get('/ar/specialties/sports-nutrition')->assertNotFound();
});

it('stays within a bounded number of queries', function () {
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get('/ar/specialties/pcos-hormonal')->assertOk();

    /*
     * Four: the specialty, its services (eager-loaded, so one query and not
     * one per package), the other specialties for the footer strip, and the
     * working hours behind the JSON-LD.
     *
     * This count found a real gap: it was three, because the specialty pages
     * carried no structured data at all. They are the pages built FOR search
     * traffic, so they are the last place that should be missing it.
     */
    expect($queries)->toHaveCount(4, "Specialty page queries:\n".implode("\n", $queries));
});

it('keeps one h1 and does not skip heading levels', function (string $locale) {
    $content = $this->get("/{$locale}/specialties/pcos-hormonal")->assertOk()->getContent();

    preg_match_all('/<h([1-6])\b/i', $content, $matches);
    $levels = array_map('intval', $matches[1]);

    expect(array_count_values($levels)[1] ?? 0)->toBe(1);

    $previous = 1;

    foreach ($levels as $level) {
        expect($level)->toBeLessThanOrEqual($previous + 1, "Jumped h{$previous} to h{$level}.");
        $previous = $level;
    }
})->with(['ar', 'en']);

it('renders no untranslated keys', function (string $locale) {
    $content = $this->get("/{$locale}/specialties/pcos-hormonal")->assertOk()->getContent();

    expect($content)->not->toMatch('/>\s*(home|nav|common|footer|booking|specialties|about)\.[a-z_.]+\s*</');
})->with(['ar', 'en']);

it('shows no weight, bmi or calorie figures', function (string $locale) {
    $content = $this->get("/{$locale}/specialties/weight-management")->assertOk()->getContent();

    foreach (['كيلو', 'كجم', 'سعرة', 'سعرات', 'BMI', 'kcal', 'calorie'] as $term) {
        expect(str_contains($content, $term))->toBeFalse("Mentions «{$term}».");
    }
})->with(['ar', 'en']);

it('translates the post category rather than showing arabic on the english page', function () {
    $this->seed(PostSeeder::class);
    PublicContent::flush();

    // The reason category was hidden from the article cards until now.
    $this->get('/en')->assertOk()->assertSee('Nutrition', false);

    $english = $this->get('/en')->getContent();
    expect($english)->not->toContain('تغذية');

    $this->get('/ar')->assertOk()->assertSee('تغذية', false);
});
