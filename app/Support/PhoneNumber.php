<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Egyptian mobile numbers, in and out.
 *
 * One place, because a phone number is the patient's identity in this system —
 * Patient::findOrCreateByPhone() looks people up by it, and the column carries
 * a UNIQUE index. Two spellings of the same number are two patient files, two
 * medical histories, and a clinician reading half a story.
 *
 * So normalisation is not cosmetic here. "01012345678", "+201012345678" and
 * "0020 101 234 5678" are the same human being, and the database has to agree.
 *
 * Everything is stored E.164 (+20…), which is what Contact:: already uses for
 * the clinic's own number and what every messaging API expects.
 */
final class PhoneNumber
{
    /**
     * The national form: 01, then the operator digit, then eight more.
     *
     * 010 Vodafone, 011 Etisalat, 012 Orange, 015 WE. There is deliberately no
     * 013/014/016-19 — those prefixes are not issued, and accepting them would
     * mean storing numbers that can never receive the confirmation.
     */
    private const NATIONAL_PATTERN = '/^01[0125][0-9]{8}$/';

    /**
     * Reduce any accepted spelling to the national eleven-digit form, or null
     * if it is not an Egyptian mobile number at all.
     *
     * Accepted on input:
     *   01012345678        national
     *   +201012345678      E.164
     *   00201012345678     international prefix as dialled from Egypt
     *   with spaces, dashes, brackets or Arabic-Indic digits anywhere
     *
     * Arabic-Indic numerals are folded first. A patient typing on an Arabic
     * keyboard may well produce ٠١٠…, and rejecting that would be rejecting
     * the number for being written in the site's primary language.
     */
    public static function national(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $digits = self::digitsOnly($input);

        if ($digits === '') {
            return null;
        }

        /*
         * Strip the country code in either spelling. Order matters: 0020 has
         * to be tried before 20, or the leading 00 would be left behind.
         *
         * E.164 drops the national trunk prefix, so +201012345678 carries
         * 1012345678 and the leading 0 has to be PUT BACK to reach the
         * national form. Both spellings are tried because people do write the
         * country code and the trunk zero together (+2001012345678), and
         * refusing that would be refusing a number that is merely
         * over-specified.
         *
         * Whatever comes out is still held to the national pattern, so a
         * string that merely happens to start with 20 cannot be mangled into
         * acceptance.
         */
        foreach (['0020', '20'] as $prefix) {
            if (! str_starts_with($digits, $prefix) || strlen($digits) <= strlen($prefix)) {
                continue;
            }

            $rest = substr($digits, strlen($prefix));

            foreach ([$rest, '0'.$rest] as $candidate) {
                if (preg_match(self::NATIONAL_PATTERN, $candidate) === 1) {
                    return $candidate;
                }
            }
        }

        return preg_match(self::NATIONAL_PATTERN, $digits) === 1 ? $digits : null;
    }

    /**
     * The storage form: +20 followed by the number without its leading zero.
     */
    public static function e164(?string $input): ?string
    {
        $national = self::national($input);

        return $national === null ? null : '+20'.substr($national, 1);
    }

    public static function isValid(?string $input): bool
    {
        return self::national($input) !== null;
    }

    /**
     * How the number is shown back to a patient: their own national spelling,
     * grouped. Never the E.164 form — nobody in Egypt recognises their own
     * number as +20…, and a confirmation screen showing one invites a call to
     * the clinic asking whose number it is.
     */
    public static function forDisplay(?string $input): ?string
    {
        $national = self::national($input);

        if ($national === null) {
            return null;
        }

        return substr($national, 0, 4).' '.substr($national, 4, 3).' '.substr($national, 7);
    }

    /**
     * Fold Arabic-Indic and Eastern Arabic-Indic digits to ASCII, then discard
     * everything that is not a digit.
     */
    private static function digitsOnly(string $input): string
    {
        $folded = strtr($input, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        return preg_replace('/\D+/', '', $folded) ?? '';
    }
}
