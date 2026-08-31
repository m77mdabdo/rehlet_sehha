<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * HOW SURE THE DRAFT WAS ABOUT A CITATION.
 *
 * This describes the DRAFTING PROCESS, not the source. A guideline does not
 * become less true because whoever wrote the sentence was working from memory;
 * what changes is how much checking is owed before it goes out under a
 * clinician's name.
 *
 * The articles on this site were drafted without internet access. That is a
 * plain fact about how they were produced, and pretending otherwise is exactly
 * how a fabricated reference ends up in a medical article — so the uncertainty
 * is recorded per citation rather than waved at in a note nobody reads.
 *
 * NEVER RENDERED. A reader seeing "medium confidence" beside a reference would
 * reasonably conclude the clinic is unsure of its own medicine. She is not the
 * audience for this field; the person with university library access is.
 */
enum CitationConfidence: string
{
    /**
     * A major guideline or position statement. Certain the document exists and
     * certain of what it says on this point.
     */
    case High = 'high';

    /**
     * Real, but a detail may be imprecise — the edition year, the exact
     * wording, or which of two documents from the same body carries the
     * statement. Verifiable in minutes by somebody with access.
     */
    case Medium = 'medium';

    /**
     * Should never have been written down.
     *
     * Kept as a value only so that the publish gate can refuse an article
     * carrying one. A rule enforced by a gate holds; a rule enforced by
     * "remember to delete these before publishing" does not.
     */
    case Low = 'low';

    public function label(string $locale = 'ar'): string
    {
        return match ($this) {
            self::High => $locale === 'ar' ? 'مؤكد' : 'Certain',
            self::Medium => $locale === 'ar' ? 'محتاج تأكيد' : 'Needs confirming',
            self::Low => $locale === 'ar' ? 'مايتنشرش' : 'Not publishable',
        };
    }

    /**
     * Whether an article carrying this citation may be published at all.
     *
     * Verification is a separate question, asked separately. A high-confidence
     * citation still has to be checked; a low-confidence one is not eligible
     * to be checked, it is eligible to be deleted.
     */
    public function isPublishable(): bool
    {
        return $this !== self::Low;
    }
}
