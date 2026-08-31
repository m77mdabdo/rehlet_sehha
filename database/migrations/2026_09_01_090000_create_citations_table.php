<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WHERE EACH CLAIM CAME FROM.
 *
 * These articles report what named bodies recommend — the ADA, WHO, NICE,
 * ESPEN, Cochrane — and a sentence like "the American Diabetes Association
 * recommends…" is only worth anything if the reader could go and check. On a
 * clinic's site, under a licensed practitioner's byline, an unverifiable
 * citation is worse than no citation: it borrows an institution's authority
 * without earning it.
 *
 * A TABLE RATHER THAN A LINE OF PROSE, for three reasons that all come down to
 * the same thing — a citation has a lifecycle, and text does not.
 *
 *   1. Each one has to be individually VERIFIED by somebody with the access to
 *      do it. That is a per-row state, not a per-article one: an article can
 *      be three-quarters checked, and the gate needs to know which quarter is
 *      outstanding.
 *   2. Each one carries a CONFIDENCE, which is a claim about the drafting
 *      process rather than about the source. It must never reach a reader, and
 *      anything living in the body eventually does.
 *   3. Guidelines are reissued. When the ADA publishes its next Standards of
 *      Care, the rows that point at it are findable in one query; the same
 *      sentence buried in fourteen article bodies is not.
 *
 * NO URL IS SEEDED, DELIBERATELY. The drafts were written without internet
 * access, so every URL would be a guess, and a guessed URL is the most
 * convincing kind of fabrication — it looks like evidence of checking. The
 * column exists for the verifier to fill in when she has actually opened the
 * document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citations', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            $table->unsignedSmallInteger('sort_order')->default(0);

            /*
             * Translatable JSON. The organisation and the document title are
             * given in both languages because the Arabic reader is the primary
             * one and "American Diabetes Association" in the middle of an
             * Arabic references list is a wall she has to climb.
             *
             * The YEAR is not translatable and not a string: it is the single
             * most useful field for finding a document again, and guidelines
             * are identified by their edition year.
             */
            $table->json('organisation');
            $table->json('title');
            $table->unsignedSmallInteger('year')->nullable();

            // Filled in at verification, by somebody who opened the document.
            $table->string('url')->nullable();

            /*
             * HOW SURE THE DRAFT WAS. Never rendered.
             *
             *   high   — a major guideline or position statement, certain it
             *            exists and certain of what it says.
             *   medium — real, but a detail may be imprecise: the year, the
             *            exact wording, which of two documents carries it.
             *   low    — should never have been written down. The publish gate
             *            refuses an article carrying one, rather than trusting
             *            anybody to notice it in a list.
             */
            $table->enum('confidence', ['high', 'medium', 'low'])->default('medium');

            /*
             * What is being cited FOR — the claim in the article this row
             * supports, and where it appears. Written for the person doing the
             * verification: "does this document say that" is answerable,
             * "is this a real document" is only half the question.
             */
            $table->text('note')->nullable();

            /*
             * Verification. Who and when, for the same reason the article's
             * clinical review is who and when rather than a boolean: when a
             * citation turns out to be wrong, the two questions are who
             * checked it and whether that was before or after the claim
             * beside it was written.
             */
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citations');
    }
};
