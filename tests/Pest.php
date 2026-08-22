<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests run against the real MySQL 8 test database (see phpunit.xml)
| and are wrapped in a transaction that is rolled back after each test, so
| every test sees a freshly migrated schema without paying to rebuild it.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
| The Unit suite gets the application container but NOT the database.
|
| Its tests are conventions checks — they read Blade files and translation
| files off disk and need lang_path(), resource_path() and config() to resolve.
| That needs a booted app. It does not need a schema, and paying for
| RefreshDatabase here would add a transaction per test to prove nothing.
*/
pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain
| conditions. The "expect()" function gives you access to a set of
| "expectations" methods that you can use to assert different things.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
|
| Lives here rather than in one of the test files that uses it. A helper
| declared at the top of a test file is a plain PHP function and is therefore
| global once that file loads — which makes it available to other files when
| the whole suite runs, and undefined when one file is run on its own. That is
| a helper that works until somebody uses --filter.
|
*/

/**
 * Render one notification to its final HTML and text, as the mailer would.
 *
 * @return array{html: string, text: string, subject: string}
 */
function renderNotification(object $notification, string $locale): array
{
    $notification->locale($locale);

    /*
     * toMail() is invoked with the app locale already switched, because that
     * is what the framework does: NotificationSender wraps sendNow() in
     * withLocale($notification->locale). It matters because the payload
     * resolves translated values — the service name, the consultation mode —
     * at the moment toMail() runs, so calling it under the ambient locale
     * would build an Arabic payload and then render it into an English
     * template. The helper reproduces the wrapping rather than the bug.
     */
    $previous = App::getLocale();
    App::setLocale($locale);

    try {
        $mailable = $notification->toMail(
            (new AnonymousNotifiable)->route('mail', 'someone@example.com')
        );
    } finally {
        App::setLocale($previous);
    }

    $mailable->locale($locale);

    /*
     * render() first, and only then buildViewData().
     *
     * The payload does not exist on the Mailable until render() has run
     * prepareMailableForDelivery(), which is what invokes content() and
     * applies its `with` data. Reading the view data before that returns an
     * empty array and every template blows up on an undefined variable —
     * which is a property of the test helper, not of the mail.
     */
    $html = (string) $mailable->render();
    $data = $mailable->buildViewData();

    /*
     * The text part is rendered inside the locale too.
     *
     * Mailable::render() wraps itself in withLocale(), so the HTML comes out
     * in the right language on its own; rendering the text view directly does
     * not, and it silently produced an Arabic plain-text alternative attached
     * to an English message.
     */
    App::setLocale($locale);

    try {
        $text = (string) view($mailable->textView, $data)->render();
    } finally {
        App::setLocale($previous);
    }

    return [
        'html' => $html,
        'text' => $text,
        'subject' => (string) $mailable->subject,
    ];
}
