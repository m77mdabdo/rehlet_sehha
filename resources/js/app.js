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
