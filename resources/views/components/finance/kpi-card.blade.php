@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'neutral',
    'href' => null,
    'hideIconOnMobile' => false,
])

@php
    $colors = [
        'neutral' => [
            'text' => 'text-neutral-900',
            'hoverText' => '',
            'iconBg' => 'bg-neutral-100',
            'iconHoverBg' => 'group-hover:bg-neutral-200',
            'iconText' => 'text-neutral-600',
            'scaleIcon' => 'text-neutral-500',
        ],
        'green' => [
            'text' => 'text-green-600',
            'hoverText' => 'group-hover:text-green-700',
            'iconBg' => 'bg-green-100',
            'iconHoverBg' => 'group-hover:bg-green-200',
            'iconText' => 'text-green-600',
            'scaleIcon' => 'text-green-600',
        ],
        'red' => [
            'text' => 'text-red-600',
            'hoverText' => 'group-hover:text-red-700',
            'iconBg' => 'bg-red-100',
            'iconHoverBg' => 'group-hover:bg-red-200',
            'iconText' => 'text-red-600',
            'scaleIcon' => 'text-red-600',
        ],
        'brand' => [
            'text' => 'text-brand-600',
            'hoverText' => 'group-hover:text-brand-700',
            'iconBg' => 'bg-brand-100',
            'iconHoverBg' => 'group-hover:bg-brand-200',
            'iconText' => 'text-brand-600',
            'scaleIcon' => 'text-brand-600',
        ],
    ];

    $theme = $colors[$color] ?? $colors['neutral'];
@endphp

<x-card :href="$href" {{ $attributes->merge(['class' => 'p-6 group']) }}>
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                <p class="text-sm font-medium text-neutral-600 {{ $href ? 'group-hover:text-brand-600 transition-colors' : '' }}">{{ $title }}</p>
            </div>
            <p class="text-xl sm:text-2xl font-semibold {{ $theme['text'] }} break-words {{ $href ? $theme['hoverText'] . ' transition-colors' : '' }}">
                {{ $value }}
            </p>
            @if($slot->isNotEmpty())
                <div class="mt-1 text-xs text-neutral-500">
                    {{ $slot }}
                </div>
            @endif
        </div>
        @if($icon)
            <div class="{{ $hideIconOnMobile ? 'hidden sm:flex' : 'flex' }} w-10 h-10 sm:w-12 sm:h-12 {{ $theme['iconBg'] }} rounded-lg items-center justify-center flex-shrink-0 {{ $href ? $theme['iconHoverBg'] . ' transition-colors' : '' }}">
                <x-dynamic-component :component="$icon" class="w-5 h-5 sm:w-6 sm:h-6 {{ $theme['iconText'] }} {{ $href ? 'group-hover:scale-110 transition-transform' : '' }}" />
            </div>
        @endif
    </div>
</x-card>
