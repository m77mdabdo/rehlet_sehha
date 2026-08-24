/**
 * The video facade.
 *
 * Nothing here talks to YouTube until a patient presses play. On click this
 * builds ONE iframe, points it at the nocookie embed URL the server put on the
 * button, and drops it into a native <dialog>. On close it REMOVES the iframe
 * again — a hidden iframe is still a live connection, and a closed dialog that
 * leaves one running would defeat the whole point.
 *
 * Dependency-free, like menu.js and copy.js. Alpine would be a pleasant way to
 * write this and would cost roughly fifteen kilobytes gzipped to load on every
 * homepage visit — more than fifteen times the site's entire current script
 * budget — for three interactions that vanilla handles in a hundred lines.
 *
 * A native <dialog> rather than a hand-rolled modal: the browser then owns the
 * focus trap, Escape, the inert backdrop and returning focus to the opener,
 * and it gets all four right in cases hand-written traps miss.
 */

function initVideoGallery() {
    const gallery = document.querySelector('[data-video-gallery]');

    if (!gallery) {
        return;
    }

    const dialog = gallery.querySelector('[data-video-dialog]');
    const frame = gallery.querySelector('[data-video-frame]');
    const title = gallery.querySelector('[data-video-dialog-title]');

    if (!dialog || !frame || !title) {
        return;
    }

    /*
     * The card that opened the dialog. <dialog> restores focus to the opener
     * on its own in current browsers, but not in every version still in use,
     * and losing focus to <body> after closing a modal strands a keyboard user
     * at the top of the page. Cheap to guarantee.
     */
    let opener = null;

    function open(button) {
        opener = button;

        title.textContent = button.dataset.videoTitle || '';

        /*
         * Built here, from the URL the server supplied. The script never
         * assembles a YouTube URL itself, so the nocookie host and the absent
         * autoplay flag stay decided in one commented place in PHP.
         */
        const iframe = document.createElement('iframe');

        iframe.src = button.dataset.videoEmbed;
        iframe.title = button.dataset.videoTitle || '';
        iframe.className = 'h-full w-full';
        iframe.allow = 'accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        iframe.allowFullscreen = true;
        // Referrer withheld: the embed does not need to know which page of the
        // clinic's site the patient was reading.
        iframe.referrerPolicy = 'no-referrer';
        iframe.setAttribute('frameborder', '0');

        frame.replaceChildren(iframe);

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            // No <dialog> support: the video still plays, just inline.
            dialog.setAttribute('open', '');
        }
    }

    function close() {
        /*
         * Emptying the container destroys the iframe, which stops playback and
         * closes the connection. Merely hiding the dialog would leave YouTube
         * streaming to a patient who thought she had closed it.
         */
        frame.replaceChildren();

        if (opener && document.contains(opener)) {
            opener.focus();
        }

        opener = null;
    }

    // Delegated: one listener for the whole gallery, and it keeps working if
    // the card markup is ever re-rendered.
    gallery.addEventListener('click', (event) => {
        const play = event.target.closest('[data-video-play]');

        if (play) {
            event.preventDefault();
            open(play);

            return;
        }

        if (event.target.closest('[data-video-close]')) {
            dialog.close();
        }
    });

    /*
     * `close` fires however the dialog was dismissed — the button, Escape, or
     * the form method="dialog" path — so the teardown is written once and
     * cannot be bypassed.
     */
    dialog.addEventListener('close', close);

    // Clicking the backdrop closes it. The dialog element's own box is the
    // hit area, so a click landing outside its content rectangle is a
    // click on the backdrop.
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) {
            return;
        }

        const box = dialog.getBoundingClientRect();

        const inside =
            event.clientX >= box.left &&
            event.clientX <= box.right &&
            event.clientY >= box.top &&
            event.clientY <= box.bottom;

        if (!inside) {
            dialog.close();
        }
    });
}

initVideoGallery();
