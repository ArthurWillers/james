@props([
    'position' => 'top', // Valor padrão: abre para cima
    'contentClass' => '',
    'accent' => false,
])

@php
    $posClasses = [
        'top' => 'bottom-full mb-2 left-1/2 -translate-x-1/2',
        'top-start' => 'bottom-full mb-2 left-0',
        'top-end' => 'bottom-full mb-2 right-0',
        'bottom' => 'top-full mt-2 left-1/2 -translate-x-1/2',
        'bottom-start' => 'top-full mt-2 left-0',
        'bottom-end' => 'top-full mt-2 right-0',
    ];
    $dropdownPosition = $posClasses[$position] ?? $posClasses['top'];
@endphp

<div x-data="{ open: false }" @click.outside="open = false" {{ $attributes->merge(['class' => 'relative']) }}>

    {{-- Slot para o gatilho (Botão) --}}
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    {{-- Slot para o conteudo --}}
    <div x-show="open" x-transition x-cloak @class([
        'absolute z-50 rounded-lg border bg-white p-1 shadow-lg',
        $dropdownPosition,
        'border-accent' => $accent,
        'border-neutral-300' => !$accent,
        $contentClass,
    ])>
        {{ $content }}
    </div>

</div>
