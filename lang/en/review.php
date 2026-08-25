<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The review form
|------------------------------------------------------------------------------
|
| CONSENT IS A SEPARATE, UNTICKED DECISION. A patient may want to tell the
| clinic something without wanting it on the internet, and the copy says that
| plainly rather than burying it under a pre-ticked box.
|
| NO CLINICAL CONTENT. The note asks her to describe the experience rather than
| her diagnosis — a public review naming a condition is her medical
| information, published by us, in a place she cannot easily take it back from.
| The moderation queue carries the same warning for whoever approves it.
|
*/

return [
    'meta_title' => 'Your review — Rehlet Sehha',

    'eyebrow' => 'Your review',
    'title' => 'How was it?',
    'lead' => 'A minute at most. It helps us improve, and it helps someone else who is deciding whether to book.',

    'rating_label' => 'Your rating',
    'rating_hint' => 'One to five.',
    'rating_required' => 'Please choose a rating.',

    'comment_label' => 'Anything you would like to say?',
    'comment_hint' => 'Write about the experience itself — the care, the plan, the follow-up.',

    'clinical_warning_title' => 'Please do not include details of your condition',
    'clinical_warning' => 'This review may be published on the site. Write about the experience rather than your diagnosis, results or medication — that is your private health information, and it is hard to take back once it is public.',

    'name_label' => 'Name to display',
    'name_hint' => 'We have used your first name and an initial. Change it however you like, or leave it.',

    'consent_label' => 'I agree to my review being published on the site',
    'consent_hint' => 'Leave it unticked and your review reaches us only, published nowhere. And nothing is published before we have read it.',

    'submit' => 'Send your review',

    'thanks_title' => 'Received, thank you',
    'thanks_body' => 'Your review has been recorded. If you agreed to publication, we will read it first and then it appears on the site.',
    'thanks_private' => 'Your review has reached us and stays between us — it will not be published.',

    'already_title' => 'You have already written a review',
    'already_body' => 'This is what reached us. If you would like to change something, message us on WhatsApp.',

    'expired' => 'This link is no longer active. If you would like to leave a review, message us on WhatsApp.',
];
