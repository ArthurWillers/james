@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'viewable' => false,
    'labelClass' => '',
    'numeric' => false,
])

@php
    $baseClasses =
        'w-full border appearance-none text-sm rounded-xl block py-2.5 px-4 bg-white disabled:shadow-none shadow-xs focus:shadow-lg text-neutral-700 disabled:text-neutral-400 placeholder-neutral-400 disabled:placeholder-neutral-400/70 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors duration-300';
    $errorClasses = $errors->has($name)
        ? 'border-red-500 focus:border-red-500 focus:ring-red-400/30'
        : 'border-neutral-200';
    $classes = $baseClasses . ' ' . $errorClasses;
@endphp

<div class="grid w-full items-center gap-1.5">

    <label for="{{ $name }}"
        class="inline-flex items-center text-sm font-semibold text-neutral-700 {{ $labelClass }}">
        {{ $label }}
    </label>

    <div class="relative" @if ($viewable) x-data="{ show: false }" @endif>
        <input
            @if ($viewable) :type="show ? 'text' : 'password'"
            @else
            type="{{ $numeric ? 'text' : $type }}" @endif
            id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
            value="{{ $value }}" {{ $attributes->merge(['class' => $classes]) }}
            @if ($numeric) x-data
                @input="$event.target.value = $event.target.value.replace(/[^0-9.,]/g, '')"
                inputmode="decimal" @endif />

        @if ($viewable)
            <div class="absolute top-0 bottom-0 flex items-center gap-x-1.5 pe-3 end-0 text-xs text-neutral-400">
                <button type="button" x-on:click="show = !show"
                    class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none h-8 text-sm rounded-md w-8 inline-flex -ms-1.5 -me-1.5 bg-transparent hover:bg-neutral-800/5 text-neutral-500 hover:text-neutral-800 transition-colors duration-300 cursor-pointer">
                    {{-- Ícone de "escondido" (olho cortado) --}}
                    <svg x-show="!show" xmlns="http://www.w.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                        class="size-4">
                        <path fill-rule="evenodd"
                            d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l10.5 10.5a.75.75 0 1 0 1.06-1.06l-1.322-1.323a7.012 7.012 0 0 0 2.16-3.11.87.87 0 0 0 0-.567A7.003 7.003 0 0 0 4.82 3.76l-1.54-1.54Zm3.196 3.195 1.135 1.136A1.502 1.502 0 0 1 9.45 8.389l1.136 1.135a3 3 0 0 0-4.109-4.109Z"
                            clip-rule="evenodd"></path>
                        <path
                            d="m7.812 10.994 1.816 1.816A7.003 7.003 0 0 1 1.38 8.28a.87.87 0 0 1 0-.566 6.985 6.985 0 0 1 1.113-2.039l2.513 2.513a3 3 0 0 0 2.806 2.806Z">
                        </path>
                    </svg>

                    {{-- Ícone de "visível" (olho aberto) --}}
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                        class="size-4" style="display: none;">
                        <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"></path>
                        <path fill-rule="evenodd"
                            d="M1.38 8.28a.87.87 0 0 1 0-.566 7.003 7.003 0 0 1 13.238.006.87.87 0 0 1 0 .566A7.003 7.003 0 0 1 1.379 8.28ZM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <x-form-error name="{{ $name }}" />
</div>
