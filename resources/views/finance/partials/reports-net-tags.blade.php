<div>
    <h4 class="text-sm font-bold text-neutral-900 mb-4 uppercase tracking-wider flex items-center gap-2">
        <x-heroicon-s-scale class="size-4 text-neutral-400" />
        Saldo Líquido por Tag
    </h4>
    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
        @forelse($netTags as $item)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 truncate">
                    <x-dynamic-component :component="$item['icon']" class="size-4 shrink-0" style="color: {{ $item['color'] }}" />
                    <span class="font-medium text-neutral-900 text-sm truncate">{{ $item['name'] }}</span>
                </div>
                @if($item['value'] >= 0)
                    <span class="font-bold text-sm text-green-600 shrink-0">+ R$ {{ number_format($item['value'], 2, ',', '.') }}</span>
                @else
                    <span class="font-bold text-sm text-red-600 shrink-0">- R$ {{ number_format(abs($item['value']), 2, ',', '.') }}</span>
                @endif
            </div>
        @empty
            <div class="text-sm text-neutral-500 py-2">Nenhuma tag no período.</div>
        @endforelse
    </div>
</div>
