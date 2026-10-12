<?php

declare(strict_types=1);

use App\Enums\FoodGroup;
use App\Models\Category;
use App\Models\PlateFood;
use App\Models\Tag;
use Database\Seeders\DatabaseSeeder;

/*
|------------------------------------------------------------------------------
| Every seeder is actually wired in
|------------------------------------------------------------------------------
|
| Three seeders existed and were never called: CategorySeeder, TagSeeder and
| PlateFoodSeeder. Nothing failed. The test suite passed, because the tests
| that need categories and tags seed them directly, and no test rendered the
| homepage's plate builder against a seeded database.
|
| It surfaced on production a month after the deploy, as a homepage section
| with no content in it and fourteen uncategorised articles. A seeder that is
| never called is indistinguishable from one that works, from the inside.
|
| So this reads the directory rather than trusting a list.
*/

it('calls every seeder in the directory', function () {
    $files = collect(glob(database_path('seeders/*Seeder.php')) ?: [])
        ->map(fn (string $path): string => basename($path, '.php'))
        ->reject(fn (string $class): bool => $class === 'DatabaseSeeder')
        ->values();

    expect($files)->not->toBeEmpty('no seeders found — has the path changed?');

    // The call list, read off the source rather than by running it: running it
    // would prove the happy path works and not that nothing was forgotten.
    $source = file_get_contents((new ReflectionClass(DatabaseSeeder::class))->getFileName());

    $missing = $files
        ->reject(fn (string $class): bool => str_contains((string) $source, $class.'::class'))
        ->all();

    expect($missing)->toBe([], 'these seeders exist but DatabaseSeeder never calls them: '.implode(', ', $missing));
});

it('seeds the plate builder with food, in every group', function () {
    /*
     * The specific thing that was missing. Asserted through a real seed rather
     * than by counting the array in the seeder, because the failure was not a
     * wrong number — it was zero rows, from a seeder nobody invoked.
     */
    $this->seed(DatabaseSeeder::class);

    $foods = PlateFood::query()->active()->get();

    expect($foods)->toHaveCount(29);

    $byGroup = $foods->groupBy(fn (PlateFood $f): string => $f->group->value);

    // Every group the enum offers has something in it. A plate builder with an
    // empty group is a group the patient cannot fill.
    foreach (FoodGroup::cases() as $group) {
        expect($byGroup->has($group->value))->toBeTrue("no foods in the {$group->value} group");
        expect($byGroup[$group->value])->not->toBeEmpty();
    }

    expect($byGroup->map->count()->sort()->values()->all())->toBe([3, 4, 5, 5, 6, 6]);

    // Both languages, because the homepage renders in either.
    $first = $foods->first();
    expect($first->getTranslation('name', 'ar'))->not->toBeEmpty();
    expect($first->getTranslation('name', 'en'))->not->toBeEmpty();
    expect($first->emoji)->not->toBeEmpty();
});

it('gives the articles a category to sit in', function () {
    // Categories and tags have to exist BEFORE PostSeeder, which is why they
    // moved above it in the call list.
    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBeGreaterThan(0);
    expect(Tag::query()->count())->toBeGreaterThan(0);
});
