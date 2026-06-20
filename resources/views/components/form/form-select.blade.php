@props([
    'label' => '',
    'name' => '',
    'labelClass' => '',
])

<x-form.field>
    @if ($label)
        <x-form.label :for="$name" class="{{ $labelClass }}">
            {{ $label }}
        </x-form.label>
    @endif

    <x-form.select :name="$name" {{ $attributes }}>
        {{ $slot }}
    </x-form.select>

    <x-form.error :name="$name" />
</x-form.field>
