@props(['count' => 0])

@if($count > 0)
    @php
        $label = $count === 1 ? '1 anexo' : "{$count} anexos";
    @endphp

    <x-tooltip :text="$label" class="shrink-0">
        <span
            class="inline-flex items-center gap-1 rounded-md bg-neutral-100 px-1.5 py-0.5 text-xs text-neutral-400"
            aria-label="{{ $label }}"
        >
            <x-heroicon-o-paper-clip class="size-3" />
            <span>{{ $count }}</span>
        </span>
    </x-tooltip>
@endif
