@props(['mobile' => null])
<div class="relative group/row">
    <div {{ $attributes->merge(['class' => 'grid items-center hover:bg-neutral-50 transition-colors']) }}>
        {{ $slot }}
    </div>

    @if($mobile)
    <div class="sm:hidden p-4 space-y-3 block">
        {{ $mobile }}
    </div>
    @endif
</div>
