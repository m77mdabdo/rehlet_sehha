/*
 * The site's only JavaScript.
 *
 * There was an axios import here, inherited from the Laravel skeleton. Nothing
 * in this project makes an XHR call, and Livewire brings its own transport, so
 * it was 18 KB gzipped of a 19 KB bundle serving no one.
 */
import './menu';
import './copy';
import './focus';

/*
 * The three interactive features from Task 8, each dependency-free for the
 * same reason menu.js is: Alpine would cost roughly fifteen kilobytes gzipped
 * on every homepage visit — more than fifteen times this whole bundle — to
 * replace a few hundred lines of DOM work. See the section components for the
 * full argument.
 */
import './video-gallery';
import './matcher';
import './plate';

/*
 * The hero background video. Decides whether the visitor gets one at all —
 * reduced motion, Save-Data and slow connections never do — and only hands the
 * URL over once the answer is yes.
 */
import './hero-video';

/*
 * The header goes transparent over a hero that has a background video, and
 * solid once you scroll past it. Adds the transparent state only — the solid
 * header is what the markup ships, so a failure here is never unreadable.
 */
import './header';

/*
 * Entrance, scroll reveals and the counting figures. Purely additive: the page
 * is complete without it, and a head script un-hides everything if this never
 * arrives. See the Motion block in app.css.
 */
import './motion';
