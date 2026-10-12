<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CitationConfidence;
use App\Enums\FoodGroup;
use App\Models\Citation;
use App\Models\PlateFood;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * THE TWO DOCUMENTS DR. RANA ACTUALLY WORKS FROM.
 *
 * GENERATED, NOT WRITTEN. Both of these are derived entirely from the articles
 * and their citations, and a hand-maintained copy of derived data is a copy
 * that goes stale — quietly, and in the direction that matters. A prompt list
 * missing the three questions somebody added last week is worse than no list,
 * because it looks complete.
 *
 * So: `php artisan clinic:export-review-docs` after any change to the article
 * seeder, and ArticleReviewDocsTest fails if the committed files no longer
 * match what the command would write.
 *
 * WHY TWO FILES AND NOT ONE. They are worked through by different people at
 * different times, in different places.
 *
 *   clinical-prompts.md — the clinician, between appointments, on a phone.
 *     Every entry is a QUESTION she can answer in a sentence, with enough of
 *     the surrounding article quoted that she never has to open it.
 *
 *   citations-to-verify.md — somebody with university library access, at a
 *     desk, working down a list. Every entry names the claim being made, so
 *     the question is "does this document say that" rather than the much
 *     weaker "does this document exist".
 */
class ExportReviewDocs extends Command
{
    protected $signature = 'clinic:export-review-docs
                            {--check : Fail if the committed files are out of date, and write nothing}';

    protected $description = 'Regenerate the clinical prompt list, the citation verification list and the plate nutrition sign-off sheet';

    private const PROMPTS = 'docs/content/clinical-prompts.md';

    private const CITATIONS = 'docs/content/citations-to-verify.md';

    private const NUTRITION = 'docs/content/plate-nutrition-to-verify.md';

    public function handle(): int
    {
        $posts = Post::query()->with('citations')->orderBy('id')->get();

        if ($posts->isEmpty()) {
            $this->error('No articles in the database. Run `php artisan db:seed --class=PostSeeder` first.');

            return self::FAILURE;
        }

        $files = [
            self::PROMPTS => $this->prompts($posts),
            self::CITATIONS => $this->citations($posts),
            self::NUTRITION => $this->nutrition(PlateFood::query()->orderBy('sort_order')->get()),
        ];

        $stale = [];

        foreach ($files as $path => $contents) {
            $full = base_path($path);

            if ($this->option('check')) {
                if (! is_file($full) || File::get($full) !== $contents) {
                    $stale[] = $path;
                }

                continue;
            }

            File::ensureDirectoryExists(dirname($full));
            File::put($full, $contents);

            $this->line('  wrote  '.$path);
        }

        if ($this->option('check')) {
            if ($stale !== []) {
                $this->error('Out of date: '.implode(', ', $stale));
                $this->line('  Run `php artisan clinic:export-review-docs` and commit the result.');

                return self::FAILURE;
            }

            $this->info('All three review documents are current.');

            return self::SUCCESS;
        }

        return self::SUCCESS;
    }

