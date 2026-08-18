<?php

declare(strict_types=1);

use App\Console\Commands\VerifyAppKey;
use App\Models\Setting;
use Database\Seeders\AppKeyFingerprintSeeder;
use Illuminate\Support\Facades\DB;

it('records the fingerprint and passes when none is stored yet', function () {
    expect(Setting::where('key', VerifyAppKey::FINGERPRINT_KEY)->exists())->toBeFalse();

    $this->artisan('clinic:verify-key')
        ->expectsOutputToContain('No APP_KEY fingerprint was stored yet')
        ->assertSuccessful();

    $stored = Setting::where('key', VerifyAppKey::FINGERPRINT_KEY)->first();

    expect($stored)->not->toBeNull()
        ->and($stored?->value['sha256'] ?? null)->toBe(hash('sha256', (string) config('app.key')));
});

it('passes when the current key matches the stored fingerprint', function () {
    $this->seed(AppKeyFingerprintSeeder::class);

    $this->artisan('clinic:verify-key')
        ->expectsOutputToContain('APP_KEY matches the key that encrypted this database')
        ->assertSuccessful();
});

it('fails loudly when the key no longer matches', function () {
    $this->seed(AppKeyFingerprintSeeder::class);

    // Simulate the classic accident: someone ran key:generate on a server that
    // already held encrypted clinical data.
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

    $this->artisan('clinic:verify-key')
        // Each expectation consumes one output line, so the headline is
        // asserted whole rather than as two separate fragments of one line.
        ->expectsOutputToContain('APP_KEY MISMATCH — ENCRYPTED CLINICAL DATA IS UNREADABLE')
        ->expectsOutputToContain('THE ONLY RECOVERY IS RESTORING THE ORIGINAL APP_KEY FROM BACKUP.')
        ->expectsOutputToContain('Do NOT run `php artisan key:generate`')
        ->expectsOutputToContain('docs/deployment/APP_KEY.md')
        ->assertFailed();
});

it('fails when the key is empty', function () {
    $this->seed(AppKeyFingerprintSeeder::class);

    config(['app.key' => '']);

    $this->artisan('clinic:verify-key')
        ->expectsOutputToContain('APP_KEY is empty')
        ->assertFailed();
});

it('does not overwrite an existing fingerprint when reseeded', function () {
    $this->seed(AppKeyFingerprintSeeder::class);

    $original = Setting::where('key', VerifyAppKey::FINGERPRINT_KEY)->first()?->value['sha256'] ?? null;

    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    $this->seed(AppKeyFingerprintSeeder::class);

    // Silently rewriting it would destroy the only evidence that the stored
    // data is no longer decryptable.
    $after = Setting::where('key', VerifyAppKey::FINGERPRINT_KEY)->first()?->value['sha256'] ?? null;

    expect($after)->toBe($original)
        ->and(Setting::where('key', VerifyAppKey::FINGERPRINT_KEY)->count())->toBe(1);
});

it('stores a hash rather than the key itself', function () {
    $this->seed(AppKeyFingerprintSeeder::class);

    $raw = DB::table('settings')
        ->where('key', VerifyAppKey::FINGERPRINT_KEY)
        ->value('value');

    // The settings table lands in every backup and every mysqldump.
    expect($raw)->not->toContain((string) config('app.key'));
});
