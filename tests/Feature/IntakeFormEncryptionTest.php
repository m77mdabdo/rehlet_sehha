<?php

declare(strict_types=1);

use App\Models\IntakeForm;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

it('stores the clinical fields as ciphertext but reads them back as plaintext', function (string $field, string $plaintext) {
    $intake = IntakeForm::factory()->create([$field => $plaintext]);

    $raw = DB::table('intake_forms')->where('id', $intake->id)->value($field);

    // What the database holds is not the answer the patient typed...
    expect($raw)->not->toBe($plaintext)
        ->and($raw)->toBeString()
        ->and(str_contains((string) $raw, $plaintext))->toBeFalse();

    // ...it is Laravel's encryption envelope: base64 JSON carrying iv/value/mac.
    $envelope = json_decode(base64_decode((string) $raw, true) ?: '', true);
    expect($envelope)->toBeArray()
        ->and($envelope)->toHaveKeys(['iv', 'value', 'mac'])
        ->and(Crypt::decryptString((string) $raw))->toBe($plaintext);

    // ...while the model attribute is the plaintext the clinician expects.
    expect($intake->fresh()?->{$field})->toBe($plaintext);
})->with([
    ['medications', 'ميتفورمين ٥٠٠ مجم مرتين يومياً'],
    ['conditions', 'تكيس المبايض وقصور الغدة الدرقية'],
    ['avoid_foods', 'حساسية من المكسرات'],
    ['note', 'أعمل بنظام ورديات ليلية وأحتاج خطة مرنة.'],
]);

it('leaves the non-clinical fields readable in the database', function () {
    // goal, consent_at and consent_ip are deliberately not encrypted: they
    // carry no health detail and are needed for filtering and audit.
    $intake = IntakeForm::factory()->create([
        'goal' => 'إنقاص الوزن',
        'consent_ip' => '197.54.11.20',
    ]);

    $row = DB::table('intake_forms')->where('id', $intake->id)->first();

    expect($row?->goal)->toBe('إنقاص الوزن')
        ->and($row?->consent_ip)->toBe('197.54.11.20');
});

it('produces different ciphertext for identical plaintext', function () {
    // Laravel prepends a random IV, so two patients reporting the same
    // condition do not produce matching rows an observer could correlate.
    $first = IntakeForm::factory()->create(['conditions' => 'ارتفاع ضغط الدم']);
    $second = IntakeForm::factory()->create(['conditions' => 'ارتفاع ضغط الدم']);

    $rawFirst = DB::table('intake_forms')->where('id', $first->id)->value('conditions');
    $rawSecond = DB::table('intake_forms')->where('id', $second->id)->value('conditions');

    expect($rawFirst)->not->toBe($rawSecond)
        ->and($first->fresh()?->conditions)->toBe($second->fresh()?->conditions);
});

it('keeps decrypted clinical data out of the activity log', function () {
    $intake = IntakeForm::factory()->create(['conditions' => 'حالة سرية للغاية']);

    $intake->update(['conditions' => 'حالة سرية محدثة', 'goal' => 'إنقاص الوزن']);

    // Both json columns: in activitylog v5 model changes land in
    // `attribute_changes`, not `properties`, so checking only the latter would
    // pass without proving anything.
    $logged = DB::table('activity_log')
        ->where('subject_type', $intake->getMorphClass())
        ->where('subject_id', $intake->id)
        ->get()
        ->map(fn (object $row): string => ($row->attribute_changes ?? '').' '.($row->properties ?? ''))
        ->implode(' ');

    expect($logged)->not->toContain('حالة سرية');
});
