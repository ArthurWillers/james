@props([
    'color' => 'default',
    'href' => null,
    'type' => 'button',
    'icon' => '',
])

@php
    $baseClasses =
        'cursor-pointer inline-flex items-center justify-center font-semibold px-3 py-2 text-sm rounded-lg disabled:opacity-75 disabled:cursor-default gap-1';

    $colorClasses = match ($color) {
        'red' => 'bg-red-500 hover:bg-red-700 text-white',
        'accent' => 'bg-accent hover:bg-[color-mix(in_srgb,var(--color-accent),#000_10%)] text-white',
        'outline' => 'border border-neutral-300 hover:bg-neutral-100 text-neutral-500 hover:text-neutral-900',
        'none' => '',
        default => 'bg-neutral-800 hover:bg-neutral-700 text-white border border-black/10',
    };

    $shadowClasses = match ($color) {
        'none' => '',
        'outline' => '',
        default => 'shadow-[inset_0px_1px_rgba(255,255,255,0.5)]',
    };

    $finalClasses = implode(' ', [$baseClasses, $colorClasses, $shadowClasses]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $finalClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" :disabled="loading" {{ $attributes->merge(['class' => $finalClasses]) }}>
        {{-- O spinner é mostrado quando 'loading' é true --}}
        <svg x-show="loading" class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" style="display: none;">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>

        {{-- O conteúdo original é mostrado quando 'loading' é false --}}
        <span x-show="!loading" class="inline-flex items-center gap-1">
            {{ $slot }}
        </span>
    </button>
@endif
