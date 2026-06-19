@props([
    'tag' => 'div',
    'href' => null,
    'size' => null,
])

@php
    $tag = $href ? 'a' : $tag;
    $isInteractive = (bool) $href;

    $baseClasses = 'bg-white dark:bg-white/10 border border-accent/30 dark:border-accent/20 shadow-sm transition-all duration-200';
    
    $sizeClasses = match ($size) {
        'sm' => 'p-4 rounded-lg',
        default => 'p-6 rounded-xl',
    };

    $interactiveClasses = $isInteractive 
        ? 'hover:border-accent hover:shadow-md hover:-translate-y-1' 
        : '';

    $classes = trim("$baseClasses $sizeClasses $interactiveClasses");
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $tag }}>
