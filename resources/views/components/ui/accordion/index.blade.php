@props([
    'id' => null,
    'open' => false,
    'panelClass' => '',
    'innerClass' => '',
])

@php
    $accordionId = $id ?? 'accordion-' . \Illuminate\Support\Str::uuid();
@endphp

<div
    x-data="{ open: @js($open), toggle() { this.open = !this.open; } }"
    data-open="{{ $open ? 'true' : 'false' }}"
    x-bind:data-open="open"
    {{ $attributes->merge(['class' => 't-acc']) }}
>
    {{ $trigger }}

    <div
        id="{{ $accordionId }}"
        class="t-acc-panel {{ $panelClass }}"
    >
        <div class="t-acc-panel-inner {{ $innerClass }}">
            {{ $slot }}
        </div>
    </div>
</div>
