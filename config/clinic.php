<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clinic Operating Timezone
    |--------------------------------------------------------------------------
    |
    | The wall-clock timezone the clinic actually operates in. This is a
    | DISPLAY and SCHEDULING concern only — it is deliberately NOT the
    | application timezone (config('app.timezone'), which stays 'UTC').
    |
    | Why the separation: Egypt observes daylight saving time. Around the
    | spring-forward transition a local wall-clock hour does not exist at all,
    | and around the autumn fall-back transition a local hour happens twice.
    | If we stored appointment times as local wall-clock values we would end up
    | persisting timestamps that either correspond to no real instant, or that
    | are ambiguous between two real instants an hour apart — with nothing in
    | the stored value to disambiguate them. Either case silently corrupts
    | ordering, conflict detection and reminder scheduling.
    |
    | So: every datetime is stored in UTC. This timezone is applied when we
    | render a time to a human, and when we translate the clinic's working
    | hours ("Saturday 10:00–20:00" is a Cairo wall-clock statement) into the
    | concrete UTC instants that become bookable slots.
    |
    */

    'timezone' => env('CLINIC_TIMEZONE', 'Africa/Cairo'),

    /*
    |--------------------------------------------------------------------------
    | Notification Log Retention
    |--------------------------------------------------------------------------
    |
    | How many days of delivery logs to keep before `model:prune` deletes them.
    |
    | Each notification_logs row ties a patient contact detail to an
    | appointment. That is genuinely useful for a few weeks — it answers "did
    | the reminder actually go out?" — and is pure stored liability afterwards.
    | Ninety days comfortably covers any dispute about a missed appointment.
    |
    */

    'notification_log_retention_days' => (int) env('CLINIC_NOTIFICATION_LOG_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Activity Log Retention
    |--------------------------------------------------------------------------
    |
    | How many days of audit trail to keep before `model:prune` deletes it.
    |
    | Deliberately longer than the notification log. An audit trail answers who
    | changed a patient record, when, and what it said before — a question that
    | can genuinely arrive months after the fact, when a clinical decision is
    | reviewed. A delivery log never carries that weight.
    |
    | Not forever, though. Even with contact values redacted, this table builds
    | a timeline of every patient interaction, and records nobody has a use for
    | can only be lost or subpoenaed.
    |
    */

    'activity_log_retention_days' => (int) env('CLINIC_ACTIVITY_LOG_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Contact Details
    |--------------------------------------------------------------------------
    |
    | The single source of truth for how to reach the clinic. Nothing here is
    | ever written into a Blade file: a phone number that appears in the footer,
    | a contact block and a booking confirmation is a number that gets changed
    | in two of those three places.
    |
    | Any value here may be null, and null means the site renders NOTHING for
    | it — no empty link, no placeholder, no "TBD". A dead tel: link on a
    | clinic site is worse than no link at all: it looks like a working way to
    | reach a doctor and is not.
    |
    | The phone number is stored three times on purpose, because three
    | different consumers need three different formats and deriving one from
    | another at render time is where this goes wrong:
    |
    |   phone          E.164. What tel: links and every messaging API expect.
    |                  Never shown to a human.
    |   phone_display  What an Egyptian visitor actually reads and recognises.
    |                  Latin digits, not Arabic-Indic (٠١٠٠): people copy
    |                  numbers into dialers and contact apps, and a good many
    |                  of those fail to parse Arabic-Indic numerals on paste.
    |   whatsapp       Same number with no leading +, because wa.me rejects
    |                  the plus. Stored rather than derived: "strip the +"
    |                  looks obvious right up to the first number someone
    |                  writes as 0020, and then it silently builds a dead link.
    |
    | Wherever the number is rendered inside Arabic text it must be wrapped in
    | dir="ltr" — otherwise bidi reordering puts the + and the digit groups in
    | the wrong order, and the number displayed is not the number stored.
    |
    */

    'contact' => [
        'email' => env('CLINIC_CONTACT_EMAIL', 'info@rehletsehha.com'),

        'phone' => '+201004818303',
        'phone_display' => '0100 481 8303',
        'whatsapp' => '201004818303',

        // Translated in config rather than in lang/ because it is a fact about
        // the clinic, not a piece of interface copy — the booking confirmation
        // and the footer want the same string, in whichever language is active.
        'address' => [
            'ar' => 'المعادي، القاهرة',
            'en' => 'Maadi, Cairo',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand Colours (mirror — not the source of truth)
    |--------------------------------------------------------------------------
    |
    | resources/css/app.css owns the palette. These entries exist only for the
    | handful of places that need a literal hex in HTML because no CSS custom
    | property is allowed there:
    |
    |   <meta name="theme-color">   — read by the browser chrome before any
    |                                 stylesheet is parsed
    |   site.webmanifest            — a JSON file, with no access to CSS at all
    |
    | Keep these in step with the @theme block in app.css. There is deliberately
    | no third copy: if a component needs a colour, it uses a Tailwind token.
    |
    */

    'brand' => [
        'ink' => '#0E2E4D',
        'paper' => '#EEF3F8',
    ],

];
