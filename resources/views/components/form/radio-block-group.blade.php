@props([
    'legend' => '',
    'sliding' => true,
])

<fieldset>
    @if($legend)
        <legend class="text-sm font-semibold text-neutral-700 mb-3">{{ $legend }}</legend>
    @endif
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 sm:flex sm:flex-row gap-2 items-stretch bg-neutral-200 rounded-xl p-1 ' . ($sliding ? 'radio-block-group--sliding' : '')]) }}>
        @if($sliding)
            <span class="radio-block-indicator bg-white" aria-hidden="true"></span>
        @endif
        {{ $slot }}
    </div>
</fieldset>
