<?php
declare(strict_types=1);
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Post;
use App\Support\ArticleBody;
use Database\Seeders\Articles\ArticleDefinition;

$classes = ArticleDefinition::all();
$rows = [];
foreach ($classes as $class) {
    if (! class_exists($class)) { $rows[] = [class_basename($class), '-', '-', '-', '-', 'MISSING']; continue; }
    $d = (new $class)->definition();
    $r = [class_basename($class)];
    foreach (['ar', 'en'] as $loc) {
        $body = ArticleBody::plain($d['body'][$loc]);
        $body = preg_replace('/^(CLINICAL_INPUT|PRACTITIONER_VOICE):.*$/mu', '', $body);
        $words = count(preg_split('/\s+/u', trim($body)) ?: []);
        $r[] = $words;
    }
    $r[] = substr_count($d['body']['ar'], Post::CLINICAL_MARKER).'/'.substr_count($d['body']['en'], Post::CLINICAL_MARKER);
    $r[] = substr_count($d['body']['ar'], Post::PRACTITIONER_MARKER).'/'.substr_count($d['body']['en'], Post::PRACTITIONER_MARKER);
    $c = $d['citations'] ?? [];
    $byConf = [];
    foreach ($c as $cite) { $byConf[$cite['confidence']->value] = ($byConf[$cite['confidence']->value] ?? 0) + 1; }
    $r[] = count($c).' ('.($byConf['high'] ?? 0).'H '.($byConf['medium'] ?? 0).'M '.($byConf['low'] ?? 0).'L)';
    $rows[] = $r;
}
printf("%-38s %6s %6s %8s %8s  %s\n", 'ARTICLE', 'AR', 'EN', 'CLIN', 'VOICE', 'CITATIONS');
foreach ($rows as $r) {
    $flagAr = is_int($r[1]) && ($r[1] < 1200 || $r[1] > 1800) ? '!' : ' ';
    $flagEn = is_int($r[2]) && ($r[2] < 1200 || $r[2] > 1800) ? '!' : ' ';
    printf("%-38s %5s%s %5s%s %8s %8s  %s\n", $r[0], $r[1], $flagAr, $r[2], $flagEn, $r[3], $r[4], $r[5]);
}
