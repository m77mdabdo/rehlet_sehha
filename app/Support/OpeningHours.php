<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\WorkingHour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The opening hours, in words, DERIVED FROM working_hours.
 *
 * The footer used to carry a hand-written string — 'من السبت للخميس، ١٠ص – ٨م'
 * — sitting a few lines under a comment claiming the hours were "built from
 * config and the working_hours rows rather than written by hand, so the
 * clinic's opening hours cannot be right on the page and wrong in the
 * structured data". Only the JSON-LD was built that way. The line beside it
 * was typed.
 *
 * That was harmless for as long as nobody could change the schedule, which was
 * true only because there was no admin screen for it. The moment one existed,
 * Saturday moved to 18:00, the structured data followed, and the footer went on
 * telling patients 8pm.
 *
 * WHY A SUMMARY RATHER THAN SEVEN LINES. A clinic that works the same hours
 * six days a week should say so in one line; a footer listing seven identical
 * rows is worse for the reader than the sentence it replaced. So consecutive
 * days sharing the same window collapse into a range, and only a genuinely
 * irregular week produces several lines.
 */
final class OpeningHours
{
    /**
     * The week as it is worked here: Saturday first, Friday last.
     *
     * Not Carbon's numbering, which would open on Sunday and put the weekend
     * in the middle of the list.
     *
     * @var list<int>
     */
    private const WEEK = [6, 0, 1, 2, 3, 4, 5];

    /**
     * One line per distinct run of days, plus a line for the closed days.
     *
     * @return list<string>
     */
    public static function summary(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $windows = self::windows();

        $lines = [];
        $closed = [];

        foreach (self::runs($windows) as $run) {
            if ($run['window'] === null) {
                $closed = array_merge($closed, $run['days']);

                continue;
            }

            $lines[] = self::describe($run['days'], $run['window'], $locale);
        }

        if ($closed !== []) {
            $lines[] = __('footer.closed_on', [
                'days' => self::joinDays($closed, $locale),
            ], $locale);
        }

        return $lines;
    }

    /**
     * day_of_week => "H:i-H:i", or absent when the clinic is shut that day.
     *
     * Rows are per practitioner, so a day with two clinicians working the same
     * window yields two identical rows; they are collapsed here. Where they
     * genuinely differ the widest span is taken, because that is what the
     * clinic is open, which is the question a footer answers.
     *
     * @return array<int, string>
     */
    private static function windows(): array
    {
        $byDay = [];

        /** @var Collection<int, WorkingHour> $hours */
        $hours = PublicContent::openingHours();

        foreach ($hours as $hour) {
            $day = $hour->day_of_week;
            $open = substr((string) $hour->start_time, 0, 5);
            $close = substr((string) $hour->end_time, 0, 5);

            if (! isset($byDay[$day])) {
                $byDay[$day] = [$open, $close];

                continue;
            }

            $byDay[$day][0] = min($byDay[$day][0], $open);
            $byDay[$day][1] = max($byDay[$day][1], $close);
        }

        return array_map(
            static fn (array $span): string => $span[0].'-'.$span[1],
            $byDay,
        );
    }

    /**
     * Walk the week and group consecutive days that share a window.
     *
     * @param  array<int, string>  $windows
     * @return list<array{days: list<int>, window: string|null}>
     */
    private static function runs(array $windows): array
    {
        $runs = [];

        foreach (self::WEEK as $day) {
            $window = $windows[$day] ?? null;
            $last = count($runs) - 1;

            if ($last >= 0 && $runs[$last]['window'] === $window) {
                $runs[$last]['days'][] = $day;

                continue;
            }

            $runs[] = ['days' => [$day], 'window' => $window];
        }

        return $runs;
    }

    /**
     * @param  list<int>  $days
     */
    private static function describe(array $days, string $window, string $locale): string
    {
        [$open, $close] = explode('-', $window);

        $key = count($days) > 2 ? 'footer.hours_range' : 'footer.hours_days';

        return __($key, [
            'days' => count($days) > 2
                ? __('footer.day_range', [
                    'from' => self::dayName($days[0], $locale),
                    'to' => self::dayNameTo($days[count($days) - 1], $locale),
                ], $locale)
                : self::joinDays($days, $locale),
            'open' => self::time($open, $locale),
            'close' => self::time($close, $locale),
        ], $locale);
    }

    /**
     * @param  list<int>  $days
     */
    private static function joinDays(array $days, string $locale): string
    {
        $names = array_map(static fn (int $d): string => self::dayName($d, $locale), $days);

        return implode(__('footer.day_separator', [], $locale), $names);
    }

    private static function dayName(int $day, string $locale): string
    {
        return (string) __('footer.days.'.$day, [], $locale);
    }

    /**
     * The day as it appears at the END of a range.
     *
     * Arabic contracts لِ + الخميس into للخميس; gluing a prefix onto the plain
     * name produces لـالخميس, which is not a word.
     */
    private static function dayNameTo(int $day, string $locale): string
    {
        return (string) __('footer.days_to.'.$day, [], $locale);
    }

    /**
     * "10:00" as the footer has always shown it: ١٠ص in Arabic, 10am in English.
     *
     * The Arabic-Indic digits were previously baked into a hand-typed string.
     * They are produced here instead, so the presentation survives the hours
     * becoming data.
     */
    private static function time(string $time, string $locale): string
    {
        $at = Carbon::createFromFormat('H:i', $time);

        $hour = (int) $at->format('g');
        $minute = (int) $at->format('i');

        $clock = $minute === 0 ? (string) $hour : $hour.':'.$at->format('i');

        $suffix = __('footer.'.($at->format('a') === 'am' ? 'am' : 'pm'), [], $locale);

        if ($locale === 'ar') {
            $clock = strtr($clock, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
                '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
        }

        return $clock.$suffix;
    }
}
