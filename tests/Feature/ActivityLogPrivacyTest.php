<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Models\ActivityLog;
use App\Models\IntakeForm;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Support\Config;

/**
 * Read everything the audit trail persisted for a subject, as raw text.
 *
 * Deliberately reads via the query builder and concatenates BOTH json columns.
 * activity_log has two of them — `attribute_changes` (where model changes
 * actually land in activitylog v5) and `properties` — so asserting on only one
 * would pass for the wrong reason.
 */
function auditTrailText(object $subject): string
{
    return DB::table('activity_log')
        ->where('subject_type', $subject->getMorphClass())
        ->where('subject_id', $subject->getKey())
        ->get()
        ->map(fn (object $row): string => ($row->description ?? '')
            .' '.($row->attribute_changes ?? '')
            .' '.($row->properties ?? ''))
        ->implode(' ');
}

it('records that a phone number changed without recording the number', function () {
    $patient = Patient::factory()->create(['phone' => '+201011111111']);

    $patient->update(['phone' => '+201022222222']);

    $trail = auditTrailText($patient);

    expect($trail)->not->toContain('+201011111111')
        ->and($trail)->not->toContain('+201022222222');

    $entry = ActivityLog::query()
        ->where('subject_type', $patient->getMorphClass())
        ->where('subject_id', $patient->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    // The change is still on the record — just not its content.
    expect($entry->attribute_changes?->get('redacted'))->toBe(['phone']);
});

it('records that an email changed without recording the address', function () {
    $patient = Patient::factory()->create(['email' => 'before@example.com']);

    $patient->update(['email' => 'after@example.com']);

    $trail = auditTrailText($patient);

    expect($trail)->not->toContain('before@example.com')
        ->and($trail)->not->toContain('after@example.com')
        ->and($trail)->toContain('email');
});

it('redacts every confidential attribute changed in one update', function () {
    $patient = Patient::factory()->create([
        'phone' => '+201011111111',
        'email' => 'before@example.com',
        'birth_date' => '1990-01-01',
        'notes' => 'ملاحظة قديمة',
    ]);

    $patient->update([
        'phone' => '+201022222222',
        'email' => 'after@example.com',
        'birth_date' => '1991-02-02',
        'notes' => 'ملاحظة جديدة',
    ]);

    $trail = auditTrailText($patient);

    foreach (['+201011111111', '+201022222222', 'before@example.com', 'after@example.com', '1990-01-01', '1991-02-02', 'ملاحظة قديمة', 'ملاحظة جديدة'] as $secret) {
        expect($trail)->not->toContain($secret);
    }

    $entry = ActivityLog::query()->where('event', 'updated')->latest('id')->firstOrFail();

    expect($entry->attribute_changes?->get('redacted'))->toBe(['phone', 'email', 'birth_date', 'notes']);
});

it('still logs the name with its values so a rename is legible', function () {
    $patient = Patient::factory()->create(['name' => 'منى عبد الرحمن']);

    $patient->update(['name' => 'منى عبد الرحمن علي']);

    $entry = ActivityLog::query()->where('event', 'updated')->latest('id')->firstOrFail();
    $changes = $entry->attribute_changes;

    expect($changes?->get('attributes')['name'] ?? null)->toBe('منى عبد الرحمن علي')
        ->and($changes?->get('old')['name'] ?? null)->toBe('منى عبد الرحمن')
        // A name-only edit touched nothing confidential.
        ->and($changes?->get('redacted'))->toBeNull();
});

it('reports only the confidential attributes that arrived with a value on create', function () {
    $patient = Patient::factory()->create([
        'phone' => '+201011111111',
        'email' => null,
        'birth_date' => null,
    ]);

    $entry = ActivityLog::query()
        ->where('subject_id', $patient->id)
        ->where('event', 'created')
        ->firstOrFail();

    // A patient booked without an email must not read as having set one.
    expect($entry->attribute_changes?->get('redacted'))->toBe(['phone']);
});

it('never writes gender into the audit trail', function () {
    $patient = Patient::factory()->create(['gender' => Gender::Female]);

    $patient->update(['gender' => Gender::Male]);

    // Clinical data with no accountability value; excluded outright.
    expect(auditTrailText($patient))->not->toContain('female')
        ->and(auditTrailText($patient))->not->toContain('male');
});

it('records that clinical notes changed without recording what they say', function () {
    $patient = Patient::factory()->create(['notes' => 'الملاحظة الأصلية']);

    $patient->update(['notes' => 'المريضة أبلغت عن دوخة متكررة بعد الوجبات']);

    $trail = auditTrailText($patient);

    // A note quietly rewritten after the fact must leave a mark — but the
    // clinical content itself never belongs in a plaintext audit table.
    expect($trail)->not->toContain('الملاحظة الأصلية')
        ->and($trail)->not->toContain('دوخة متكررة');

    $entry = ActivityLog::query()
        ->where('subject_id', $patient->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($entry->attribute_changes?->get('redacted'))->toBe(['notes']);
});

it('does not log a change to an unaudited field', function () {
    $patient = Patient::factory()->create();
    $before = ActivityLog::count();

    // gender stays out of the trail entirely: clinical data with no
    // accountability value, so not even a redacted marker.
    $patient->update(['gender' => Gender::Male]);

    expect(ActivityLog::count())->toBe($before);
});

it('keeps the intake form logging goal and consent only', function () {
    $intake = IntakeForm::factory()->create([
        'goal' => 'إنقاص الوزن',
        'medications' => 'ميتفورمين ٥٠٠ مجم',
        'conditions' => 'تكيس المبايض',
        'avoid_foods' => 'حساسية من المكسرات',
        'note' => 'ملاحظة سرية',
    ]);

    $intake->update([
        'goal' => 'ضبط سكر الدم',
        'conditions' => 'تكيس المبايض وقصور الغدة الدرقية',
        'note' => 'ملاحظة سرية محدثة',
    ]);

    $trail = auditTrailText($intake);

    // Task 1 behaviour, re-asserted against BOTH json columns this time.
    foreach (['ميتفورمين', 'تكيس المبايض', 'المكسرات', 'ملاحظة سرية'] as $clinical) {
        expect($trail)->not->toContain($clinical);
    }

    expect($trail)->toContain('ضبط سكر الدم');
});

it('uses our prunable model for every logged entry', function () {
    // Given what happened last time, assert the trait is genuinely applied
    // rather than trusting that model:prune exited zero.
    expect(config('activitylog.activity_model'))->toBe(ActivityLog::class)
        ->and(Config::activityModel())->toBe(ActivityLog::class)
        ->and(class_uses_recursive(ActivityLog::class))->toContain(Prunable::class)
        ->and(method_exists(ActivityLog::class, 'pruneAll'))->toBeTrue();

    Patient::factory()->create();

    expect(ActivityLog::query()->latest('id')->first())->toBeInstanceOf(ActivityLog::class);
});

it('prunes activity older than the retention window and keeps newer', function () {
    config(['clinic.activity_log_retention_days' => 365]);

    Patient::factory()->create();
    $entries = ActivityLog::all();
    expect($entries)->not->toBeEmpty();

    $ancient = $entries->first();
    $ancient->forceFill(['created_at' => Carbon::now()->subDays(400)])->saveQuietly();

    $borderline = ActivityLog::create(['log_name' => 'patient', 'description' => 'old']);
    $borderline->forceFill(['created_at' => Carbon::now()->subDays(366)])->saveQuietly();

    $recent = ActivityLog::create(['log_name' => 'patient', 'description' => 'recent']);
    $recent->forceFill(['created_at' => Carbon::now()->subDays(200)])->saveQuietly();

    $this->artisan('model:prune', ['--model' => [ActivityLog::class]])->assertSuccessful();

    $this->assertDatabaseMissing('activity_log', ['id' => $ancient->id]);
    $this->assertDatabaseMissing('activity_log', ['id' => $borderline->id]);
    $this->assertDatabaseHas('activity_log', ['id' => $recent->id]);
});

it('honours a configured activity retention window', function () {
    config(['clinic.activity_log_retention_days' => 30]);

    $old = ActivityLog::create(['log_name' => 'patient', 'description' => 'old']);
    $old->forceFill(['created_at' => Carbon::now()->subDays(45)])->saveQuietly();

    $new = ActivityLog::create(['log_name' => 'patient', 'description' => 'new']);
    $new->forceFill(['created_at' => Carbon::now()->subDays(10)])->saveQuietly();

    $this->artisan('model:prune', ['--model' => [ActivityLog::class]])->assertSuccessful();

    $this->assertDatabaseMissing('activity_log', ['id' => $old->id]);
    $this->assertDatabaseHas('activity_log', ['id' => $new->id]);
});

it('keeps the audit trail longer than the delivery log', function () {
    expect(config('clinic.activity_log_retention_days'))
        ->toBeGreaterThan(config('clinic.notification_log_retention_days'));
});
