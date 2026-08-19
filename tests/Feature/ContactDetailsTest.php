<?php

declare(strict_types=1);

use App\Support\Contact;

/**
 * Contact details are the part of a clinic site that fails silently.
 *
 * A tel: link built from the display number, a wa.me link that kept its plus,
 * a phone number reordered by bidi inside Arabic text — none of these throw,
 * none show up in a log, and none get reported, because the person who tapped
 * them just gave up and went somewhere else. So they are asserted here.
 */
it('builds tel links from the E.164 number, never the display value', function () {
    config()->set('clinic.contact.phone', '+201004818303');
    config()->set('clinic.contact.phone_display', '0100 481 8303');

    expect(Contact::telHref())->toBe('tel:+201004818303');

    // The spaces in the display value are the whole reason for the split.
    expect(Contact::telHref())->not->toContain(' ');
});

it('builds wa.me links without the leading plus', function () {
    config()->set('clinic.contact.whatsapp', '201004818303');

    expect(Contact::whatsappHref())->toBe('https://wa.me/201004818303');
    expect(Contact::whatsappHref())->not->toContain('+');
});

it('shows the number in latin digits, not arabic-indic', function () {
    app()->setLocale('ar');

    $display = Contact::phoneDisplay();

    expect($display)->not->toBeNull();

    // Arabic-Indic digits U+0660–U+0669. Egyptian visitors copy these numbers
    // into dialers and contact apps, plenty of which fail to parse them.
    expect($display)->not->toMatch('/[\x{0660}-\x{0669}]/u');
    expect($display)->toMatch('/[0-9]/');
});

it('renders the number inside an ltr wrapper on the arabic page', function () {
    $response = $this->get('/ar')->assertOk();

    // Without dir="ltr" the bidi algorithm reorders the + and the digit groups
    // inside Arabic text, and the number displayed stops matching the number
    // dialled.
    $response->assertSee('<bdi dir="ltr" class="tabular-nums">'.Contact::phoneDisplay().'</bdi>', false);
});

it('renders every configured detail in the footer', function (string $locale) {
    $response = $this->get("/{$locale}")->assertOk();

    $response->assertSee('tel:+201004818303', false);
    $response->assertSee('https://wa.me/201004818303', false);
    $response->assertSee('mailto:info@rehletsehha.com', false);
    $response->assertSee(Contact::address($locale), false);
})->with(['ar', 'en']);

it('renders nothing at all for a detail that is null', function () {
    config()->set('clinic.contact.phone', null);
    config()->set('clinic.contact.phone_display', null);
    config()->set('clinic.contact.whatsapp', null);

    expect(Contact::telHref())->toBeNull();
    expect(Contact::whatsappHref())->toBeNull();
    expect(Contact::phoneDisplay())->toBeNull();

    $content = $this->get('/ar')->assertOk()->getContent();

    // No empty link, no placeholder, no "TBD" — the rows simply do not exist.
    expect($content)->not->toContain('tel:');
    expect($content)->not->toContain('wa.me');
    expect($content)->not->toContain('href=""');

    // The details that ARE configured must still be there: one missing value
    // may not take the rest of the block down with it.
    expect($content)->toContain('mailto:info@rehletsehha.com');
});

it('drops the whole contact list when nothing is configured', function () {
    config()->set('clinic.contact', [
        'email' => null,
        'phone' => null,
        'phone_display' => null,
        'whatsapp' => null,
        'address' => null,
    ]);

    expect(Contact::hasAny())->toBeFalse();

    $content = $this->get('/ar')->assertOk()->getContent();

    expect($content)->not->toContain('mailto:');
    expect($content)->not->toContain('tel:');
});

it('treats an empty string as an unfilled detail', function () {
    // '' in config is someone who has not got round to it, not a value.
    config()->set('clinic.contact.email', '   ');

    expect(Contact::email())->toBeNull();
    expect($this->get('/ar')->getContent())->not->toContain('mailto:');
});

it('gives the address in the active locale', function () {
    expect(Contact::address('ar'))->toBe('المعادي، القاهرة');
    expect(Contact::address('en'))->toBe('Maadi, Cairo');

    app()->setLocale('en');
    expect(Contact::address())->toBe('Maadi, Cairo');
});

it('falls back to the default locale for an address it has no translation for', function () {
    expect(Contact::address('de'))->toBe('المعادي، القاهرة');
});
