@props(['title', 'description' => null, 'action' => null, 'actionText' => null, 'icon' => null])

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-neutral-900">{{ $title }}</h2>
        @if($description)
            <p class="text-sm text-neutral-500 mt-1">{{ $description }}</p>
        @endif
    </div>

    @if($slot->isNotEmpty() || $action)
        <div class="flex items-center gap-3 w-full sm:w-auto">
            {{ $slot }}
            
            @if ($action)
                <x-button :href="$action" class="w-full sm:w-auto">
                    @if ($icon)
                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="size-5!" />
                    @endif
                    {{ $actionText }}
                </x-button>
            @endif
        </div>
    @endif
</div>
