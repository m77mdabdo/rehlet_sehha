@php
    /*
     * Whether a real photograph of the practitioner exists yet.
     *
     * Deliberately not wired to a stock image. See x-photo-frame: on this page
     * a stand-in is not a placeholder, it is a false claim about who will be
     * treating you. The clinic's own photographs are coming; until they do,
     * the frame holds their shape.
     */
    $portrait = null;
    $clinicPhoto = null;
@endphp

<x-page-shell
    :eyebrow="__('about.eyebrow')"
    :title="__('about.page_title')"
    :lead="__('about.page_lead')"
    :meta-title="__('about.meta_title')"
    :meta-description="__('about.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.about'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('about.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('about.cta.lead') }}</x-slot:cta-lead>

    <section class="py-20 sm:py-28" aria-labelledby="practitioner-heading">
        <x-container>
            <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-16">
                {{-- The portrait's reserved space. Offset downward on large
                     screens so the pair is a composition, not two columns. --}}
                <div class="lg:col-span-5 lg:mt-6">
                    @if ($portrait)
                        <x-photo :slug="$portrait" :alt="__('about.portrait_alt', ['name' => __('about.name')])" sizes="(min-width: 1024px) 36vw, 100vw" />
                    @else
                        <x-photo-frame :label="__('about.portrait_pending')" class="mx-auto max-w-sm shadow-sm" />
                        <p class="mt-4 text-center text-sm text-muted lg:text-start">{{ __('about.portrait_pending_title') }}</p>
                    @endif
                </div>

                <div class="lg:col-span-7">
                    <h2 id="practitioner-heading" class="font-display text-2xl font-semibold text-ink sm:text-3xl">
                        {{ __('about.name') }}
                    </h2>

                    <p class="mt-2 text-accent-dark">{{ __('about.title') }}</p>

                    <h3 class="mt-10 text-sm font-semibold tracking-wide text-accent-dark uppercase">
                        {{ __('about.philosophy_heading') }}
                    </h3>

                    <p class="mt-4 leading-relaxed text-pretty text-muted">{{ __('about.philosophy') }}</p>

                    <h3 class="mt-10 text-sm font-semibold tracking-wide text-accent-dark uppercase">
                        {{ __('about.credentials_heading') }}
                    </h3>

                    <dl class="mt-4 divide-y divide-line border-y border-line">
                        @foreach (__('about.credentials') as $key => $value)
                            <div class="py-4">
                                <dt class="sr-only">{{ $key }}</dt>
                                <dd class="text-sm leading-relaxed text-muted">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-8 rounded-xl bg-sage/50 p-5">
                        <h3 class="text-sm font-semibold text-ink">{{ __('about.registration_heading') }}</h3>
                        <p class="mt-2 text-sm text-muted">{{ __('about.registration') }}</p>
                        <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('about.registration_note') }}</p>
                    </div>
                </div>
            </div>
        </x-container>
    </section>

    {{-- What she treats, read from the same source the services page uses so
         the two can never drift. --}}
    <section class="bg-sage/50 py-20 sm:py-24" aria-labelledby="treats-heading">
        <x-container>
            <x-section-heading
                id="treats-heading"
                :title="__('about.treats_heading')"
                :lead="__('about.treats_lead')"
            />

            <ul class="mt-10 flex flex-wrap gap-3">
                @foreach ($footerServices as $service)
                    <li>
                        <a
                            href="{{ route('services') }}"
                            class="inline-flex rounded-pill bg-white px-4 py-2 text-sm text-ink ring-1 ring-line transition hover:ring-accent"
                        >{{ $service->name }}</a>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>

    {{-- The clinic's own space, reserved the same way. --}}
    <section class="py-20 sm:py-24" aria-labelledby="clinic-heading">
        <x-container>
            <h2 id="clinic-heading" class="font-display text-2xl font-semibold text-ink sm:text-3xl">
                {{ __('about.clinic_photo_heading') }}
            </h2>

            <div class="mt-8 grid gap-6 sm:grid-cols-3">
                @if ($clinicPhoto)
                    <x-photo :slug="$clinicPhoto" :alt="__('about.clinic_photo_heading')" class="sm:col-span-2" />
                @else
                    {{-- Two frames at different sizes and offsets: the shape the
                         real photographs will take, held now so nothing moves
                         when they arrive. --}}
                    <x-photo-frame ratio="aspect-3/2" :label="__('about.clinic_photo_pending')" class="sm:col-span-2" :size="140" />
                    <x-photo-frame ratio="aspect-3/2" :label="__('about.clinic_photo_pending')" class="sm:mt-10" :size="96" />
                @endif
            </div>

            <p class="mt-4 text-sm text-muted">{{ __('about.clinic_photo_pending') }}</p>
        </x-container>
    </section>
</x-page-shell>
