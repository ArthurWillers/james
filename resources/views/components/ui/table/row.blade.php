@props(['mobile' => null, 'href' => null])
<div class="relative group/row last:rounded-b-lg border-b border-neutral-100 last:border-0">
    @if($href)
        <a href="{{ $href }}" {{ $attributes->merge(['class' => 'grid items-center hover:bg-neutral-100 transition-colors rounded-b-inherit cursor-pointer']) }}>
            {{ $slot }}
        </a>
    @else
        <div {{ $attributes->merge(['class' => 'grid items-center hover:bg-neutral-100 transition-colors rounded-b-inherit']) }}>
            {{ $slot }}
        </div>
    @endif

    @if($mobile)
        @if($href)
            <a href="{{ $href }}" class="sm:hidden p-4 space-y-3 block rounded-b-inherit hover:bg-neutral-100 transition-colors cursor-pointer">
                {{ $mobile }}
            </a>
        @else
            <div class="sm:hidden p-4 space-y-3 block rounded-b-inherit">
                {{ $mobile }}
            </div>
        @endif
    @endif
</div>
