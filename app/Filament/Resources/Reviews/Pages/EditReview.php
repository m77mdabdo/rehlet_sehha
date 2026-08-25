<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    /**
     * Translate the approval checkbox into a timestamp and an approver.
     *
     * WHO and WHEN, not just "true". If a patient later asks why her words are
     * on the site, the answer has to name somebody and a date.
     *
     * The model still refuses to save an approval without consent, so this
     * cannot publish something she declined even if the checkbox were forced.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $approved = (bool) ($this->data['is_approved'] ?? false);
        $review = $this->getRecord();

        /*
         * Not a type-check for its own sake. Consent is verified against the
         * stored review, and an approval whose consent cannot be read is one
         * that must not be written — so the fallback below is to refuse,
         * never to assume.
         */
        if (! $review instanceof Review) {
            $data['approved_at'] = null;
            $data['approved_by'] = null;

            return $data;
        }

        if ($approved && $review->consented_at !== null) {
            $data['approved_at'] = $review->approved_at ?? Carbon::now();
            $data['approved_by'] = auth()->id();
        } else {
            $data['approved_at'] = null;
            $data['approved_by'] = null;
        }

        return $data;
    }
}
