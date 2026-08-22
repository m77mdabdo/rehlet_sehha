/**
 * Move focus to a field a Livewire component asks for.
 *
 * Used by the "Add my email" button on the no-email notice. The notice sits
 * well below the email input on a phone, so dismissing it without taking the
 * patient back to the field would leave her looking at a form that has
 * silently changed somewhere off screen — and pressing the button again.
 *
 * Listens for Livewire's dispatched event rather than binding to the button,
 * because the component re-renders the notice away in the same round trip and
 * any handler attached to that markup would go with it.
 */
document.addEventListener('livewire:init', () => {
    window.Livewire.on('focus-field', (event) => {
        const name = Array.isArray(event) ? event[0]?.field : event?.field;

        if (!name) {
            return;
        }

        /*
         * Deferred to the next frame. The event fires while Livewire is still
         * patching the DOM, and focusing an element that is about to be
         * replaced puts focus back on the body.
         */
        window.requestAnimationFrame(() => {
            const field = document.getElementById(name);

            if (!field) {
                return;
            }

            field.focus();

            // Centred rather than scrolled-to-top: on a short viewport the
            // default lands the input under the sticky header.
            field.scrollIntoView({ block: 'center', behavior: 'smooth' });
        });
    });
});
