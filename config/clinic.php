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
