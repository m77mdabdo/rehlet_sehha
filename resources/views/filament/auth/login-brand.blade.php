{{--
    The admin sign-in screen.

    STYLE ONLY, AND THAT IS A HARD CONSTRAINT RATHER THAN A PREFERENCE. This is
    injected through Filament's SIMPLE_LAYOUT_START render hook. It adds markup
    and CSS around the login page and touches no part of it: the form, its
    validation, the throttle, the session guard and the two-factor challenge are
    all still Filament's own, unmodified and unwrapped.

    Overriding the login VIEW would have been the obvious route and it is the
    one that breaks quietly. That view renders the multi-factor challenge and
    the password-reset link from the page object; a copy of it in this
    repository is a copy that stops matching the package on the next upgrade,
    and the way you find out is that somebody with 2FA enabled cannot get in.

    So: a render hook, a brand mark, and a stylesheet that dresses Filament's
    own elements by their public class names.

    NO "CREATE ACCOUNT" LINK, and there is nothing to remove — the panel never
    calls ->registration(), so Filament does not render one. Accounts are made
    by an administrator. This note exists so that nobody adds one back thinking
    it was an oversight.

    ARABIC ONLY. The panel is not bilingual and has no locale prefix; see the
    ->path('admin') note in AdminPanelProvider for why. dir comes from the
    document, so the inputs, the focus rings and the remember-me row all mirror
    without a single physical property here.
--}}
<style>
    /*
     * THE CARD IS EXPLICITLY LIGHT, IN BOTH COLOUR SCHEMES, AND THAT IS A BUG
     * THIS FILE ALREADY SHIPPED ONCE.
     *
     * Filament follows the operating system's dark-mode preference. The first
     * version of this stylesheet painted the card white and left the text to
     * Filament, so on a machine set to dark the labels came out white on white
     * and the form was a row of empty boxes with a red asterisk beside each.
     * It looked perfectly fine on the machine it was written on.
     *
     * So every colour inside the card is stated here rather than inherited.
     * The ground is navy in both schemes, which is the one thing that was
     * already safe.
     */
    .fi-simple-layout {
        position: relative;
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.75rem;
        padding: 2.5rem 1.25rem;
        background-color: #0e2e4d;
        background-image:
            radial-gradient(1100px 480px at 50% -15%, rgba(72, 187, 212, 0.18), transparent 70%),
            radial-gradient(820px 380px at 12% 112%, rgba(26, 109, 166, 0.30), transparent 72%);
    }

    /*
     * The brand pattern, on a pseudo-element so its opacity is its own.
     *
     * The first attempt set the mark as a repeating background at 132px and it
     * became wallpaper — a wall of logos with a form on it. At this scale and
     * this opacity it is a texture you notice only if you look for it, which is
     * what a pattern behind a sign-in form should be.
     */
    .fi-simple-layout::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("{{ asset('brand/icon-tile-navy.svg') }}");
        background-size: 320px 320px;
        background-repeat: repeat;
        opacity: 0.05;
        pointer-events: none;
    }

    .fi-simple-layout > * { position: relative; }

    /* Filament's own logo, replaced by the mark below. */
    .fi-simple-layout .fi-logo { display: none; }

    .fi-simple-main-ctn { flex: none; width: 100%; }

    .rs-login-mark {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.7rem;
        text-align: center;
    }

    .rs-login-mark img { width: 3.5rem; height: 3.5rem; }

    .rs-login-mark span {
        font-size: 1.0625rem;
        font-weight: 600;
        color: #ffffff;
    }

    /* The card. */
    .fi-simple-main {
        background-color: #ffffff;
        color: #0e2e4d;
        border-radius: 1.25rem;
        box-shadow: 0 24px 60px -24px rgba(2, 12, 24, 0.55);
        padding: 2rem 1.75rem;
        width: 100%;
        max-width: 25rem;
        margin-inline: auto;
    }

    @media (min-width: 640px) {
        .fi-simple-main { padding: 2.5rem 2.25rem; }
    }

    /*
     * Every piece of text inside it, stated. See the note at the top.
     *
     * .fi-fo-field-label-content IS THE ONE THAT MATTERS and it is easy to
     * miss: Filament puts the colour on a span INSIDE the label, not on the
     * label, so styling .fi-fo-field-label alone leaves the words white on
     * white while devtools reports the label as correctly dark. The only
     * visible symptom is a red asterisk floating beside an empty box.
     */
    .fi-simple-main,
    .fi-simple-main .fi-fo-field-label,
    .fi-simple-main .fi-fo-field-label-content,
    .fi-simple-main .fi-fo-field-wrp-hint,
    .fi-simple-main .fi-fo-field-wrp-helper-text,
    .fi-simple-main label,
    .fi-simple-main .fi-sc-text,
    .fi-simple-main h1,
    .fi-simple-main h2 { color: #0e2e4d; }

    .fi-simple-main .fi-fo-field-label { font-weight: 600; }

    .fi-simple-main input { color: #0e2e4d; background-color: #ffffff; }
    .fi-simple-main input::placeholder { color: rgba(74, 102, 132, 0.75); }

    /* Soft borders, and a focus ring that is unmistakable rather than tasteful —
       this is the one form on the site where getting it wrong locks somebody
       out of her own clinic. */
    .fi-simple-main .fi-input-wrp {
        background-color: #ffffff;
        border-radius: 0.75rem;
        box-shadow: none;
        outline: 1px solid rgba(14, 46, 77, 0.18);
        transition: outline-color 150ms ease, box-shadow 150ms ease;
    }

    .fi-simple-main .fi-input-wrp:has(:focus) {
        outline: 2px solid #1a6da6;
        box-shadow: 0 0 0 4px rgba(26, 109, 166, 0.18);
    }

    /* The remember-me checkbox and the reset link. */
    .fi-simple-main .fi-checkbox-input { border-color: rgba(14, 46, 77, 0.35); }
    .fi-simple-main a { color: #166a9f; }
    .fi-simple-main a:hover { text-decoration: underline; }

    .fi-simple-main .fi-btn {
        border-radius: 9999px;
        padding-block: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="rs-login-mark">
    <img src="{{ asset('brand/icon-192.png') }}" alt="" aria-hidden="true">
    <span>{{ config('clinic.name_ar', 'رحلة صحة') }}</span>
</div>
