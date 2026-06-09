@props([
    'label' => '',
    'name' => '',
])

<div>
    <label for="{{ $name }}" class="flex items-center gap-x-2 cursor-pointer">
        <input id="{{ $name }}" name="{{ $name }}" type="checkbox"
            {{ $attributes->merge(['class' => 'peer sr-only']) }} />

        {{-- Checkbox Visual --}}
        <div
            class="flex h-[1.125rem] w-[1.125rem] shrink-0 items-center justify-center rounded-sm border
                    border-neutral-300
                    bg-white
                    shadow-xs transition-colors duration-200
                    peer-checked:border-neutral-900
                    peer-checked:bg-neutral-900
                    text-transparent peer-checked:text-white">

            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z"
                    clip-rule="evenodd" />
            </svg>
        </div>

        @if ($label)
            <span class="text-sm font-medium text-neutral-700">
                {{ $label }}
            </span>
        @endif
    </label>

    {{-- Mensagem de Erro --}}
    <x-form-error :name="$name" class="!mt-3" />
</div>
