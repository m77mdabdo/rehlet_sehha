<?php

declare(strict_types=1);

/**
 * .env.example IS THE DEPLOY TEMPLATE, AND IT USED TO BE A LOADED GUN.
 *
 * It carried APP_ENV=local, APP_DEBUG=true and MAIL_MAILER=log. Copied to a
 * server unchanged — which is exactly what a deploy template is for — that
 * produces a site which prints stack traces containing database credentials
 * on every error page, and which confirms bookings on screen while sending
 * no email whatsoever. A patient would hold an appointment nobody told her
 * about, and the clinic would have no way to know.
 *
 * Nothing else in the suite could see it. Every other test runs against the
 * test environment's own configuration, so the file that actually reaches the
 * server was the one file nobody checked.
 *
 * The values below are asserted individually rather than as a snapshot, so a
 * failure names the specific setting that is unsafe and says why.
 */

/**
 * @return array<string, string>
 */
function envExample(): array
{
    $pairs = [];

    foreach (preg_split('/\R/', (string) file_get_contents(base_path('.env.example'))) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $m) === 1) {
            $pairs[$m[1]] = trim($m[2], "\"'");
        }
    }

    return $pairs;
}

it('ships production values in the deploy template', function (string $key, string $forbidden, string $why) {
    $env = envExample();

    expect($env)->toHaveKey($key);

    expect(strtolower($env[$key]))->not->toBe(
        strtolower($forbidden),
        ".env.example sets {$key}={$forbidden}.\n\n{$why}\n\n"
        .'This file is copied to the server. Put the local value in your own .env instead.'
    );
})->with([
    ['APP_ENV', 'local',
        'A deployed app in the local environment skips production safeguards and '
        .'advertises itself as a development site.'],
    ['APP_DEBUG', 'true',
        'Debug mode prints a stack trace on every error, and a Laravel stack trace '
        .'includes the database credentials and the application key.'],
    ['MAIL_MAILER', 'log',
        'The log mailer sends nothing. Bookings would confirm on screen while the '
        .'patient is never told, and the clinic would never find out.'],
    ['LOG_LEVEL', 'debug',
        'Debug logging writes every query and every mail payload to disk. On this '
        .'site that means clinical content in an unrotated log file.'],
    ['DEBUGBAR_ENABLED', 'true',
        'The profiler widget sits over the page and exposes queries and session data.'],
]);

it('keeps the settings the deploy actually depends on', function (string $key, string $expected, string $why) {
    $env = envExample();

    expect($env)->toHaveKey($key);
    expect($env[$key])->toBe($expected, ".env.example must set {$key}={$expected}. {$why}");
})->with([
    ['QUEUE_CONNECTION', 'database',
        'Notifications are queued and drained by scheduled queue:work; sync would '
        .'block every booking request on SMTP.'],
    ['CACHE_STORE', 'file',
        'Measured fastest on single-server shared hosting; see the note in the file.'],
    ['SESSION_DRIVER', 'database',
        'Sessions must survive the deploy that replaces the filesystem.'],
    ['SESSION_SECURE_COOKIE', 'true',
        'Without it the session cookie travels over plain http on the first request '
        .'of a session, before that browser has ever seen HSTS.'],
    ['SESSION_ENCRYPT', 'true',
        'Sessions live in the database, which is what a backup file contains.'],
    ['SESSION_HTTP_ONLY', 'true',
        'A session cookie readable by script is a session cookie an XSS can steal.'],
]);

it('commits no secret', function () {
    /*
     * The other half of making this file safe to hold production values: it
     * must carry the SHAPE of the configuration and none of the credentials.
     */
    $env = envExample();

    foreach (['APP_KEY', 'DB_PASSWORD', 'MAIL_PASSWORD', 'ADMIN_PASSWORD', 'DOCTOR_PASSWORD'] as $secret) {
        expect($env)->toHaveKey($secret);
        expect($env[$secret])->toBe('', "{$secret} has a value committed in .env.example.");
    }
});
