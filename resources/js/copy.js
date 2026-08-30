/**
 * Copy-to-clipboard, for the booking a patient has no other copy of.
 *
 * A patient who booked without an email address gets no confirmation, no
 * reminder and no link — the confirmation screen is the whole record. Asking
 * her to transcribe a sixty-four character token by hand from a phone is
 * asking her to lose it, so there is a button.
 *
 * Dependency-free and delegated from the document, for the same reason as
 * menu.js and one more: this markup is inside a Livewire component, so it is
 * replaced wholesale on every re-render. A listener bound to the button itself
 * would survive exactly until the patient typed in the "add my email" field
 * next to it. A delegated listener never goes stale.
 */

const RESET_AFTER_MS = 2500;

/**
 * The manual-copy prompt stays up longer than a success message.
 *
 * "Copied" is an acknowledgement and can go as soon as it is read. This is an
 * INSTRUCTION — select the text, press the keys — and taking it away while she
 * is still reaching for the keyboard is worse than not showing it.
 */
const FALLBACK_AFTER_MS = 12000;

function labelFor(button, state) {
    if (state === 'copied') {
        return button.dataset.copiedLabel || 'Copied';
    }

    if (state === 'manual') {
        return button.dataset.manualLabel || 'Copy it yourself';
    }

    return button.dataset.idleLabel || 'Copy';
}

/**
 * Put the value on screen, selected, so she can copy it by hand.
 *
 * THE CASE THIS EXISTS FOR. A patient who booked without an email address has
 * no confirmation, no reminder and no link — this screen is the entire record
 * of her appointment, and the button is the only offered way to keep it. When
 * both clipboard paths fail she previously got NOTHING: no error, no change,
 * a button that did not work and did not say so.
 *
 * Both paths do fail, together, more often than they look like they would. The
 * async API needs a secure context, a granted permission and a focused
 * document; execCommand is deprecated and already removed or restricted in
 * some browsers. A locked-down corporate browser or an expired certificate
 * knocks out both at once.
 *
 * So the fallback is the oldest one there is: show her the text, select it for
 * her, and tell her to copy it. Selecting the ELEMENT SHE CAN ALREADY SEE
 * rather than injecting a new field means the highlight lands on the reference
 * printed in front of her, which is the thing she is trying to keep.
 */
function offerManualCopy(button, value) {
    const scope = button.closest('[data-copy-scope]') || button.parentElement || document.body;

    // The visible element holding this exact value: the reference, or the URL.
    const source = [...scope.querySelectorAll('bdi, code, p, span, a')].find(
        (el) => el.textContent.trim() === value.trim() && el.offsetParent !== null,
    );

    if (!source) {
        return false;
    }

    try {
        const range = document.createRange();

        range.selectNodeContents(source);

        const selection = window.getSelection();

        selection.removeAllRanges();
        selection.addRange(range);
    } catch {
        return false;
    }

    // A hint the eye can follow to what has just been highlighted.
    source.setAttribute('data-copy-selected', '');

    window.setTimeout(() => source.removeAttribute('data-copy-selected'), FALLBACK_AFTER_MS);

    return true;
}

async function writeToClipboard(text) {
    /*
     * navigator.clipboard needs a secure context. Over plain http — which is
     * how this is developed, and how it would behave if the certificate ever
     * lapsed — it is simply undefined, and the button would fail silently.
     * The textarea fallback is deprecated and still works everywhere.
     */
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            /*
             * Falls through to the textarea below rather than giving up.
             *
             * writeText REJECTS far more often than its availability suggests:
             * the permission can be denied, and it throws outright whenever
             * the document is not focused — which includes the moment a
             * patient taps the button while a soft keyboard is dismissing.
             * Without this catch the rejection propagated out of the handler
             * and the button did nothing at all, silently, on exactly the
             * screen where she has no other copy of her booking.
             */
        }
    }

    const carrier = document.createElement('textarea');

    carrier.value = text;
    carrier.setAttribute('readonly', '');
    carrier.style.position = 'fixed';
    carrier.style.opacity = '0';
    carrier.style.pointerEvents = 'none';

    document.body.appendChild(carrier);
    carrier.select();

    let copied = false;

    try {
        copied = document.execCommand('copy');
    } catch {
        copied = false;
    }

    document.body.removeChild(carrier);

    return copied;
}

function initCopy() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const value = button.dataset.copy;

        if (!value) {
            return;
        }

        const copied = await writeToClipboard(value);
        const output = button.querySelector('[data-copy-label]') ?? button;
        const live = document.querySelector('[data-copy-announcer]');

        if (!copied) {
            /*
             * Neither clipboard path worked. Say so, visibly, and select the
             * text for her — silence here loses her booking.
             */
            const selected = offerManualCopy(button, value);

            output.textContent = labelFor(button, 'manual');
            button.setAttribute('data-copy-state', 'manual');

            if (live) {
                live.textContent = selected
                    ? button.dataset.manualHint || labelFor(button, 'manual')
                    : labelFor(button, 'manual');
            }

            window.setTimeout(() => {
                output.textContent = labelFor(button, 'idle');
                button.removeAttribute('data-copy-state');

                if (live) {
                    live.textContent = '';
                }
            }, FALLBACK_AFTER_MS);

            return;
        }

        output.textContent = labelFor(button, 'copied');
        button.setAttribute('data-copy-state', 'copied');

        /*
         * The confirmation is announced, not just shown. A patient using a
         * screen reader has no way of knowing the button did anything —
         * nothing moved and focus did not change — and this is the one action
         * on the page she cannot afford to be unsure about.
         */
        if (live) {
            live.textContent = labelFor(button, 'copied');
        }

        window.setTimeout(() => {
            output.textContent = labelFor(button, 'idle');
            button.removeAttribute('data-copy-state');

            if (live) {
                live.textContent = '';
            }
        }, RESET_AFTER_MS);
    });
}

initCopy();
