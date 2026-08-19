<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The site's entry point.
     *
     * This was the skeleton's "root returns 200". It no longer does, and that
     * is the design: every page lives under a /{locale}/ prefix so each
     * language is its own indexable URL, and the bare root only points the way.
     *
     * See LocaleRoutingTest for what the locales themselves do.
     */
    public function test_the_root_directs_visitors_to_a_locale(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/ar');
    }
}
