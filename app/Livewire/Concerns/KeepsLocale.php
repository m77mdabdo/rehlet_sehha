<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Support\Locales;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Locked;

/**
 * Carries the page's locale into every subsequent Livewire request.
 *
 * A Livewire update is a POST to /livewire/update — a route with no {locale}
 * segment, which therefore never passes through the SetLocale middleware. Two
 * things break without this, and both are invisible on the first render:
 *
 *   1. route() throws. Every named route in this application requires a
 *      {locale} parameter, supplied by URL::defaults() in the middleware. On
 *      an update request that default is gone, so the first re-render
 *      containing route('privacy') dies with "Missing parameter: locale".
 *
 *   2. The component silently changes language. Without an explicit locale the
 *      app falls back to the default, so an English patient who ticks the
 *      consent box watches the form redraw itself in Arabic.
 *
 * The locale is captured at mount, when the middleware has run, and reapplied
 * on boot of every later request. Locked, so the client cannot ask for a
 * locale the URL did not have.
 *
 * Persistent middleware was the alternative. It was rejected because SetLocale
 * reads the locale from a ROUTE PARAMETER that does not exist on the update
 * request, so re-running it would find nothing to read — the component has to
 * carry the value itself.
 */
trait KeepsLocale
{
    #[Locked]
    public string $locale = '';

    public function mountKeepsLocale(): void
    {
        $this->locale = App::getLocale();

        /*
         * Applied here as well as on boot.
         *
         * boot() runs BEFORE mount() on the very first render, when $this->locale
         * is still empty — so on that first pass nothing had set URL::defaults
         * except the middleware. That is fine in a real request and false
         * everywhere else: a component rendered without the middleware (a test,
         * a console render, a future queued job) would throw on the first
         * route() call it reached. The trait is supposed to make the component
         * self-sufficient, so it has to do this itself rather than assume.
         */
        $this->applyLocale();
    }

    /**
     * Runs on every request, after hydration. On the first render the
     * middleware has already done this and the value is still empty; from then
     * on this is the only thing setting it.
     */
    public function bootKeepsLocale(): void
    {
        $this->applyLocale();
    }

    private function applyLocale(): void
    {
        if ($this->locale === '' || ! Locales::isSupported($this->locale)) {
            return;
        }

        App::setLocale($this->locale);
        Locales::applyToUrlGenerator($this->locale);
    }
}
