@props(['title', 'action' => null, 'actionText' => null, 'icon' => null])

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">{{ $title }}</h2>

    @if ($action)
        <x-button :href="$action">
            @if ($icon)
                <x-icon :name="$icon" class="size-5" />
            @endif
            {{ $actionText }}
        </x-button>
    @endif
</div>
