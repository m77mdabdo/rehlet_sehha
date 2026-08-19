<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\WorkingHour;
use Illuminate\Support\Collection;

/**
 * The schema.org MedicalClinic description of the practice.
 *
 * This is what puts opening hours, a phone number and an address into a Google
 * result rather than just a blue link. It is also the one place on the site
 * where being machine-readable matters more than being readable, so it is
 * built from the same config and the same working_hours rows the site renders
 * — a hand-written JSON-LD block is a second copy of the clinic's hours that
 * nobody notices has gone wrong, because no human ever looks at it.
 *
 * MedicalClinic rather than LocalBusiness: it is the specific type, and Google
 * treats medical entities differently. It inherits everything LocalBusiness
 * offers, so nothing is lost by being precise.
 */
final class ClinicSchema
{
    /**
     * Carbon's day_of_week (0 = Sunday) mapped to schema.org's day URIs.
     *
     * schema.org wants the full English day name regardless of the page's
     * language — this is a machine vocabulary, not display text, so it is not
     * translated and must not be.
     *
     * @var array<int, string>
     */
    private const SCHEMA_DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function build(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalClinic',
            '@id' => url('/').'#clinic',
            'name' => __('common.brand'),
            'description' => __('home.meta_description'),
            'url' => url()->current(),
            'medicalSpecialty' => 'Nutrition',
            'inLanguage' => Locales::current(),
        ];

        if (($phone = Contact::phone()) !== null) {
            $schema['telephone'] = $phone;
        }

        if (($email = Contact::email()) !== null) {
            $schema['email'] = $email;
        }

        /*
         * PostalAddress with addressCountry as the ISO code, because that is
         * the field consumers actually parse. The locality is the translated
         * address line — "المعادي، القاهرة" is genuinely the address for an
         * Arabic reader, and schema.org permits localised address text.
         */
        if (($address = Contact::address()) !== null) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $address,
                'addressCountry' => 'EG',
            ];
        }

        $hours = self::openingHours();

        if ($hours !== []) {
            $schema['openingHoursSpecification'] = $hours;
        }

        return $schema;
    }

    public static function toJson(): string
    {
        return json_encode(
            self::build(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Opening hours read from working_hours, grouped so that consecutive days
     * sharing the same times become one specification entry rather than six.
     *
     * Days with no row are simply absent, which is how the slot generator
     * already reads "closed" — Friday needs no entry saying it is shut.
     *
     * Times are Cairo wall-clock, which is what schema.org wants: these are
     * opening hours, not instants, and converting them to UTC would tell a
     * search engine the clinic opens at 08:00.
     *
     * @return list<array<string, mixed>>
     */
    private static function openingHours(): array
    {
        return PublicContent::openingHours()
            ->groupBy(fn (WorkingHour $hour): string => substr($hour->start_time, 0, 5).'-'.substr($hour->end_time, 0, 5))
            ->map(function (Collection $group, string $window): array {
                [$opens, $closes] = explode('-', $window);

                $days = $group
                    ->pluck('day_of_week')
                    ->unique()
                    ->sort()
                    ->map(fn (int $day): string => self::SCHEMA_DAYS[$day])
                    ->values()
                    ->all();

                return [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $days,
                    'opens' => $opens,
                    'closes' => $closes,
                ];
            })
            ->values()
            ->all();
    }
}
