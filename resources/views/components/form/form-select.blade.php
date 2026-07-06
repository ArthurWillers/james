@props([
    'label' => '',
    'name' => '',
    'labelClass' => '',
])

<x-field>
    @if ($label)
        <x-label :for="$name" class="{{ $labelClass }}">
            {{ $label }}
        </x-label>
    @endif

    <x-select :name="$name" {{ $attributes }}>
        {{ $slot }}
    </x-select>

    <x-error :name="$name" />
</x-field>
