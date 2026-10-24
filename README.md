# Rehlet Sehha — رحلة صحة

A bilingual (Arabic / English) website, online booking system and staff panel
for a clinical nutrition practice. Live at **<https://rehletsehha.com>**.

> **رحلة صحة** موقع وعيادة تغذية علاجية إلكترونية باللغتين العربية والإنجليزية:
> صفحات تعريفية ومقالات علمية مُراجَعة، حجز مواعيد أونلاين أو في العيادة، رابط
> خاص لكل مريض لإدارة موعده وبياناته، ولوحة تحكم عربية للطاقم لإدارة المواعيد
> والمرضى والمحتوى.

---

## Contents

- [What it does](#what-it-does)
- [Features](#features)
- [Technology stack](#technology-stack)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Local installation](#local-installation)
- [Environment configuration](#environment-configuration)
- [Database](#database)
- [Front-end](#front-end)
- [Tests and code quality](#tests-and-code-quality)
- [Staff panel](#staff-panel)
- [Localization](#localization)
- [Security and privacy](#security-and-privacy)
- [Deployment](#deployment)
- [Maintenance and troubleshooting](#maintenance-and-troubleshooting)
- [Documentation](#documentation)
- [Ownership and licensing](#ownership-and-licensing)

---

## What it does

Rehlet Sehha serves one practice and its practitioner. Patients read about the
services and published articles, book an appointment (online or in the clinic)
without creating an account, and receive a private link to manage that booking.
Staff work in an Arabic admin panel: they confirm and run the day's
appointments, keep patient records, and maintain the public content.

It is a single-clinic application. It is not a multi-tenant platform and has no
payment processing.

## Features

### Public website

- Arabic (default, right-to-left) and English versions of every public page:
  home, services, packages, how it works, about, FAQ, contact, privacy notice,
  specialties and articles (by category and by tag).
- Article publishing gated in the model: a post cannot be saved as published
  without a named clinical reviewer and review date, while it still contains
  open `CLINICAL_INPUT` / `PRACTITIONER_VOICE` markers, or while any of its
  citations is low-confidence or unverified.
- Homepage plate builder, featured videos (YouTube, loaded through a
  click-to-play facade on `youtube-nocookie.com`) and patient reviews.
- SEO: per-locale URLs, `sitemap.xml`, JSON-LD structured data
  (`MedicalClinic`, breadcrumbs, articles with their reviewer), Open Graph
  images and a web app manifest.

### Booking (Livewire)

- Step-by-step booking wizard: service → day → time slot → patient details and
  an intake questionnaire (goal, medical history, medications).
- Availability calculated from the practitioner's working hours, existing
  appointments, blocked slots, booking lead time, booking horizon and buffer
  between appointments. Double booking is prevented at the database level;
  see [docs/architecture/booking-concurrency.md](docs/architecture/booking-concurrency.md).
- Patients without an email address can still book: they are told beforehand
  that no email will arrive, and the confirmation screen becomes their record.
- Each appointment has a private, tokenised management link where the patient
  can cancel, reschedule, view and edit the intake form, erase it, and download
  a self-contained HTML copy of their data.
- Review invitations three days after a completed appointment, through a
  tokenised link; patients can withdraw a review they submitted.

### Notifications (email)

- Patient emails: booking confirmed, rescheduled, cancelled, reminders 24 hours
  and 1 hour before, review request.
- Clinic emails: new booking, cancellation, failed patient email, and a 07:00
  daily schedule (Cairo time) that includes a call list for patients without email.
- Each notification is recorded in a delivery log as sent, queued or
  deliberately skipped (for example, a patient with no email address).

### Staff panel (Filament, `/admin`)

- Dashboard: today's schedule, appointments awaiting confirmation, overview.
- Appointments: confirm, complete, mark no-show, cancel, reschedule; intake
  forms visible to clinical roles only.
- Patients, services, specialties, working hours, FAQ, articles, categories,
  tags, videos, reviews (moderation) and staff users.
- Clinic settings (contact details, booking rules) and the practitioner profile
  (name, titles, licence, training), editable without a deploy.

### Operations

- `/up` health endpoint reporting database, storage, cache, scheduler, queue,
  backups and deployment state.
- Encrypted daily database backups (`spatie/laravel-backup`), and an
  `APP_KEY` fingerprint check that detects a changed key before encrypted
  records become unreadable.
- Scheduled retention pruning for the delivery log and activity log.

### Implemented only in part

Stated plainly so nobody builds on an assumption:

- **WhatsApp and SMS** exist as values in `NotificationChannel`, but nothing
  sends through them. Email is the only automated channel; WhatsApp appears as
  a contact link and a "send it to yourself" action.
- **Booking source** has `phone` and `walk_in` values, but only `website` is
  ever assigned; appointments created in the panel are recorded as `website`.
- **Testimonials, plate-builder foods and blocked slots** are read by the site
  but have no admin screen; they are maintained through seeders or the database.

## Technology stack

| Layer | Technology |
|---|---|
| Language / framework | PHP 8.4+, Laravel 12 |
| Admin panel | Filament 5 |
| Interactive components | Livewire 4 |
| Database | MySQL 8 |
| Front-end | Tailwind CSS 4, Vite 7, self-hosted fonts (Readex Pro, Tajawal) |
| Roles and permissions | spatie/laravel-permission |
| Translatable content | spatie/laravel-translatable |
| Audit trail | spatie/laravel-activitylog |
| Backups | spatie/laravel-backup |
| Tests / quality | Pest 3, PHPUnit 11, Larastan (PHPStan), Laravel Pint |

Exact versions are pinned in `composer.lock` and `package-lock.json`.

## Architecture

```
app/
  Http/Controllers/       one controller per public page, plus health, sitemap, export
  Http/Middleware/        locale, security headers, HTTPS, token-URL headers, admin session expiry
  Livewire/               BookingWizard, AppointmentManager (the patient's private link)
  Services/
    Availability/         AvailabilityEngine — which slots are bookable
    Booking/              BookingService — creating and moving appointments safely
    Clinical/             ClinicalAccessLog — audit of reads of clinical records
    Notifications/        AppointmentNotifier — who is told what, and when
  Notifications/          one class per email
  Filament/               resources, pages, widgets and actions for /admin
  Policies/               authorisation for every admin resource
  Support/                structured data, settings, public content cache, locales
  Console/Commands/       clinic:* scheduled jobs and maintenance tools
routes/web.php            public routes, all under /{locale}
routes/console.php        the schedule
lang/{ar,en}/             translations
database/seeders/         roles, staff, schedule, services and published content
```

Background work runs on the `database` queue, which the scheduler drains every
minute, so the application needs only cron and no long-running worker.

## Requirements

- PHP **8.4.1 or later**, with `gd` (WebP support), `intl`, `mbstring`,
  `pdo_mysql`, `openssl`, `curl`, `fileinfo`, `zip`, `bcmath`, `exif`.
  (`composer.json` still says `^8.2`, but the locked dependencies need 8.4.1.)
- Composer 2
- MySQL 8
- Node.js 20.19+ or 22.12+ and npm (for Vite 7)

## Local installation

```bash
git clone https://github.com/m77mdabdo/rehlet_sehha.git
cd rehlet_sehha

composer install
npm install

cp .env.example .env
php artisan key:generate
```

`.env.example` is the **production** template on purpose (a test,
`EnvironmentExampleTest`, keeps it that way). Before going further, edit `.env`
for local use: see the next section.

Then create the database and run:

```bash
php artisan migrate --seed
npm run build
composer dev        # serve, queue listener, log tail and Vite, together
```

The site is at `http://127.0.0.1:8000/ar` and the panel at `/admin`.

(`composer setup` exists too, but it migrates immediately after copying
`.env.example`, before you have pointed `.env` at a local database.)

## Environment configuration

Values to change in `.env` for local development:

| Key | Local value |
|---|---|
| `APP_ENV` | `local` |
| `APP_DEBUG` | `true` |
| `APP_URL` | `http://127.0.0.1:8000` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | your local MySQL |
| `MAIL_MAILER` | `log` (emails are written to `storage/logs` instead of sent) |
| `SESSION_SECURE_COOKIE` | `false` when not serving over HTTPS |

Other keys worth knowing (see `.env.example` for the full list):

| Key | Purpose |
|---|---|
| `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `DOCTOR_EMAIL`, `DOCTOR_PASSWORD` | Staff accounts created by the seeders. If a password is not set, a random one is generated and printed **once**. |
| `CLINIC_TIMEZONE` | The clinic's local time zone (appointments are stored in UTC) |
| `CLINIC_CONTACT_EMAIL` | Where clinic alerts and the daily schedule are sent |
| `BACKUP_ARCHIVE_PASSWORD` | Encrypts backup archives. **If unset, backups are not encrypted.** |
| `BACKUP_ALERT_EMAIL` | Backup failure alerts |
| `PEXELS_API_KEY` | Only for the stock-media tools (`clinic:fetch-pexels`); not needed to run the site |

Never commit `.env`. Production's `APP_KEY` must never be regenerated; see
[docs/deployment/APP_KEY.md](docs/deployment/APP_KEY.md).

## Database

```bash
php artisan migrate            # schema
php artisan db:seed            # roles, staff, schedule, services, content
```

`DatabaseSeeder` runs every seeder in dependency order (a test fails if a new
seeder is not wired in). `DemoAppointmentSeeder` creates fake patients and
appointments **only when `APP_ENV=local`** and does nothing elsewhere.

Never run `migrate:fresh` against production.

## Front-end

```bash
npm run dev       # Vite dev server with hot reload
npm run build     # production build into public/build
```

**`public/build` is committed**, because the host does not run a Node build.
After any change to `resources/css` or `resources/js`, run `npm run build` and
commit the new hashed files together with the deletion of the old ones;
`BuiltAssetsAreCommittedTest` fails otherwise.

## Tests and code quality

```bash
php artisan test               # full suite, serially
./vendor/bin/pint --test       # code style
./vendor/bin/phpstan analyse   # static analysis
composer audit                 # known vulnerabilities in PHP dependencies
```

- Tests use the `rehlet_sehha_test` database. `phpunit.xml` overrides only
  `DB_DATABASE`, so the host, port and credentials come from `.env`.
- Some tests read the base test database directly; migrate it once in a new
  environment with `DB_DATABASE=rehlet_sehha_test php artisan migrate`. (Do not
  use `--env=testing` for this: without a `.env.testing` file it falls back to
  `.env` and migrates the development database.)
- `php artisan test --parallel` is faster but currently shows occasional
  failures that do not reproduce serially; treat the serial run as authoritative.

## Staff panel

The panel is at `/admin`, in Arabic regardless of the public site's language.

| Role | Access |
|---|---|
| `admin` | Everything, including staff users and settings. **Multi-factor authentication (authenticator app) is required.** |
| `doctor` | Everything except staff users and some deletions: appointments, patients, clinical intake forms, the schedule, content, clinic settings and the practitioner profile |
| `receptionist` | Appointments and patients, **without** clinical intake data |

Every read of a clinical intake form, and every refused attempt, is recorded
in the activity log. Staff sessions end after 30 minutes of inactivity
(`CLINIC_ADMIN_SESSION_TIMEOUT_MINUTES`).

## Localization

- Supported locales: `ar` (default, RTL) and `en`, configured in
  `config/app.php` (`supported_locales`).
- Every public URL is prefixed with the locale (`/ar/...`, `/en/...`); `/`
  redirects to the default.
- Interface strings live in `lang/ar` and `lang/en`; database content (services,
  articles, FAQ, specialties) is stored per locale with
  spatie/laravel-translatable.
- Copy that still needs the practitioner's wording is marked `TODO_COPY`.
  `php artisan clinic:verify-copy` fails while any remains; run it before a
  release (the automatic deployment does not run it for you).

## Security and privacy

This application stores health information. In summary:

- Encrypted model attributes for clinical data; an `APP_KEY` fingerprint check
  (`clinic:verify-key`, daily) to detect a key change early.
- Role-based access with policies on every admin resource; MFA for
  administrators; clinical-read audit trail; idle staff session expiry.
- Content-Security-Policy with per-request nonces, HSTS, `X-Frame-Options:
  DENY`, `nosniff`, a restrictive Permissions-Policy, HTTPS redirect, and
  `noindex` for the panel and for tokenised patient URLs.
- No analytics, tag managers or third-party scripts on public pages; fonts are
  self-hosted.
- Patients can export their data and erase their intake form from their
  private link; the export exists to provide access rights under Egypt's
  Personal Data Protection Law (Law 151 of 2020).
- Encrypted backups when `BACKUP_ARCHIVE_PASSWORD` is set; log retention windows.

Before changing anything about secrets, keys or the server, read
[docs/deployment/launch-security-checklist.md](docs/deployment/launch-security-checklist.md).

Please report security problems privately to the repository owner rather than
in a public issue.

## Deployment

Production runs on shared hosting with Git deployment from `main`.
**A push to `main` is a production deployment.**

1. The host clones `main` and copies it into the web root (it runs no build
   step of its own).
2. The scheduled `clinic:watch-deployment` command notices the new commit and
   finishes the release: maintenance mode → `composer install --no-dev` →
   `migrate --force` → config and route caches → live. A push is normally
   fully live within two minutes.
3. `/up` reports `deploy: true` once the deployed commit has been built.

The only server-side requirement besides PHP and MySQL is a cron entry running
`php artisan schedule:run` every minute. The step-by-step guide, including the
manual fallback sequence, is [docs/deployment/hostinger.md](docs/deployment/hostinger.md).

## Maintenance and troubleshooting

| Symptom | Where to look |
|---|---|
| `/up` reports `scheduler: false` | Cron is not running `schedule:run`; see [docs/deployment/cron.md](docs/deployment/cron.md) |
| `/up` reports `deploy: false` for more than a few minutes | The watcher failed or stopped; `storage/logs/laravel.log`, then run `php artisan clinic:watch-deployment --force` |
| Admin panel has no styling after a deploy | `composer install` did not run, so Filament's assets were not published |
| Every test fails with `Access denied` or `Connection refused` | `.env` database settings, or MySQL is not running |
| Emails not arriving | The delivery log in the panel, then [docs/deployment/mail-authentication.md](docs/deployment/mail-authentication.md) |
| Opening a backup archive | `php artisan clinic:unpack-backup` (standard `unzip` cannot open AES-encrypted archives) |

Useful commands:

```bash
php artisan clinic:verify-key                  # APP_KEY matches the stored fingerprint
php artisan clinic:send-review-requests --dry-run
php artisan clinic:verify-copy                 # TODO_COPY left in translations
php artisan schedule:list                      # everything the scheduler runs
```

## Documentation

| Path | Contents |
|---|---|
| [docs/deployment/](docs/deployment/) | Hosting and deployment, cron, mail authentication, `APP_KEY`, launch security checklist |
| [docs/architecture/booking-concurrency.md](docs/architecture/booking-concurrency.md) | How double booking is prevented |
| [docs/brand.md](docs/brand.md), [docs/media/photography.md](docs/media/photography.md) | Brand assets and the photography policy, including licensing |
| [docs/content/](docs/content/) | Editorial working documents for articles (generated by `clinic:export-review-docs`) |
| [docs/REHLET_SEHA_TECHNICAL_AUDIT.md](docs/REHLET_SEHA_TECHNICAL_AUDIT.md) | Technical audit, October 2026 |

## Ownership and licensing

Maintained by Mohamed Abdo ([@m77mdabdo](https://github.com/m77mdabdo)).

**No open-source licence has been granted.** The repository is publicly
visible, but no licence file is included, so all rights are reserved by the
owner. Third-party packages remain under their own licences. Stock photography
is used under the Pexels licence, with sources recorded as described in
[docs/media/photography.md](docs/media/photography.md).

Parts of this codebase were written with AI assistance; those commits carry a
`Co-Authored-By` trailer in the Git history.
