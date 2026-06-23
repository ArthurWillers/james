@props([
    'model' => null,
    'size' => 'md',
    'icon' => null
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-6 h-6 text-xs',
        'md' => 'w-8 h-8 text-sm',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-2xl',
        '2xl' => 'w-24 h-24 text-4xl',
        '3xl' => 'w-32 h-32 text-5xl',
        default => 'w-8 h-8 text-sm',
    };

    $avatarUrl = $model->avatar ?? null;
    $initials = $model && method_exists($model, 'initials') ? $model->initials() : '';
@endphp

@if($icon)
    <div {{ $attributes->merge(['class' => "shrink-0 flex items-center justify-center border rounded-md font-medium bg-neutral-200 border-neutral-300 text-neutral-400 {$sizeClasses}"]) }}>
        <x-dynamic-component :component="$icon" class="w-[50%] h-[50%]" />
    </div>
@elseif($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="{{ $model->name ?? 'Avatar' }}" {{ $attributes->merge(['class' => "shrink-0 border rounded-md object-cover bg-neutral-200 border-[var(--color-accent)] {$sizeClasses}"]) }}>
@elseif(!empty($initials))
    <div {{ $attributes->merge(['class' => "shrink-0 flex items-center justify-center border rounded-md font-medium bg-neutral-200 border-neutral-300 text-neutral-700 {$sizeClasses}"]) }}>
        {{ $initials }}
    </div>
@else
    <div {{ $attributes->merge(['class' => "shrink-0 flex items-center justify-center border rounded-md font-medium bg-neutral-200 border-neutral-300 text-neutral-400 {$sizeClasses}"]) }}>
        <x-heroicon-o-user class="w-[50%] h-[50%]" />
    </div>
@endif
