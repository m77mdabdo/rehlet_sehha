{{--
    THE PHOTO LIBRARY, AS PICTURES.

    A filename dropdown is unusable for choosing a photograph.
    «food-fruit-bowl» and «food-vegetables-overhead» are indistinguishable in a
    list, so the only way to choose was to open the manifest, read `describes`
    and guess — which is how twelve articles ended up sharing generic covers
    picked for other pieces.

    ------------------------------------------------------------------------
    WHY THIS IS STYLED IN A <style> BLOCK RATHER THAN WITH TAILWIND CLASSES

    Filament compiles its own stylesheet. Utility classes written in an
    application Blade view are NOT in its content globs, so `grid-cols-4` and
    friends simply do not exist in the CSS the panel loads — the first version
    of this file used them and rendered thirty-four full-width images in a
    single column, which looked like a broken page.

    The alternative is registering a custom Filament theme: a second Tailwind
    entry point, a build step and a stylesheet to keep in sync, all so that one
    field can have a grid. That is disproportionate. Scoped CSS with a
    namespaced class prefix costs nothing and cannot drift.

    Colours are inherited or given in both schemes explicitly, because the panel
    may be in light or dark mode and this view has no way to ask which.
--}}

@php
    use App\Support\Photo;

    $library = Photo::all();

    $grouped = [];

    foreach ($library as $slug => $entry) {
        $grouped[$entry['topic']][$slug] = $entry;
    }

    ksort($grouped);

    $topicLabels = [
        'food' => 'أكل ومطبخ',
        'clinic' => 'العيادة',
        'clinical' => 'أجهزة وتحاليل',
        'diabetes' => 'السكري',
        'blood-pressure' => 'الضغط',
        'pregnancy' => 'الحمل',
        'child-nutrition' => 'الأطفال',
    ];
@endphp

<style>
    .rs-picker__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.75rem;
        /* Items are equal height (see the caption rule), so rows stay flush.
           Without this a slug that wraps to two lines pushes its neighbours
           and leaves holes in the grid. */
        align-items: start;
    }

    .rs-picker__item {
        display: block;
        width: 100%;
        overflow: hidden;
        border-radius: 0.5rem;
        border: 2px solid transparent;
        background: rgb(0 0 0 / 3%);
        padding: 0;
        text-align: start;
        cursor: pointer;
        position: relative;
        transition: border-color 120ms ease;
    }

    .rs-picker__item:hover { border-color: rgb(148 163 184); }
    .rs-picker__item[aria-pressed="true"] { border-color: rgb(59 130 246); }

    .rs-picker__item img {
        display: block;
        width: 100%;
        aspect-ratio: 3 / 2;
        object-fit: cover;
    }

    .rs-picker__caption {
        display: block;
        padding: 0.35rem 0.5rem;
        font-size: 11px;
        line-height: 1.3;
        opacity: 0.75;
        /* ONE LINE, ALWAYS. Slugs vary from 12 to 30 characters, and letting
           them wrap made every tile a different height and the grid ragged.
           The full slug is in the title attribute, and the picture is what she
           is choosing by anyway. */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        direction: ltr;
        text-align: start;
    }

    .rs-picker__badge {
        position: absolute;
        inset-block-start: 0.35rem;
        inset-inline-end: 0.35rem;
        border-radius: 999px;
        background: rgb(59 130 246);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 0.1rem 0.4rem;
    }

    .rs-picker__none {
        border-radius: 0.5rem;
        border: 2px solid transparent;
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
        cursor: pointer;
        background: rgb(0 0 0 / 4%);
    }

    .rs-picker__none[aria-pressed="true"] { border-color: rgb(59 130 246); }

    .rs-picker__topic {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        opacity: 0.6;
        margin-block-end: 0.4rem;
    }

    .rs-picker__group + .rs-picker__group { margin-block-start: 1.25rem; }
</style>

<div x-data="{ selected: $wire.$entangle('{{ $getStatePath() }}') }">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-block-end:1rem;">
        <p style="font-size:0.875rem; opacity:0.7; margin:0;">
            {{ count($library) }} صورة في المكتبة. دوسي على الصورة عشان تختاريها.
        </p>

        {{-- REMOVING THE COVER IS A FIRST-CLASS CHOICE, not an empty option at
             the top of a list. An article with no cover is a valid article. --}}
        <button
            type="button"
            class="rs-picker__none"
            x-on:click="selected = null"
            x-bind:aria-pressed="selected === null ? 'true' : 'false'"
        >من غير صورة</button>
    </div>

    @foreach ($grouped as $topic => $entries)
        <div class="rs-picker__group">
            <p class="rs-picker__topic">{{ $topicLabels[$topic] ?? $topic }}</p>

            <div class="rs-picker__grid">
                @foreach ($entries as $slug => $entry)
                    <button
                        type="button"
                        class="rs-picker__item"
                        title="{{ $entry['describes'] }}"
                        x-on:click="selected = @js($slug)"
                        x-bind:aria-pressed="selected === @js($slug) ? 'true' : 'false'"
                    >
                        <img src="{{ Photo::url($slug, 'sm') }}" alt="{{ $entry['describes'] }}" loading="lazy">

                        <span class="rs-picker__caption">{{ $slug }}</span>

                        <span class="rs-picker__badge" x-show="selected === @js($slug)" x-cloak>مختارة</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
