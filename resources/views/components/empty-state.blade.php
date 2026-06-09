@props(['message', 'icon' => null, 'actionText' => null, 'actionRoute' => null])

<div class="py-12 px-4 text-center">
    @if ($icon)
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center">
                <x-icon :name="$icon" class="w-8 h-8 text-neutral-400" />
            </div>
        </div>
    @endif

    <div class="text-neutral-600">
        <p class="font-medium text-base">{{ $message }}</p>
    </div>

    @if ($actionText && $actionRoute)
        <div class="mt-6">
            <x-button :href="$actionRoute">
                {{ $actionText }}
            </x-button>
        </div>
    @endif
</div>
