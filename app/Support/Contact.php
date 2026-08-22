<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * Reads the clinic's contact details out of config/clinic.php.
 *
 * Everything here returns null when the detail is not configured, and every
 * caller is expected to render nothing at all in that case — no empty link, no
 * placeholder, no "TBD". On a clinic site a link that looks like a way to
 * reach a doctor but is not is worse than an absent one.
 *
 * The point of routing all of this through one class rather than reading
 * config() in each Blade file is the link formats: a tel: link must carry the
 * E.164 number and a wa.me link must carry the number without its plus. Those
 * are easy to get subtly wrong in a template, and a wrong phone link fails
 * silently — nobody reports the call that never connected.
 */
final class Contact
{
    public static function email(): ?string
    {
        return self::string('email');
    }

    /**
     * The E.164 number. For links and APIs — never shown to a human.
     */
    public static function phone(): ?string
    {
        return self::string('phone');
    }

    /**
     * The number as an Egyptian visitor reads it.
     *
     * Falls back to the E.164 value so a half-configured number still renders
     * something correct rather than nothing: +201004818303 is ugly next to
     * "0100 481 8303", but it is dialable, and silence is not.
     */
    public static function phoneDisplay(): ?string
    {
        return self::string('phone_display') ?? self::phone();
    }

    /**
     * href for a phone link, or null if there is no number.
     *
     * Always built from the E.164 value. The display value carries spaces, and
     * a tel: URI with spaces in it is at the mercy of whatever the visitor's
     * dialer decides to do with them.
     */
    public static function telHref(): ?string
    {
        $phone = self::phone();

        return $phone === null ? null : 'tel:'.$phone;
    }

    /**
     * href for a WhatsApp conversation, or null.
     *
     * wa.me takes the number with no leading plus and no separators. That form
     * is stored in config rather than derived here by stripping characters,
     * because stripping is a guess about what the stored format was, and the
     * failure mode is a link that looks fine and opens an empty chat.
     */
    public static function whatsappHref(): ?string
    {
        $number = self::string('whatsapp');

        return $number === null ? null : 'https://wa.me/'.$number;
    }

    /**
     * href for a WhatsApp conversation that opens with a message already typed.
     *
     * This is the whole of the clinic's WhatsApp integration, and deliberately
     * so: no Business API, no third-party gateway, no per-message cost, and
     * nothing that requires handing a patient list to a vendor. A wa.me link
     * is an ordinary hyperlink that opens the patient's own WhatsApp with text
     * prefilled. She sends it, or she does not.
     *
     * IT SENDS NOTHING BY ITSELF. Nothing in this application can originate a
     * WhatsApp message, and no interface copy or email may imply otherwise —
     * see the audit note in docs/deployment/notifications.md.
     *
     * THE PREFILLED TEXT MUST NOT CONTAIN CLINICAL INFORMATION. It ends up in
     * a URL, and a URL lands in browser history, in the address bar during a
     * screen share, in a screenshot sent to a family member, and in the
     * referrer of anything the page opens next. A booking reference is safe
     * there — it identifies an appointment to the clinic and means nothing to
     * anyone else. A goal or a condition is not.
     */
    public static function whatsappMessageHref(string $message): ?string
    {
        $number = self::string('whatsapp');

        if ($number === null) {
            return null;
        }

        // rawurlencode, not urlencode: the latter encodes a space as "+",
        // which WhatsApp renders literally as a plus sign in the message box.
        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    /**
     * The clinic's address in the given locale, falling back to the default.
     */
    public static function address(?string $locale = null): ?string
    {
        /** @var array<string, string>|string|null $address */
        $address = config('clinic.contact.address');

        if (is_string($address)) {
            return self::trimToNull($address);
        }

        if (! is_array($address)) {
            return null;
        }

        $locale ??= App::getLocale();

        $value = $address[$locale]
            ?? $address[Locales::DEFAULT]
            ?? null;

        return is_string($value) ? self::trimToNull($value) : null;
    }

    /**
     * Whether there is anything at all to put in a contact block.
     *
     * Lets a caller skip the whole section — heading included — rather than
     * rendering an empty "Get in touch" with nothing underneath it.
     */
    public static function hasAny(): bool
    {
        return self::email() !== null
            || self::phone() !== null
            || self::whatsappHref() !== null
            || self::address() !== null;
    }

    private static function string(string $key): ?string
    {
        $value = config('clinic.contact.'.$key);

        return is_string($value) ? self::trimToNull($value) : null;
    }

    /**
     * An empty string in config is a detail nobody has filled in yet, not a
     * detail whose value is "". Treat it exactly like null.
     */
    private static function trimToNull(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
