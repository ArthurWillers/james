@props([
    'contact' => null,
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'h-6 w-6 text-xs',
        'md' => 'h-8 w-8 text-sm',
        'lg' => 'h-14 w-14 text-xl',
        default => 'h-8 w-8 text-sm',
    };

    $initials = $contact ? $contact->initials() : '?';
    $hasAvatar = $contact && $contact->hasMedia('avatar');
@endphp

<div class="relative inline-flex items-center justify-center overflow-hidden rounded-full {{ $sizeClasses }} bg-[var(--color-accent)]/20">
    @if($hasAvatar)
        <img src="{{ $contact->avatar }}" alt="{{ $contact->name }}" class="h-full w-full object-cover">
    @else
        <span class="font-medium text-[var(--color-accent)]">{{ $initials }}</span>
    @endif
</div>
