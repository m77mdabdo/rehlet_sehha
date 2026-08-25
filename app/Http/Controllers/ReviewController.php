<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\PublicContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * The review form, opened from a token in the invitation.
     *
     * The token is a BEARER CREDENTIAL — whoever holds it writes as that
     * patient — so the route carries the same protections as the cancellation
     * link: noindex, no-store, no-referrer, applied by the token-url
     * middleware and asserted in TokenUrlHygieneTest.
     */
    /*
     * No $locale parameter: SetLocale drops the segment from the route's
     * parameters once it has applied it, so controllers never accept a locale
     * they do not use. Route helpers still emit it, because Locales
     * applyToUrlGenerator sets the default.
     */
    public function show(string $token): View
    {
        $review = Review::where('token', $token)->firstOrFail();

        return view('pages.review', [
            'review' => $review,
            'footerServices' => PublicContent::services(),
            // A patient who already answered sees what she said, not a blank
            // form inviting her to answer again.
            'alreadySubmitted' => $review->submitted_at !== null,
        ]);
    }

    /**
     * Record what she wrote.
     *
     * CONSENT IS A SEPARATE, UNTICKED BOX and it is stored as a TIMESTAMP
     * rather than a flag: when it was given is evidence, and if she ever
     * withdraws it we need to know what she agreed to and when.
     *
     * Nothing here approves anything. A submission is not a publication —
     * the clinic still has to read it, because somebody may have written a
     * diagnosis, another patient's name, or something they will regret being
     * public, and that is caught before it is on the internet rather than
     * after.
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        $review = Review::where('token', $token)->firstOrFail();

        if ($review->submitted_at !== null) {
            return redirect()->route('review.show', ['token' => $token]);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1200'],
            'display_name' => ['nullable', 'string', 'max:60'],
            // Unticked is a valid answer, not a validation failure. She may
            // want to tell the clinic something and not the internet.
            'consent' => ['nullable', Rule::in(['1'])],
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'display_name' => $validated['display_name'] ?: $review->display_name,
            'submitted_at' => Carbon::now(),
            'consented_at' => ($validated['consent'] ?? null) === '1' ? Carbon::now() : null,
        ]);

        return redirect()
            ->route('review.show', ['token' => $token])
            ->with('review-submitted', true);
    }
}
