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
    | Booking Rules
    |--------------------------------------------------------------------------
    |
    | Every number the availability engine uses. Nothing here is duplicated in
    | code: App\Services\Availability\AvailabilityEngine reads all of it, so
    | changing the clinic's policy is a config edit and a deploy, not a patch.
    |
    */

    'booking' => [

        /*
         * How far ahead of now the earliest bookable slot sits.
         *
         * Protects the practitioner from a booking landing twenty minutes
         * before it starts, which nobody sees in time to prepare for.
         */
        'lead_time_hours' => (int) env('CLINIC_LEAD_TIME_HOURS', 2),

        /*
         * How far into the future the calendar opens.
         *
         * Longer is not better: a schedule published six months out is a
         * schedule the clinic cannot change without ringing people up.
         */
        'horizon_days' => (int) env('CLINIC_HORIZON_DAYS', 30),

        /*
         * Dead time after every appointment — notes, a break, overrun.
         *
         * Applied on BOTH sides when testing a candidate slot against an
         * existing appointment, and required to fit inside the working window
         * along with the service itself. A 45-minute service therefore needs
         * 60 minutes of room before closing time.
         */
        'buffer_minutes' => (int) env('CLINIC_BUFFER_MINUTES', 15),

        /*
         * How close to the appointment a patient may still cancel or move it.
         *
         * FLAGGED, as requested: one hour is almost certainly lost revenue. A
         * slot released at 09:00 for a 10:00 appointment will not be rebooked
         * — nobody is browsing a clinic calendar with an hour's notice — so
         * the practice eats the gap. Four to six hours is the usual choice,
         * and gives the clinic a realistic chance to offer the slot to someone
         * on a waiting list. Set here as one hour on instruction; it is a
         * config change whenever the clinic wants it.
         */
        'reschedule_min_hours' => (int) env('CLINIC_RESCHEDULE_MIN_HOURS', 1),

        /*
         * Which appointment modes may be BOOKED right now.
         *
         * AppointmentMode::Clinic deliberately still exists in the enum and is
         * NOT deleted. The clinic will very likely offer in-person visits
         * later, and removing the case now would mean a migration plus a data
         * backfill to bring it back — while any historical row carrying
         * mode='clinic' would immediately fail to cast and take down every
         * page that renders it.
         *
         * So the enum is the set of modes that have EVER been valid, and this
         * list is the set that is SELECTABLE TODAY. Input validation reads this
         * list; rendering reads the enum. An existing clinic-mode appointment
         * keeps displaying correctly while no new one can be created.
         */
        'modes' => ['online'],

        /*
         * The shortest time in which a human could plausibly complete step 3.
         *
         * Step 3 asks for a name, a phone number, a goal, any medications and
         * conditions, and requires reading a consent notice. Six seconds is
         * generous for a person and impossible for a script that fills the
         * form and posts it in one pass.
         *
         * Paired with a honeypot field rather than used alone: timing catches
         * the fast automated case, the honeypot catches the patient one, and
         * neither asks a real patient to solve a puzzle. There is deliberately
         * no CAPTCHA — it would be a third-party script on a medical form, and
         * it fails hardest for exactly the visitors this clinic serves.
         */
        'minimum_fill_seconds' => (int) env('CLINIC_MINIMUM_FILL_SECONDS', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Session
    |--------------------------------------------------------------------------
    |
    | How many minutes of inactivity before a staff session is dropped.
    |
    | Deliberately shorter than the public SESSION_LIFETIME. The panel is used
    | on a shared clinic computer that sits unattended between patients, and
    | every screen on it shows somebody's medical history. A patient's own
    | session on the public site protects one appointment; this one protects
    | all of them.
    |
    | Enforced per request by App\Http\Middleware\ExpireAdminSession, because
    | Laravel's session lifetime is global and cannot differ per route group.
    |
    */

    'admin_session_timeout_minutes' => (int) env('CLINIC_ADMIN_SESSION_TIMEOUT_MINUTES', 30),

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
        /*
         * NO ADDRESS. THE PRACTICE IS ONLINE AND HAS NO PREMISES.
         *
         * This is null rather than deleted so the reason is visible: an
         * address here would put a place on the contact page, in the footer
         * and in the structured data that a patient could travel to and find
         * nothing at. Contact::address() returns null and every component that
         * renders it already renders nothing for an unset value.
         *
         * areaServed below is what replaces it — where she practises, not
         * where you go.
         */
        'address' => null,

        // Where consultations are actually available. Not a building.
        'area_served' => 'EG',
    ],

    /*
    |--------------------------------------------------------------------------
    | Headline Figures
    |--------------------------------------------------------------------------
    |
    | The four numbers in the stats band on the homepage. They live here rather
    | than in a Blade file or a translation string for two reasons: they are
    | facts about the clinic, not interface copy, so they must read identically
    | in both languages; and they are the kind of number someone updates once a
    | year, which is exactly the kind nobody can ever find when it is inlined in
    | markup.
    |
    | `cases` and `years` are rendered with a "+" suffix by the view — they are
    | deliberately approximate, and writing "500+" here would make the value
    | unusable as a number the day anything wants to compare or sum it.
    |
    | `rating` is out of 5 and formatted to one decimal at render time.
    |
    | Nothing here is a clinical outcome. There is no average weight lost, no
    | success rate, no before/after statistic — see the hero case card for why
    | that is a standing rule on this site rather than an omission.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | The practitioner
    |--------------------------------------------------------------------------
    |
    | HER ACTUAL RECORD, FROM HER CERTIFICATES. Single source of truth: no page
    | states a qualification, a licensing body or a number that is not here,
    | and CredentialsTest fails the build if one appears.
    |
    | This replaced fabricated placeholder data. What was there before claimed
    | a different university, a master's degree she does not hold, and the
    | wrong syndicate — she is licensed by نقابة المهن الزراعية, the
    | agricultural professions syndicate, which is the correct body for a
    | clinical nutrition specialist holding an agricultural sciences degree in
    | Egypt. Naming the medical syndicate instead would be claiming a
    | registration she does not have, in public, under her own name.
    |
    | EVERY FIGURE HERE MUST BE EVIDENCED. Each carries a note saying what the
    | evidence is. A number on a clinic's homepage is a claim a patient may
    | act on, and one nobody can trace is worse than no number at all.
    */
    'practitioner' => [
        'name_ar' => 'رنا محمد أحمد سالم',

        // Evidence: syndicate licence card.
        /*
         * Her name TRANSLITERATED for English pages, not translated — a person
         * has one name, and users.name is deliberately not a translatable
         * column for that reason.
         *
         * The English article byline was rendering "Clinically reviewed by
         * د. رنا سالم", an Arabic name mid-sentence in a Latin paragraph. The
         * fix is a second spelling of the same name, not a second name.
         */
        'display_name_en' => 'Dr Rana Salem',

        'title_ar' => 'أخصائية تغذية إكلينيكية',
        'title_en' => 'Clinical Nutrition Specialist',

        // Evidence: degree certificate.
        'degree_ar' => 'بكالوريوس العلوم الزراعية — جامعة المنصورة، 2025',

        /*
         * The licence. A verifiable membership number is a far stronger trust
         * signal than any adjective, which is why it is displayed rather than
         * summarised — a patient can check it against the syndicate register.
         */
        'licence_body_ar' => 'نقابة المهن الزراعية',
        'licence_number' => '949728',
        'licence_year' => 2025,

        // Evidence: practice history. Counted from her second undergraduate
        // year, when supervised practice began — not from graduation.
        'years_practising' => 4,

        // Evidence: training logs and her own caseload, combined. Training,
        // supervised practice and independent follow-up.
        'cases_followed' => 1000,

        // Evidence: the two hospital programmes below, 150 hours each.
        'clinical_training_hours' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Clinical training
    |--------------------------------------------------------------------------
    |
    | In the order it happened. Two NAMED university hospitals is the strongest
    | fact on this list, and the reason the about page shows this as a timeline
    | rather than a paragraph: an institution a patient can look up carries
    | weight that "extensive training" never will.
    */
    'training' => [
        [
            /*
             * VERIFIED AGAINST THE CERTIFICATE. DO NOT "CORRECT" THIS.
             *
             * «معهد وأمراض الكبد المصري» is an odd construction and reads like
             * a typo. It is not. It is the entity's registered name, matching
             * "Egyptian Liver Research Institute and Hospital" as printed on
             * the certificate.
             *
             * This is a credential published under a licensed practitioner's
             * name. Smoothing the grammar would replace a verified fact with a
             * plausible invention — the same failure as the master's degree
             * and the medical syndicate that this file exists to undo.
             */
            'institution_ar' => 'معهد وأمراض الكبد المصري، جامعة المنصورة',
            'programme_ar' => 'برنامج دايتيتيك',
            'hours' => 150,
            'year' => null,
        ],
        [
            'institution_ar' => 'مستشفى دمياط التخصصي',
            'programme_ar' => 'تدريب دايتيتيك',
            'hours' => 150,
            'year' => 2023,
        ],
        [
            'institution_ar' => 'مبادئ التغذية الإكلينيكية',
            'programme_ar' => 'دورة تدريبية',
            'hours' => 12,
            'year' => 2022,
        ],
        [
            'institution_ar' => 'مركز تدريب نقابة المهن الزراعية',
            'programme_ar' => 'برنامج أخصائي تغذية',
            'hours' => 45,
            'year' => null,
        ],
        [
            'institution_ar' => 'مؤتمر Nutrition Specialist Professional، المنصورة',
            'programme_ar' => 'حضور مؤتمر',
            'hours' => null,
            'year' => 2023,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consultation platforms
    |--------------------------------------------------------------------------
    |
    | The practice is ONLINE. There is no clinic address to visit, so the
    | question a patient actually has is "on what?" — and the answer belongs in
    | config rather than in prose, so the contact page and the schema cannot
    | disagree about it.
    */
    'platforms' => ['zoom', 'google_meet', 'whatsapp_video'],

    /*
    |--------------------------------------------------------------------------
    | The headline figures
    |--------------------------------------------------------------------------
    |
    | POINTERS, NOT VALUES. Each entry names a key under `practitioner` or a
    | count derived elsewhere, so the strip on the homepage cannot drift away
    | from the record on the about page — there is one number and two places
    | that read it.
    |
    | THERE IS NO RATING HERE ANY MORE. The 4.9 that used to sit in this block
    | was invented. A rating is now computed from real approved reviews and is
    | only displayed once there are enough of them to mean anything; see
    | App\Support\Reviews.
    */
    'stats' => [
        'years' => 'practitioner.years_practising',
        'cases' => 'practitioner.cases_followed',
        'training_hours' => 'practitioner.clinical_training_hours',
        'support_days' => 'support_days',
    ],

    // Evidence: the working_hours table — six days with an active schedule.
    'support_days' => (int) env('CLINIC_STAT_SUPPORT_DAYS', 6),

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
