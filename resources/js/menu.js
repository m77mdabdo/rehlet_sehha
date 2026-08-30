/**
 * Mobile menu.
 *
 * Deliberately dependency-free rather than an Alpine component: this is the
 * first interaction a visitor on a phone has with the site, and it should not
 * wait on a framework to hydrate. The whole behaviour is thirty lines, and the
 * markup carries the state in `aria-expanded` where assistive technology can
 * read it, rather than in a JS object where it cannot.
 */
function initMenu() {
    const root = document.querySelector('[data-menu-root]');

    if (!root) {
        return;
    }

    const toggle = root.querySelector('[data-menu-toggle]');
    const panel = root.querySelector('[data-menu-panel]');
    const label = root.querySelector('[data-menu-label]');
    const iconOpen = root.querySelector('[data-menu-icon-open]');
    const iconClose = root.querySelector('[data-menu-icon-close]');

    if (!toggle || !panel) {
        return;
    }

    const labels = {
        open: label?.dataset.labelOpen ?? label?.textContent?.trim() ?? '',
        close: toggle.dataset.labelClose ?? '',
    };

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        panel.classList.toggle('hidden', !open);
        iconOpen?.classList.toggle('hidden', open);
        iconClose?.classList.toggle('hidden', !open);

        /*
         * MOVE FOCUS INTO THE PANEL ON OPEN.
         *
         * aria-expanded told a screen reader the menu had opened and nothing
         * moved, so the next Tab continued from the toggle through whatever
         * follows it in the source — a keyboard user opened a menu they were
         * not in. Escape already returned focus to the toggle; this is the
         * other half of that contract.
         *
         * The first link rather than the panel itself: focusing a container
         * announces the container and leaves the user still needing to Tab.
         */
        if (open) {
            panel.querySelector('a, button, [tabindex]:not([tabindex="-1"])')?.focus();
        }

        if (label && labels.close) {
            label.textContent = open ? labels.close : labels.open;
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    // Escape closes the menu and returns focus to the control that opened it,
    // so a keyboard user is never stranded inside a panel they cannot leave.
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });

    // Following an in-page anchor should close the menu behind you.
    panel.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a')) {
            setOpen(false);
        }
    });

    // Resizing up to the desktop breakpoint leaves the panel orphaned open.
    const desktop = window.matchMedia('(min-width: 1024px)');
    desktop.addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMenu);
} else {
    initMenu();
}