    /**
     * The plate builder's energy estimates, for the practitioner to sign off.
     *
     * Same discipline as the citations sheet, for the same reason: these
     * figures were drafted without internet access, from memory of what the
     * named sources say. Every one is flagged `needs_review` and shows no
     * estimate to a patient until she has checked it.
     *
     * @param  Collection<int, PlateFood>  $foods
     */
    private function nutrition($foods): string
    {
        $pending = $foods->where('needs_review', true);
        $signed = $foods->where('needs_review', false);

        $lines = [$this->nutritionHeader($foods->count(), $pending->count(), $signed->count())];

        foreach (FoodGroup::cases() as $group) {
            $inGroup = $foods->where('group', $group);

            if ($inGroup->isEmpty()) {
                continue;
            }

            $lines[] = '## '.$group->label().'  ('.$group->value.')';
            $lines[] = '';

            foreach ($inGroup as $food) {
                $ar = $food->getTranslation('name', 'ar', false);
                $en = $food->getTranslation('name', 'en', false);
                $box = $food->needs_review ? '[ ]' : '[x]';

                $lines[] = "### {$box} {$food->emoji} {$ar} — {$en}";
                $lines[] = '';
                $lines[] = '| | |';
                $lines[] = '|---|---|';
                $lines[] = '| Portion | one '.$food->unit->value.', default '.rtrim(rtrim(number_format($food->default_portion, 2), '0'), '.').' |';
                $lines[] = '| **Energy, per '.$food->unit->value.'** | **'.$food->energy_min.'–'.$food->energy_max.'** |';

                $macros = $food->macros ?? [];

                foreach (['protein', 'carbs', 'fat'] as $macro) {
                    if (isset($macros[$macro])) {
                        $lines[] = '| '.ucfirst($macro).' (g) | '.$macros[$macro][0].'–'.$macros[$macro][1].' |';
                    }
                }

                $lines[] = '| Source | '.($food->source ?? '**none — must not publish**').' |';
                $lines[] = '| Status | '.($food->needs_review
                    ? 'awaiting sign-off — **no estimate shown to patients**'
                    : 'signed off '.$food->reviewed_at?->toDateString()).' |';
                $lines[] = '';

                if (filled($food->source_note)) {
                    $lines[] = '> '.$food->source_note;
                    $lines[] = '';
                }
            }
        }

        return implode("\n", $lines);
    }

    private function nutritionHeader(int $total, int $pending, int $signed): string
    {
        return <<<MD
            # Plate nutrition — values to verify

            > **Generated file.** `php artisan clinic:export-review-docs`. Edit the values in
            > `database/seeders/PlateFoodSeeder.php`, not this document.

            {$total} foods. **{$pending} awaiting sign-off, {$signed} signed off.**

            ## Why this document exists

            The plate builder showed no numbers at all until 2026-10-12, by design and
            with a test enforcing it. The practitioner decided an honest energy estimate,
            kept secondary to the proportion feedback and free of any target, is more
            useful to her patients than silence. This sheet is the condition attached to
            that decision.

            **Every figure below was drafted without internet access, from memory of what
            USDA FoodData Central and the Egyptian food composition tables say.** That is
            a legitimate way to draft and an indefensible way to publish. A plausible
            calorie figure on a clinic's homepage, under a licensed practitioner's name,
            borrows a nutritional authority's credibility for a number that authority may
            never have published.

            So nothing here reaches a patient until it is checked. A food awaiting
            sign-off still appears on the plate and still counts towards the proportion
            feedback — it simply carries no estimate and is left out of the total. With
            every food pending, the feature behaves exactly as it did before the change.

            ## What verifying means here

            Not "is this number plausible" — it is, that is why it was easy to draft.
            The question is **does the named source actually say this, for this portion**.

            Three outcomes, all useful:

            1. **Confirmed.** Tick the box, record the edition or FDC ID in the source
               note, and set `needs_review => false` with your name against it.
            2. **The source says something different.** Replace the span with what it
               actually says. The span may get wider; that is fine and usually better.
            3. **The source does not cover this food.** Either name a different source
               or leave the food with no estimate. A food with no figure is a working
               part of this tool, not a gap.

            ## Why every value is a span and never a single figure

            The unit is a household measure — a spoon, a cup, a baladi loaf. A spoon is
            not a calibrated volume, so anything derived from one is a range before the
            food itself varies at all. `PlateFood` refuses to save a row where the
            minimum equals the maximum, so a single figure cannot enter the database even
            by accident.

            The four composite dishes — كشري، مسقعة، ملوخية، كرنب محشي — carry
            deliberately wide spans. Nobody measures the oil in molokhia or the pasta in
            koshari. Those are the rows where the span is the whole point: a
            tight-looking number for a dish nobody measures is worse than an honest one.

            ## Two foods flagged for a decision, not just a check

            - **بطاطس** spans 110–350 because boiled potatoes and chips are the same chip
              on this screen. You may want them as two foods.
            - **مكسرات** is "nuts" generally; almonds and cashews are not interchangeable
              figures. You may want to name one.

            ---

            MD;
    }

