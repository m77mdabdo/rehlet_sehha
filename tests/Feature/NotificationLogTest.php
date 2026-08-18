<?php

declare(strict_types=1);

use App\Models\NotificationLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

it('stores the recipient as ciphertext but reads it back as plaintext', function (string $recipient) {
    $log = NotificationLog::factory()->create(['recipient' => $recipient]);

    $raw = DB::table('notification_logs')->where('id', $log->id)->value('recipient');

    expect($raw)->not->toBe($recipient)
        ->and(str_contains((string) $raw, $recipient))->toBeFalse();

    $envelope = json_decode(base64_decode((string) $raw, true) ?: '', true);
    expect($envelope)->toBeArray()
        ->and($envelope)->toHaveKeys(['iv', 'value', 'mac'])
        ->and(Crypt::decryptString((string) $raw))->toBe($recipient);

    expect($log->fresh()?->recipient)->toBe($recipient);
})->with([
    'mona@example.com',
    '+201012345678',
    // A long but perfectly legal address: this is why the column is TEXT and
    // not VARCHAR(255) — encrypted it exceeds 300 bytes and would truncate.
    'a-very-long-but-entirely-legal-address-for-a-patient@a-long-domain-name.example.com',
]);

it('leaves the operational columns queryable', function () {
    // channel, template and status carry no personal data and are what the
    // clinic actually filters on, so they stay in the clear.
    $log = NotificationLog::factory()->create([
        'template' => 'appointment.reminder',
        'status' => 'sent',
    ]);

    $row = DB::table('notification_logs')->where('id', $log->id)->first();

    expect($row?->template)->toBe('appointment.reminder')
        ->and($row?->status)->toBe('sent');
});

it('prunes logs older than the retention window and keeps newer ones', function () {
    config(['clinic.notification_log_retention_days' => 90]);

    $ancient = NotificationLog::factory()->create();
    $ancient->forceFill(['created_at' => Carbon::now()->subDays(120)])->saveQuietly();

    $borderline = NotificationLog::factory()->create();
    $borderline->forceFill(['created_at' => Carbon::now()->subDays(91)])->saveQuietly();

    $recent = NotificationLog::factory()->create();
    $recent->forceFill(['created_at' => Carbon::now()->subDays(30)])->saveQuietly();

    $today = NotificationLog::factory()->create();

    $this->artisan('model:prune', ['--model' => [NotificationLog::class]])->assertSuccessful();

    $this->assertDatabaseMissing('notification_logs', ['id' => $ancient->id]);
    $this->assertDatabaseMissing('notification_logs', ['id' => $borderline->id]);
    $this->assertDatabaseHas('notification_logs', ['id' => $recent->id]);
    $this->assertDatabaseHas('notification_logs', ['id' => $today->id]);

    expect(NotificationLog::count())->toBe(2);
});

it('honours a configured retention window', function () {
    config(['clinic.notification_log_retention_days' => 7]);

    $old = NotificationLog::factory()->create();
    $old->forceFill(['created_at' => Carbon::now()->subDays(10)])->saveQuietly();

    $new = NotificationLog::factory()->create();
    $new->forceFill(['created_at' => Carbon::now()->subDays(3)])->saveQuietly();

    $this->artisan('model:prune', ['--model' => [NotificationLog::class]])->assertSuccessful();

    $this->assertDatabaseMissing('notification_logs', ['id' => $old->id]);
    $this->assertDatabaseHas('notification_logs', ['id' => $new->id]);
});

it('reports the prunable query it will run', function () {
    config(['clinic.notification_log_retention_days' => 90]);

    $sql = (new NotificationLog)->prunable()->toSql();

    expect($sql)->toContain('created_at');
});
