@props(['services'])

{{--
    The package matcher.

    Three questions, a recommendation, and a deep link into the booking wizard
    with the service preselected — which Task 5 already handles via
    /booking?service={slug}.

    WHY THIS EXISTS. The pricing section is where most visitors stop. They read
    four packages, cannot tell which one is theirs, and leave. A price list
    cannot answer "which of these is for someone like me", and that is the
    question they were actually asking.

    NOTHING IS COLLECTED. The answers never leave the browser: there is no
    fetch, no form post, no analytics event, and nothing written to storage.
    That is stated on screen in one line, because a health quiz that quietly
    profiles you is exactly what patients fear, and the only way she can know
    otherwise is if we say so.

    VANILLA, NOT ALPINE. Three clicks with no server state worth keeping is
    exactly the case for Alpine — and Alpine is not loaded on this page. The
    public site ships about a kilobyte of script in total; adding a framework
    for this would multiply that by fifteen for every visitor, including the
    ones who never touch the quiz. The scoring is a dozen lines.

    ALL COPY COMES FROM lang/*/matcher.php. The question data is emitted as
    JSON below so the script never contains a sentence — the clinic can reword
    every question without anybody opening a .js file.
--}}

@php
    use App\Support\Locales;

    $questions = __('matcher.questions');
    $results = __('matcher.results');

    /*
     * Only the packages that actually exist and are bookable. A recommendation
     * pointing at a withdrawn service would deep-link into a booking wizard
     * that refuses to load it, which is a worse outcome than no quiz.
     */
    $bookable = $services->keyBy('slug');

    $payload = [
        'questions' => $questions,
        'results' => collect($results)
            ->filter(fn (array $result, string $slug): bool => $bookable->has($slug))
            ->map(fn (array $result, string $slug): array => [
                'slug' => $slug,
                'name' => $result['name'],
                'why' => $result['why'],
                'url' => route('booking', ['service' => $slug]),
            ])
            ->values()
            ->all(),
    ];
@endphp

<section id="matcher" class="bg-sage/50 py-20 sm:py-24" aria-labelledby="matcher-heading">
    <x-container>
        <x-section-heading
            id="matcher-heading"
            :eyebrow="__('matcher.eyebrow')"
            :title="__('matcher.title')"
            :lead="__('matcher.lead')"
        />

        <div
            class="mx-auto mt-12 max-w-2xl"
            data-matcher
            data-matcher-payload="{{ json_encode($payload, JSON_UNESCAPED_UNICODE) }}"
        >
            <x-card class="p-6 sm:p-8">
                {{--
                    Rendered server-side so the first question is visible and
                    readable before any script runs, and so a visitor with
                    JavaScript disabled sees a real question rather than an
                    empty box. The script takes over from there.
                --}}
                <div data-matcher-quiz>
                    {{-- The template keeps its placeholders so the script
                         substitutes into the clinic's sentence rather than
                         pattern-matching digits out of a rendered string —
                         which breaks the moment the copy uses Arabic-Indic
                         numerals or puts the total first. --}}
                    <p
                        class="text-sm text-muted"
                        data-matcher-progress
                        data-template="{{ __('matcher.progress', ['current' => ':current', 'total' => ':total']) }}"
                    >
                        {{ __('matcher.progress', ['current' => 1, 'total' => count($questions)]) }}
                    </p>

                    <h3 class="mt-3 font-display text-xl font-semibold text-ink" data-matcher-question>
                        {{ $questions[0]['text'] }}
                    </h3>

                    {{-- A radiogroup: arrow keys move between options, which is
                         what a keyboard user expects of a single-choice list. --}}
                    <div class="mt-6 space-y-3" role="group" aria-labelledby="matcher-heading" data-matcher-options>
                        @foreach ($questions[0]['options'] as $option)
                            <button
                                type="button"
                                data-matcher-option="{{ $option['id'] }}"
                                class="block w-full rounded-lg bg-white p-4 text-start text-sm leading-relaxed text-ink ring-1 ring-line transition hover:ring-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark"
                            >
                                {{ $option['text'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            data-matcher-back
                            class="rounded-pill px-4 py-2 text-sm font-medium text-accent-dark ring-1 ring-line hover:bg-sage/60 disabled:cursor-not-allowed disabled:opacity-40"
                            disabled
                        >
                            {{ __('matcher.back') }}
                        </button>

                        <p class="text-xs leading-relaxed text-muted">{{ __('matcher.privacy_note') }}</p>
                    </div>
                </div>

                {{-- The result. Hidden until there is one. --}}
                <div data-matcher-result hidden>
                    <p class="text-sm text-muted">{{ __('matcher.result_heading') }}</p>

                    <h3 class="mt-2 font-display text-2xl font-semibold text-ink" data-matcher-result-name></h3>

                    <p class="mt-5 text-sm font-medium text-ink">{{ __('matcher.result_why_heading') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-muted" data-matcher-result-why></p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a
                            data-matcher-cta
                            {{-- The template, with the placeholder intact, so
                                 the script substitutes the package name into
                                 the clinic's sentence rather than building one
                                 of its own. --}}
                            data-template="{{ __('matcher.cta', ['package' => ':package']) }}"
                            href="#"
                            class="inline-flex items-center justify-center rounded-pill bg-accent px-6 py-3 text-sm font-semibold text-white transition hover:bg-accent-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark"
                        ></a>

                        <a
                            href="#packages"
                            class="inline-flex items-center justify-center rounded-pill px-6 py-3 text-sm font-medium text-accent-dark ring-1 ring-line hover:bg-sage/60"
                        >
                            {{ __('matcher.other_packages') }}
                        </a>

                        {{-- Retakeable without a page reload: the state lives
                             in one array in memory, so starting again is
                             emptying it. --}}
                        <button
                            type="button"
                            data-matcher-restart
                            class="inline-flex items-center justify-center rounded-pill px-6 py-3 text-sm font-medium text-muted underline hover:text-ink"
                        >
                            {{ __('matcher.restart') }}
                        </button>
                    </div>

                    <p class="mt-6 text-xs leading-relaxed text-muted">{{ __('matcher.not_binding') }}</p>
                </div>

                {{-- Announces the new question or the result to a screen
                     reader, since neither moves focus on its own. --}}
                <p class="sr-only" role="status" aria-live="polite" data-matcher-announce></p>
            </x-card>
        </div>
    </x-container>
</section>