    /**
     * Every unanswered prompt, grouped by article.
     *
     * @param  Collection<int, Post>  $posts
     */
    private function prompts($posts): string
    {
        $clinical = 0;
        $voice = 0;

        $body = '';

        foreach ($posts as $post) {
            $entries = $this->promptsIn($post);

            if ($entries === []) {
                continue;
            }

            $body .= "\n---\n\n## ".$post->getTranslation('title', 'ar', false)."\n\n";
            $body .= '`'.$post->slug.'` · '.($post->category?->getTranslation('name', 'ar', false) ?? '—')."\n\n";
            $body .= '> '.$post->getTranslation('excerpt', 'ar', false)."\n\n";

            foreach ($entries as $entry) {
                $entry['marker'] === Post::CLINICAL_MARKER ? $clinical++ : $voice++;

                $label = $entry['marker'] === Post::CLINICAL_MARKER
                    ? 'CLINICAL_INPUT'
                    : 'PRACTITIONER_VOICE — **بصوتك إنتِ**';

                $body .= "### {$label}\n\n";
                $body .= "**في قسم:** {$entry['section']}\n\n";
                $body .= "**السؤال:** {$entry['ar']}\n\n";
                $body .= "*In English:* {$entry['en']}\n\n";

                if ($entry['context'] !== '') {
                    $body .= "<details>\n<summary>الفقرة اللي قبله في المقال</summary>\n\n";
                    $body .= $entry['context']."\n\n</details>\n\n";
                }
            }
        }

        return $this->promptsHeader($posts->count(), $clinical, $voice).$body;
    }

