@props(['mobile' => null])
<div class="relative group/row last:rounded-b-lg">
    <div {{ $attributes->merge(['class' => 'grid items-center hover:bg-neutral-50 transition-colors rounded-b-inherit']) }}>
        {{ $slot }}
    </div>

    @if($mobile)
    <div class="sm:hidden p-4 space-y-3 block rounded-b-inherit">
        {{ $mobile }}
    </div>
    @endif
</div>
