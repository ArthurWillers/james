@props([
    'href' => '#',
    'current' => false,
])

<a href="{{ $href }}" {{ $attributes->class([
        // Classes base, sempre aplicadas
        'rounded-lg h-10 lg:h-8 gap-3 flex relative items-center w-full text-sm px-3 text-start',
        // Classes para o estado ATIVO
        'bg-neutral-50 border border-neutral-300' => $current,
        // Classes para o estado INATIVO
        'text-neutral-500 hover:bg-black/7 hover:text-neutral-800' => !$current,
    ]) }}>
    {{ $slot }}
</a>