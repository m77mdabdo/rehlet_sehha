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

function labelFor(button, state) {
    return state === 'copied'
        ? button.dataset.copiedLabel || 'Copied'
        : button.dataset.idleLabel || 'Copy';
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

        if (!copied) {
            return;
        }

        const output = button.querySelector('[data-copy-label]') ?? button;

        output.textContent = labelFor(button, 'copied');
        button.setAttribute('data-copy-state', 'copied');

        /*
         * The confirmation is announced, not just shown. A patient using a
         * screen reader has no way of knowing the button did anything —
         * nothing moved and focus did not change — and this is the one action
         * on the page she cannot afford to be unsure about.
         */
        const live = document.querySelector('[data-copy-announcer]');

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
