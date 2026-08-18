<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Faq;
use App\Models\IntakeForm;
use App\Models\NotificationLog;
use App\Models\Patient;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Video;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Model;

it('can create every model from its factory', function (string $model) {
    /** @var Model $instance */
    $instance = $model::factory()->create();

    expect($instance->exists)->toBeTrue()
        ->and($instance->getKey())->not->toBeNull();

    $this->assertDatabaseHas($instance->getTable(), [
        $instance->getKeyName() => $instance->getKey(),
    ]);
})->with([
    Appointment::class,
    BlockedSlot::class,
    Faq::class,
    IntakeForm::class,
    NotificationLog::class,
    Patient::class,
    Post::class,
    Service::class,
    Setting::class,
    Testimonial::class,
    User::class,
    Video::class,
    WorkingHour::class,
]);

it('can create many of a model without colliding on unique columns', function () {
    // Each appointment pulls in its own patient and service, so those two are
    // asserted relative to their pre-existing count rather than absolutely.
    Appointment::factory()->count(5)->create();

    $servicesBefore = Service::count();
    $patientsBefore = Patient::count();

    Service::factory()->count(5)->create();
    Post::factory()->count(5)->create();
    Video::factory()->count(5)->create();
    Patient::factory()->count(5)->create();

    expect(Appointment::count())->toBe(5)
        ->and(Post::count())->toBe(5)
        ->and(Video::count())->toBe(5)
        ->and(Service::count())->toBe($servicesBefore + 5)
        ->and(Patient::count())->toBe($patientsBefore + 5);
});
