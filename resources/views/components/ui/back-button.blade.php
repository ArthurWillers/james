@props(['fallback', 'text' => 'Voltar', 'icon' => 'heroicon-o-arrow-left'])

@php
    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    
    // Se a URL anterior for igual à atual (ex: recarregou a página ou falhou na validação de form),
    // ou se não houver referer (cai na home/atual), usa a rota de fallback.
    $href = ($previousUrl === $currentUrl || $previousUrl === url('/')) ? $fallback : $previousUrl;
@endphp

<x-button color="outline" href="{{ $href }}" {{ $attributes->merge(['class' => 'bg-white']) }}>
    @if($icon)
        <x-dynamic-component :component="$icon" class="size-4" />
    @endif
    <span>{{ $text }}</span>
</x-button>
