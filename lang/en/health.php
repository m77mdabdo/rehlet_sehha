<?php

declare(strict_types=1);

/*
 * See the note at the top of lang/ar/health.php: this page is written for
 * whoever is at the front desk, so every failed line says what has stopped
 * working for a patient rather than which subsystem is down.
 */

return [
    'title' => 'Site status',

    'healthy' => 'Everything is working',
    'healthy_body' => 'All checks passed. There is nothing you need to do.',

    'degraded' => 'Something has stopped',
    'degraded_body' => 'The site is still open to visitors, but part of it is not running. Send this page to whoever maintains the site.',

    'ok' => 'Working',
    'failed' => 'Stopped',

    'checks' => [
        'database' => [
            'label' => 'Database',
            'ok' => 'Appointments and records are readable.',
            'failed' => 'New bookings will not be saved. Fix this first.',
        ],
        'storage' => [
            'label' => 'Disk space',
            'ok' => 'There is room and files are being written.',
            'failed' => 'The disk is full or not writable. New intake forms may not save.',
        ],
        'cache' => [
            'label' => 'Cache',
            'ok' => 'Working.',
            'failed' => 'The site still works, but more slowly than usual.',
        ],
        'scheduler' => [
            'label' => 'Scheduled tasks',
            'ok' => 'Ran a few minutes ago.',
            'failed' => 'Reminders are not going out to patients, and the daily schedule is not arriving.',
        ],
        'queue' => [
            'label' => 'Messages',
            'ok' => 'Nothing overdue.',
            'failed' => 'Messages are stuck. A patient has not received a confirmation or a reminder.',
        ],
        'backup' => [
            'label' => 'Backup',
            'ok' => 'The most recent dump was taken on time.',
            'failed' => 'There is no recent backup. Nothing is lost yet, but there is nothing to restore from.',
        ],
    ],
];
