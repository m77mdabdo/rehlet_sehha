<?php

declare(strict_types=1);

use App\Models\Specialty;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * TWO UTILITIES FOR THE SAME PROPERTY ON ONE ELEMENT.
 *
 * This is the most expensive kind of bug this codebase produces, because it
 * produces NOTHING: no error, no warning, correct markup, correct data, and
 * both classes sitting in the DOM where a review would read them as intentional.
 * Which one wins is decided by their order in the generated stylesheet — an
 * order nobody writing a Blade file can see.
 *
 * It has now happened twice.
 *
 *   1. The promoted specialty card passed `bg-ink` into x-card alongside the
 *      component's own `bg-white`. The card rendered white and the promotion
 *      silently did nothing.
 *   2. The contact page's primary booking card passed `bg-ink text-white
 *      ring-0`. It shipped as WHITE TEXT ON WHITE — the entire call to action
 *      invisible, on the page whose only job is getting somebody to book. It
 *      had been that way for as long as the card existed.
 *
 * Both were fixed by giving x-card a `tone` prop, which is the shape that makes
 * the mistake impossible rather than unlikely. This test is what stops the next
 * component from reintroducing it, and what catches a call site that reaches
 * for a class instead of a tone.
 *
 * IT CHECKS THE RENDERED PAGE, not the templates, because the conflict is only
 * visible after Blade has merged a component's own classes with its call site's
 * — which is exactly where it hides.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
    $this->seed(PostSeeder::class);
});

/**
 * The utility families where a second one silently overrides the first.
 *
 * Deliberately NOT every property. Two paddings or two margins on one element
 * are usually a responsive pair or a deliberate override and are visible the
 * moment somebody looks at the page. These four are the ones that hide: a
 * background or a text colour that loses is invisible by definition, and a
 * conflicting ring reads as a design choice.
 *
 * @return array<string, string>
 */
function conflictingFamilies(): array
{
    return [
        // bg-white / bg-ink, but not bg-white/5 (an opacity variant of the
        // same colour is a deliberate pair) and not the gradient utilities.
        'background' => '/(?<![\w:\/-])bg-(?!gradient|linear|radial|conic|none|clip|origin|fixed|local|scroll|center|cover|contain|repeat|no-repeat|top|bottom|left|right)([a-z]+)(?![\w\/-])/',
        'text colour' => '/(?<![\w:\/-])text-(?!left|right|center|justify|start|end|balance|pretty|nowrap|wrap|clip|ellipsis|xs|sm|base|lg|xl|\dxl|\[)([a-z-]+)(?![\w\/-])/',
    ];
}

it('never renders one element with two utilities fighting for the same property', function (string $locale) {
    $paths = [
        '', 'services', 'packages', 'how-it-works', 'about', 'articles',
        'faq', 'contact', 'booking', 'privacy',
    ];

    $paths[] = 'specialties/'.Specialty::query()->where('is_active', true)->firstOrFail()->slug;

    $offences = [];

    foreach ($paths as $path) {
        $html = $this->get('/'.$locale.($path === '' ? '' : '/'.$path))->assertOk()->getContent();

        preg_match_all('/\sclass="([^"]+)"/', $html, $matches);

        foreach ($matches[1] as $attribute) {
            /*
             * Only the UNPREFIXED utilities. `sm:bg-white` beside `bg-ink` is a
             * responsive override and is the whole point of the prefix; only
             * two bare ones are the silent conflict.
             */
            $classes = array_filter(
                preg_split('/\s+/', trim($attribute)) ?: [],
                fn (string $c): bool => ! str_contains($c, ':')
            );

            foreach (conflictingFamilies() as $family => $pattern) {
                $seen = [];

                foreach ($classes as $class) {
                    if (preg_match($pattern, $class, $hit) === 1) {
                        $seen[] = $hit[0];
                    }
                }

                $seen = array_values(array_unique($seen));

                if (count($seen) > 1) {
                    $offences[] = "{$locale}/{$path} — {$family}: ".implode(' + ', $seen);
                }
            }
        }
    }

    $offences = array_values(array_unique($offences));

    expect($offences)->toBeEmpty(
        "Two utilities are fighting for the same property on one element:\n  "
        .implode("\n  ", $offences)
        ."\n\nWhich one wins is decided by stylesheet order, not by the order they are written.\n"
        .'Give the component a tone/variant prop instead of passing a colour class into it.'
    );
})->with(['ar', 'en']);
