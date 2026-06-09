@props([
    'label' => '',
    'name' => '',
    'labelClass' => '',
])

@php
    $baseClasses =
        'w-full border text-sm rounded-xl block py-2.5 px-4 bg-white disabled:shadow-none shadow-xs focus:shadow-lg text-neutral-700 disabled:text-neutral-400 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors duration-300';

    $errorClasses = $errors->has($name)
        ? 'border-red-500 focus:border-red-500 focus:ring-red-400/30' // Estilo de erro
        : 'border-neutral-200'; // Estilo padrão

    $classes = $baseClasses . ' ' . $errorClasses;
@endphp

<div class="grid w-full items-center gap-1.5">

    @if ($label)
        <label for="{{ $name }}"
            class="inline-flex items-center text-sm font-semibold text-neutral-700 {{ $labelClass }}">
            {{ $label }}
        </label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </select>

    <x-form-error name="{{ $name }}" />
</div>
