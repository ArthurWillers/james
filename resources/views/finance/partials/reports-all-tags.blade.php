<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- All Expenses -->
    <div>
        <h4 class="text-sm font-bold text-neutral-900 mb-4 uppercase tracking-wider flex items-center gap-2">
            <x-heroicon-s-tag class="size-4 text-neutral-400" />
            Todas as Tags - Despesas
        </h4>
        <div class="space-y-4">
            @forelse($allExpenses as $item)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 truncate">
                        <x-dynamic-component :component="$item['icon']" class="size-4 shrink-0" style="color: {{ $item['color'] }}" />
                        <span class="font-medium text-neutral-900 text-sm truncate">{{ $item['name'] }}</span>
                    </div>
                    <span class="font-bold text-sm text-red-600 shrink-0">- R$ {{ number_format($item['value'], 2, ',', '.') }}</span>
                </div>
            @empty
                <div class="text-sm text-neutral-500 py-2">Nenhuma despesa no período.</div>
            @endforelse
        </div>
    </div>

    <!-- All Incomes -->
    <div>
        <h4 class="text-sm font-bold text-neutral-900 mb-4 uppercase tracking-wider flex items-center gap-2">
            <x-heroicon-s-tag class="size-4 text-neutral-400" />
            Todas as Tags - Receitas
        </h4>
        <div class="space-y-4">
            @forelse($allIncomes as $item)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 truncate">
                        <x-dynamic-component :component="$item['icon']" class="size-4 shrink-0" style="color: {{ $item['color'] }}" />
                        <span class="font-medium text-neutral-900 text-sm truncate">{{ $item['name'] }}</span>
                    </div>
                    <span class="font-bold text-sm text-green-600 shrink-0">+ R$ {{ number_format($item['value'], 2, ',', '.') }}</span>
                </div>
            @empty
                <div class="text-sm text-neutral-500 py-2">Nenhuma receita no período.</div>
            @endforelse
        </div>
    </div>
</div>
