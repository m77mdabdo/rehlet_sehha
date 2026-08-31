<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

/**
 * ONE ARTICLE, ONE FILE.
 *
 * These used to live in a single array inside PostSeeder. At twelve short
 * drafts that was merely long; at fourteen full articles in two languages it
 * would be a six-thousand-line file in which nobody could find anything, and
 * every edit to one article would show up in a diff alongside thirteen others.
 *
 * WHAT EACH FILE OWES:
 *
 *   slug, category, tags, cover  — where the piece sits on the site
 *   title, excerpt               — both locales, always
 *   body                         — both locales, 1,200–1,800 words each
 *   citations                    — every named body the article reports
 *
 * BODY FORMAT. Plain text. The column is rendered as text so that an editor
 * pasting from a word processor cannot inject markup into a medical page.
 * Four conventions carry everything:
 *
 *   ## A heading             → <h2>
 *   CLINICAL_INPUT: …        → a directed recommendation, left for the clinician
 *   PRACTITIONER_VOICE: …    → a first-person observation, left for HER
 *   [[booking|احجزي]]        → an internal link; see App\Support\ArticleBody
 *
 * THE TWO MARKERS ARE NOT INTERCHANGEABLE. CLINICAL_INPUT is a gap in the
 * medicine — a quantity, a target, an instruction aimed at the reader.
 * PRACTITIONER_VOICE is a gap in the authorship: "what I see in patients
 * who…", a sentence that is a claim about one clinician's own experience and
 * cannot honestly be written by anybody else. Post::booted() refuses to
 * publish an article containing either.
 *
 * WHAT IS WRITTEN RATHER THAN MARKED. Everything attributable to a published
 * source: what the ADA recommends, how insulin resistance develops, what a
 * systematic review found, why a myth spreads and what the evidence says
 * instead. Reporting a named body's recommendation is evidence, not advice,
 * and it does not need a clinician to write it — it needs a citation, which
 * is what the `citations` key carries.
 *
 * CITATIONS. Organisation, title, year. No DOI, no volume, no page numbers —
 * those are the fields a draft written from memory invents most convincingly.
 * Each carries a confidence and a note saying which claim it supports, and
 * none of them publish until somebody with library access has opened the
 * document. See the citations migration and docs/content/citations-to-verify.md.
 */
abstract class ArticleDefinition
{
    /**
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    /**
     * Every article class, in the order they appear on the site's index.
     *
     * @return list<class-string<self>>
     */
    public static function all(): array
    {
        return [
            WhyWeQuitInWeekThree::class,
            WhatTheScaleDoesNotSay::class,
            WhatFixYourDietActuallyMeans::class,
            DietitianVersusDownloadablePlan::class,
            NormalResultsStillTired::class,
            WhatToBringToAFirstAppointment::class,
            PcosAndFoodJudgingAClaim::class,
            QuestionsToAskAboutHormonesAndFood::class,
            PregnancyEatingMyths::class,
            FeedingAndEatingRecurringQuestions::class,
            TheChildWhoWillNotEat::class,
            EatingAroundTraining::class,
            PostpartumNutrition::class,
            DiabetesInWomen::class,
        ];
    }
}
