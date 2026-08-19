@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'size' => 'default',
    /*
     * A real prop, rather than Blade's boolean-attribute directive at the
     * call site.
     *
     * Those directives compile to inline conditional PHP fragments, and a
     * component tag re-parses its attributes into an array — so the fragments
     * land outside the conditional and the view fails to compile with a
     * bewildering "unexpected endif" pointing at the wrong file. They work
     * perfectly on a plain HTML element; on a component tag they do not.
     *
     * NOTE FOR ANYONE EDITING THIS COMMENT: Blade compiles directives and
     * component tags inside comments too. Naming the directive, or writing a
     * component tag here, breaks the build — which is how this note reached
     * its third draft.
     */
    'disabled' => false,
])

@php
    // Every colour here resolves to a design token — no component invents one.
    $variants = [
        'primary' => 'bg-accent text-white shadow-sm hover:bg-accent-dark',
        'ghost' => 'bg-transparent text-ink ring-1 ring-line hover:bg-sage',
        'light' => 'bg-white text-ink shadow-sm hover:bg-sage',
    ];

    $sizes = [
        'default' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
        'sm' => 'px-4 py-2 text-sm',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-2 rounded-pill font-medium',
        'transition-colors duration-200',
        // A disabled control must LOOK disabled, or a patient keeps pressing it.
        $disabled ? 'cursor-not-allowed opacity-50' : '',
        // No focus:outline-none anywhere: the base stylesheet owns the focus
        // ring so it can never be removed from one component by accident.
        $sizes[$size] ?? $sizes['default'],
        $variants[$variant] ?? $variants['primary'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
