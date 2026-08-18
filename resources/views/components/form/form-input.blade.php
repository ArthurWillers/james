@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'viewable' => false,
    'labelClass' => '',
    'numeric' => false,
    'currency' => false,
    'allowNegative' => false,
    'bag' => 'default',
])

<x-field>
    @if ($label)
        <x-label :for="$name" class="{{ $labelClass }}">
            {{ $label }}
        </x-label>
    @endif

    <x-input
        :name="$name"
        :type="$type"
        :value="$value"
        :placeholder="$placeholder"
        :viewable="$viewable"
        :numeric="$numeric"
        :currency="$currency"
        :allow-negative="$allowNegative"
        :bag="$bag"
        {{ $attributes }}
    />

    <x-error :name="$name" :bag="$bag" />
</x-field>
