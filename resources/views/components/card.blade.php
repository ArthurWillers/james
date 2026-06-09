@props(['tag' => 'div', 'href' => null])

@php
    $tag = $href ? 'a' : $tag;
    $classes = $attributes->get('class', '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-lg p-6 border border-neutral-200 ' . $classes]) }}>
    {{ $slot }}
    </{{ $tag }}>
