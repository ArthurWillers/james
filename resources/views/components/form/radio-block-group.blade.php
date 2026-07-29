@props(['legend' => ''])

<fieldset>
    @if($legend)
        <legend class="text-sm font-semibold text-neutral-700 mb-3">{{ $legend }}</legend>
    @endif
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 sm:flex sm:flex-row gap-2 items-stretch bg-neutral-100 rounded-xl p-1']) }}>
        {{ $slot }}
    </div>
</fieldset>
