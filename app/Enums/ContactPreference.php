<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How the clinic can actually reach this patient.
 *
 * DERIVED, NEVER STORED. There is no contact_preference column and there
 * should not be one: the answer is a function of whether the patient record
 * carries an email address, and that address changes — she can add one from
 * the confirmation screen minutes after booking, or correct a typo in it a
 * week later. A stored copy would be wrong from the moment she did, and the
 * failure mode is the worst kind: a receptionist ringing a patient who has
 * been receiving reminders all along, or worse, NOT ringing one who never got
 * any because a stale column said she had email.
 *
 * Computing it costs one property read on a relation the schedule already
 * eager-loads. Storing it would cost a column, a backfill, and a
 * synchronisation bug waiting to happen.
 */
enum ContactPreference: string
{
    /**
     * We have an email address. Confirmation, reminders and the manage link
     * all reach her without anyone doing anything.
     */
    case Email = 'email';

    /**
     * A phone number and nothing else.
     *
     * She receives NOTHING automatically — no confirmation, no 24-hour or
     * 1-hour reminder, and no link to cancel or reschedule. The clinic has to
     * telephone her, which is why the daily schedule prints these patients as
     * a call list rather than leaving the gap to be noticed.
     */
    case PhoneOnly = 'phone_only';

    public function reachesElectronically(): bool
    {
        return $this === self::Email;
    }

    public function label(): string
    {
        return match ($this) {
            self::Email => __('booking.contact_preference.email'),
            self::PhoneOnly => __('booking.contact_preference.phone_only'),
        };
    }
}
