<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

it('returns the value for the active locale', function () {
    $service = Service::factory()->create([
        'name' => ['ar' => 'استشارة تغذية فردية', 'en' => 'Single Nutrition Consultation'],
    ]);

    app()->setLocale('ar');
    expect($service->name)->toBe('استشارة تغذية فردية');

    app()->setLocale('en');
    expect($service->name)->toBe('Single Nutrition Consultation');
});

it('returns a specific locale on request regardless of the active one', function () {
    $service = Service::factory()->create([
        'name' => ['ar' => 'مراجعة تحاليل', 'en' => 'Lab Review'],
    ]);

    app()->setLocale('ar');

    expect($service->getTranslation('name', 'en'))->toBe('Lab Review')
        ->and($service->getTranslation('name', 'ar'))->toBe('مراجعة تحاليل');
});

it('stores both locales in a single json column', function () {
    $service = Service::factory()->create([
        'name' => ['ar' => 'برنامج شهر', 'en' => 'One Month'],
    ]);

    $raw = DB::table('services')->where('id', $service->id)->value('name');

    expect(json_decode((string) $raw, true))
        ->toBe(['ar' => 'برنامج شهر', 'en' => 'One Month']);
});

it('falls back to the fallback locale when a translation is missing', function () {
    // APP_FALLBACK_LOCALE is en, so a missing Arabic value resolves to English
    // rather than rendering an empty string on the page.
    $service = Service::factory()->create([
        'name' => ['en' => 'Lab Review'],
    ]);

    app()->setLocale('ar');

    expect($service->name)->toBe('Lab Review');
});

it('translates every translatable field on every translatable model', function (string $model, string $attribute) {
    $record = $model::factory()->create([
        $attribute => ['ar' => 'قيمة عربية', 'en' => 'English value'],
    ]);

    app()->setLocale('ar');
    expect($record->{$attribute})->toBe('قيمة عربية');

    app()->setLocale('en');
    expect($record->{$attribute})->toBe('English value');
})->with([
    [Service::class, 'name'],
    [Service::class, 'subtitle'],
    [Service::class, 'description'],
    [Post::class, 'title'],
    [Post::class, 'excerpt'],
    [Post::class, 'body'],
    [Video::class, 'title'],
    [Video::class, 'description'],
    [Testimonial::class, 'quote'],
    [Testimonial::class, 'context'],
    [Faq::class, 'question'],
    [Faq::class, 'answer'],
]);

it('keeps a translated array value intact per locale', function () {
    $service = Service::factory()->create([
        'features' => [
            'ar' => ['تقييم كامل', 'خطة مكتوبة'],
            'en' => ['Full assessment', 'Written plan'],
        ],
    ]);

    expect($service->getTranslation('features', 'ar'))->toBe(['تقييم كامل', 'خطة مكتوبة'])
        ->and($service->getTranslation('features', 'en'))->toBe(['Full assessment', 'Written plan']);
});
