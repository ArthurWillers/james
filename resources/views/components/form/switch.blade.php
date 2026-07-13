@props([
    'name' => '',
    'checked' => false,
    'value' => '1',
    'label' => '',
])

<div x-data="{ 
    checked: {{ $attributes->has('x-model') ? $attributes->get('x-model') : ($checked ? 'true' : 'false') }},
    init() {
        if (this.$el.hasAttribute('x-model')) {
            this.$watch('checked', value => this.$dispatch('input', value));
        }
    }
}" class="flex items-center gap-3" {{ $attributes->whereStartsWith('x-model') }}>
    <button
        type="button"
        role="switch"
        :aria-checked="checked"
        @click="checked = !checked"
        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2"
        :class="{ 'bg-accent': checked, 'bg-neutral-200': !checked }"
        {{ $attributes }}
    >
        <span
            aria-hidden="true"
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $checked ? 'translate-x-5' : 'translate-x-0' }}"
            :class="{ 'translate-x-5': checked, 'translate-x-0': !checked }"
        ></span>
    </button>
    <input type="hidden" name="{{ $name }}" :value="checked ? '{{ $value }}' : '0'">
    @if($label)
        <span class="text-sm font-medium text-neutral-700 cursor-pointer" @click="checked = !checked">{{ $label }}</span>
    @endif
</div>
