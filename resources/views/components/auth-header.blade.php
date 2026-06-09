@props(['title', 'description'])

<div class="flex w-full flex-col text-center">
    {{-- Título --}}
    <div class="text-2xl font-medium text-neutral-800 mb-2">
        {{ $title }}
    </div>

    {{-- Subtítulo --}}
    <div class="text-sm text-neutral-500">
        {{ $description }}
    </div>
</div>