    /**
     * @return list<array{marker: string, section: string, ar: string, en: string, context: string}>
     */
    private function promptsIn(Post $post): array
    {
        $arBlocks = $this->blocks((string) $post->getTranslation('body', 'ar', false));
        $enBlocks = $this->blocks((string) $post->getTranslation('body', 'en', false));

        $arPrompts = $this->markerBlocks($arBlocks);
        $enPrompts = $this->markerBlocks($enBlocks);

        $entries = [];

        foreach ($arPrompts as $index => $prompt) {
            $entries[] = [
                'marker' => $prompt['marker'],
                'section' => $prompt['section'],
                'ar' => $prompt['text'],
                /*
                 * Paired BY POSITION, which is safe only because the article
                 * standard test asserts both languages carry the same number
                 * of prompts in the same order. If that ever stops being true
                 * this falls back to an em dash rather than to a mismatched
                 * pair, because a prompt shown beside the wrong translation is
                 * worse than one shown with none.
                 */
                'en' => $enPrompts[$index]['text'] ?? '—',
                'context' => $prompt['context'],
            ];
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function blocks(string $body): array
    {
        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\R{2,}/u', $body) ?: []
        ), fn (string $block): bool => $block !== ''));
    }

    /**
     * Marker blocks, each carrying the heading it sits under and the paragraph
     * before it — which is what turns "what do you tell her?" into a question
     * somebody can answer without opening the article.
     *
     * @param  list<string>  $blocks
     * @return list<array{marker: string, section: string, text: string, context: string}>
     */
    private function markerBlocks(array $blocks): array
    {
        $found = [];
        $section = '—';
        $previous = '';

        foreach ($blocks as $block) {
            if (str_starts_with($block, '## ')) {
                $section = trim(substr($block, 3));
                $previous = '';

                continue;
            }

            $marker = null;

            foreach (Post::markers() as $candidate) {
                if (str_starts_with($block, $candidate.':')) {
                    $marker = $candidate;
                    break;
                }
            }

            if ($marker === null) {
                $previous = $block;

                continue;
            }

            $found[] = [
                'marker' => $marker,
                'section' => $section,
                'text' => trim(substr($block, strlen($marker) + 1)),
                'context' => $previous,
            ];
        }

        return $found;
    }

    /**
     * Every citation, grouped by confidence rather than by article.
     *
     * BY CONFIDENCE ON PURPOSE. The person doing this work has finite library
     * time, and the medium-confidence entries are where it should go first —
     * those are the ones where a year or a title may be wrong. Grouping by
     * article would bury them among the certainties.
     *
     * @param  Collection<int, Post>  $posts
     */
    private function citations($posts): string
    {
        $all = $posts->flatMap(
            fn (Post $post) => $post->citations->map(fn (Citation $c): array => ['post' => $post, 'citation' => $c])
        );

        $body = $this->citationsHeader($all->count(), $posts->count());

        foreach ([CitationConfidence::Low, CitationConfidence::Medium, CitationConfidence::High] as $confidence) {
            $group = $all->filter(fn (array $row): bool => $row['citation']->confidence === $confidence);

            if ($group->isEmpty()) {
                if ($confidence === CitationConfidence::Low) {
                    $body .= "\n---\n\n## Not publishable — none\n\n"
                        ."No citation in the set is marked `low`. A source the draft could not be confident\n"
                        ."exists was left out rather than recorded, and `CitationGateTest` fails if one appears.\n";
                }

                continue;
            }

            $body .= "\n---\n\n## ".$this->groupHeading($confidence).' — '.$group->count()." to check\n\n";
            $body .= $this->groupNote($confidence)."\n";

            foreach ($group as $row) {
                /** @var Citation $citation */
                $citation = $row['citation'];
                /** @var Post $post */
                $post = $row['post'];

                $body .= "\n### ".$citation->getTranslation('organisation', 'en', false)."\n\n";
                $body .= '**'.$citation->getTranslation('title', 'en', false).'**';
                $body .= $citation->year === null ? " · *no year given*\n\n" : ' · '.$citation->year."\n\n";
                $body .= '- **Appears in:** ['.$post->getTranslation('title', 'en', false).'](../../database/seeders/Articles/) — `'.$post->slug."`\n";
                $body .= '- **Arabic rendering:** '.$citation->getTranslation('organisation', 'ar', false).'. '.$citation->getTranslation('title', 'ar', false)."\n";
                $body .= '- **What it is being cited for:** '.$citation->note."\n";
                $body .= "- **Verified:** ☐ &nbsp; **URL:** ______________________\n";
            }
        }

        return $body;
    }

    private function groupHeading(CitationConfidence $confidence): string
    {
        return match ($confidence) {
            CitationConfidence::Low => 'Not publishable',
            CitationConfidence::Medium => 'Check these first',
            CitationConfidence::High => 'Confirm and record the edition',
        };
    }

    private function groupNote(CitationConfidence $confidence): string
    {
        return match ($confidence) {
            CitationConfidence::Low => "These should not exist. Delete each one, and the sentence it supports.\n",

            CitationConfidence::Medium => "The draft is confident these documents EXIST. What may be wrong is a\n"
                ."detail — the edition year, the exact title, which of two documents from the\n"
                ."same body carries the statement, or whether the body named is really the one\n"
                ."that issued it. Two of these name no issuing body at all, deliberately,\n"
                ."because inventing one is exactly the failure this list exists to prevent.\n"
                ."\n"
                ."**Start here.** This is where the article set is most likely to be wrong.\n",

            CitationConfidence::High => "Major guidelines and position statements. The draft is confident both that\n"
                ."these exist and that they say what the article says they say.\n"
                ."\n"
                ."They still need checking, for one specific reason: **several are reissued\n"
                ."annually**. Citing the 2025 edition of a document whose 2027 edition is current\n"
                ."is the most likely way these articles date, and it is invisible from the page.\n",
        };
    }

    private function promptsHeader(int $articles, int $clinical, int $voice): string
    {
        $total = $clinical + $voice;

        return <<<MD
        # The questions only you can answer

        > **Generated file.** `php artisan clinic:export-review-docs`. Edit the articles in
        > `database/seeders/Articles/`, not this document — anything typed here is
        > overwritten on the next run.

        {$total} questions across {$articles} articles: **{$clinical} CLINICAL_INPUT** and
        **{$voice} PRACTITIONER_VOICE**. Every one is a gap left open deliberately, and no
        article can be published while any of its own remain.

        ## The two kinds, and why they are not the same

        **CLINICAL_INPUT** is a gap in the *medicine*. A directed recommendation: a
        quantity, a target, an instruction aimed at the reader — «خُدي»، «كُلي»، «اعملي».
        Everything attributable to a published source is already written into the
        article and cited; what is left here is the part that is advice rather than
        evidence, and advice belongs to the clinician who will answer for it.

        **PRACTITIONER_VOICE** is a gap in the *authorship*, and it is the more important
        of the two. It marks a first-person clinical observation — «في تجربتي»، «اللي
        بشوفه مع مرضايا»، the sentence you actually use with a patient. Nobody else can
        write these, because they are not claims about the literature. They are claims
        about what **you** have seen across your own patients, and writing one on your
        behalf would be inventing a professional memory and signing your name to it.

        They are also the sentences that make this blog yours rather than a competent
        translation of somebody else's. They are worth the wait.

        ## How to use this

        Answer in a sentence or two. Nothing here needs a paragraph, and none of it
        needs to be polished — the phrasing can be tidied afterwards, the substance
        cannot be invented.

        Each entry gives the article, the section it sits in, the question in Arabic,
        the same question in English, and — folded up — the paragraph immediately
        before it, so you can see what the reader has just been told without opening
        the article.

        Where a question does not apply, say so. "I would not give a number here" is an
        answer, and a useful one.

        MD;
    }

    private function citationsHeader(int $count, int $articles): string
    {
        return <<<MD
        # Citations to verify

        > **Generated file.** `php artisan clinic:export-review-docs`. Edit the articles in
        > `database/seeders/Articles/`, not this document.

        {$count} citations across {$articles} articles.

        ## Why this document exists

        These articles were drafted **without internet access**, from memory, and they
        report what the ADA, WHO, NICE, ESHRE, AAP and Cochrane say. That is a
        legitimate way to draft and an indefensible way to publish.

        A fabricated reference in a medical article, under a licensed practitioner's
        name, is worse than no article at all — it borrows an institution's authority to
        make a claim that institution never made. And a plausible-looking citation is
        the most convincing thing on a page, which is precisely why the uncertainty is
        recorded per reference rather than waved at in a note nobody reads.

        **No article publishes until every one of its citations is marked verified.**
        That is not a convention. `Post::assertCitationsArePublishable()` throws,
        `Citation` refuses to attach an unverified reference to a published article,
        and `scopePublished()` will not serve one even if a row reaches the table by
        some other route. `CitationGateTest` covers all four orders of operations.

        ## What verifying means here

        Not "does this document exist" — that is the easy half and the less important
        one. The question is **does this document say what the article says it says**,
        which is why every entry below names the claim it is holding up.

        Three outcomes, and all three are useful:

        1. **Confirmed.** Record the URL and the edition, and tick it.
        2. **Exists, says something different.** Say what it actually says. The
           sentence in the article changes, or goes.
        3. **Cannot be found.** Delete the citation *and the sentence it supports*.
           Softening the sentence and keeping the reference is the one outcome that
           makes things worse.

        ## What was deliberately left out

        No DOIs, no volume numbers, no page numbers, and no URLs. Those are the fields
        a draft written from memory invents most convincingly — a fabricated DOI looks
        more like evidence of checking than anything else on the page. An organisation,
        a title and a year identify a guideline unambiguously and can be checked with a
        search engine. The URL column below is for you to fill in once you have
        actually opened the document.

        Several numbers were also left out of the articles themselves for the same
        reason: protein grams per kilogram, the postpartum glucose screening interval,
        weekly activity minutes, anaemia thresholds, waist-to-height ratios. Where an
        entry below says so, the figure should be added **from the document**, not from
        anybody's memory.

        MD;
    }
}
