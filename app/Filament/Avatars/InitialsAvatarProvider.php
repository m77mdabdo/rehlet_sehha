<?php

declare(strict_types=1);

namespace App\Filament\Avatars;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

/**
 * THE ADMIN PANEL WAS SENDING THE PRACTITIONER'S NAME TO A THIRD PARTY.
 *
 * Filament's default avatar provider builds an <img> pointing at
 * ui-avatars.com with the user's name in the query string. Every admin page
 * load was therefore an outbound request to a company nobody here has an
 * agreement with, carrying «د. رنا سالم» in a URL that lands in their access
 * log — on the panel where patient records are opened.
 *
 * IT WAS ALREADY BLOCKED, and that is the only reason this was found rather
 * than shipped. The CSP's `img-src 'self' data:` refused the request and
 * logged it, so the name never left and the avatar simply rendered broken. It
 * is exactly the shape of the fonts.bunny.net request the same policy caught
 * earlier — a default in a dependency, quietly reaching off-site.
 *
 * A stricter policy that happens to hold is not the same as not making the
 * request. If the CSP is ever loosened for an unrelated reason, this comes
 * back on its own and nothing complains.
 *
 * So the avatar is generated here, as an inline SVG data URI: no network, no
 * third party, and it works offline and on a locked-down host. `data:` is
 * already permitted by the policy for exactly this kind of thing.
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $name = trim((string) ($record->getAttribute('name') ?? ''));

        $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96">
                <rect width="96" height="96" rx="48" fill="#0E2E4D"/>
                <text x="48" y="48" fill="#E8A94A" font-family="system-ui, sans-serif"
                      font-size="36" font-weight="600" text-anchor="middle"
                      dominant-baseline="central">{$this->initials($name)}</text>
            </svg>
            SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * ONE LETTER, NOT TWO.
     *
     * The obvious "first letter of the first two words" produces «د ر» for
     * «د. رنا سالم» — the honorific and the given name — which is not initials,
     * it is noise. Arabic names on this panel start with a title far more often
     * than not, so the title is dropped and one meaningful letter is used.
     *
     * Falls back to a bullet rather than to an empty circle: a blank avatar
     * reads as a failed image, which is what this replaced.
     */
    private function initials(string $name): string
    {
        $words = preg_split('/\s+/u', $name) ?: [];

        $words = array_values(array_filter(
            $words,
            fn (string $word): bool => ! in_array(rtrim($word, '.'), ['د', 'أ', 'م', 'Dr', 'Mr', 'Ms', 'Mrs'], true)
        ));

        $first = $words[0] ?? '';

        return $first === '' ? '•' : e(mb_substr($first, 0, 1));
    }
}
