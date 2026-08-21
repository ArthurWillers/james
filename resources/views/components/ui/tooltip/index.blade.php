@props([
    'text' => '',
    'id' => null,
    'position' => 'top',
    'contentClass' => '',
])

@php
    $tooltipId = $id ?? 'tooltip-' . \Illuminate\Support\Str::uuid();
    $tooltipPosition = in_array($position, ['top', 'bottom'], true) ? $position : 'top';
@endphp

<span {{ $attributes->merge(['class' => 't-tt-wrap']) }}>
    {{ $slot }}

    <span
        id="{{ $tooltipId }}"
        role="tooltip"
        data-position="{{ $tooltipPosition }}"
        class="t-tt {{ $contentClass }}"
    >
        {{ $text }}
    </span>
</span>
