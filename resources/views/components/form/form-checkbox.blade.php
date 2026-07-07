@props([
    'label' => '',
    'name' => '',
    'id' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div>
    <label @if($inputId) for="{{ $inputId }}" @endif class="flex items-center gap-x-2 cursor-pointer {{ $attributes->get('class') }}">
        <input @if($inputId) id="{{ $inputId }}" @endif name="{{ $name }}" type="checkbox"
            {{ $attributes->except('class')->merge(['class' => 'peer sr-only']) }} />

        {{-- Checkbox Visual --}}
        <div
            class="flex h-[1.125rem] w-[1.125rem] shrink-0 items-center justify-center rounded-sm border
                    border-neutral-300
                    bg-white
                    shadow-xs transition-all duration-200
                    peer-checked:border-neutral-900
                    peer-checked:bg-neutral-900
                    peer-focus-visible:ring-2 peer-focus-visible:ring-neutral-900 peer-focus-visible:ring-offset-2
                    text-transparent peer-checked:text-white">

            <x-heroicon-s-check class="size-3.5" />
        </div>

        @if ($label)
            <span class="text-sm font-medium text-neutral-700">
                {{ $label }}
            </span>
        @endif

        {{ $slot }}
    </label>

    {{-- Mensagem de Erro --}}
    <x-error :name="$name" class="mt-3!" />
</div>
