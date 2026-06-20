@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'viewable' => false,
    'labelClass' => '',
    'numeric' => false,
    'bag' => 'default',
])

<x-form.field>
    @if ($label)
        <x-form.label :for="$name" class="{{ $labelClass }}">
            {{ $label }}
        </x-form.label>
    @endif

    <x-form.input
        :name="$name"
        :type="$type"
        :value="$value"
        :placeholder="$placeholder"
        :viewable="$viewable"
        :numeric="$numeric"
        :bag="$bag"
        {{ $attributes }}
    />

    <x-form.error :name="$name" :bag="$bag" />
</x-form.field>
