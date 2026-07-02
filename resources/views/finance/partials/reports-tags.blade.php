<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Expenses -->
    <div>
        <h4 class="text-sm font-bold text-neutral-900 mb-4 uppercase tracking-wider flex items-center gap-2">
            <x-heroicon-s-arrow-trending-down class="size-4 text-red-500" />
            Top Categorias - Despesas
        </h4>
        <div class="space-y-6">
            @forelse($expenses as $index => $item)
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-6 h-6 rounded flex items-center justify-center text-xs font-bold" 
                         style="background-color: {{ $item['color'] }}20; color: {{ $item['color'] }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex items-center gap-2 truncate">
                                <x-dynamic-component :component="$item['icon']" class="size-4 shrink-0" style="color: {{ $item['color'] }}" />
                                <span class="font-semibold text-neutral-900 text-sm truncate">{{ $item['name'] }}</span>
                            </div>
                            <span class="font-bold text-sm text-red-600 shrink-0">- R$ {{ number_format($item['value'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs text-neutral-500">{{ $item['percentage'] }}% do total</span>
                        </div>
                        <div class="w-full bg-neutral-100 rounded-full h-2 overflow-hidden" x-data="{ width: 0 }" x-init="setTimeout(() => width = {{ $item['percentage'] }}, 100)">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                                 :style="`width: ${width}%; background-color: {{ $item['color'] }}`"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-neutral-500 py-4">Nenhuma despesa no período.</div>
            @endforelse
        </div>
    </div>

    <!-- Incomes -->
    <div>
        <h4 class="text-sm font-bold text-neutral-900 mb-4 uppercase tracking-wider flex items-center gap-2">
            <x-heroicon-s-arrow-trending-up class="size-4 text-emerald-500" />
            Top Categorias - Receitas
        </h4>
        <div class="space-y-6">
            @forelse($incomes as $index => $item)
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-6 h-6 rounded flex items-center justify-center text-xs font-bold" 
                         style="background-color: {{ $item['color'] }}20; color: {{ $item['color'] }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex items-center gap-2 truncate">
                                <x-dynamic-component :component="$item['icon']" class="size-4 shrink-0" style="color: {{ $item['color'] }}" />
                                <span class="font-semibold text-neutral-900 text-sm truncate">{{ $item['name'] }}</span>
                            </div>
                            <span class="font-bold text-sm text-green-600 shrink-0">+ R$ {{ number_format($item['value'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs text-neutral-500">{{ $item['percentage'] }}% do total</span>
                        </div>
                        <div class="w-full bg-neutral-100 rounded-full h-2 overflow-hidden" x-data="{ width: 0 }" x-init="setTimeout(() => width = {{ $item['percentage'] }}, 100)">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                                 :style="`width: ${width}%; background-color: {{ $item['color'] }}`"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-neutral-500 py-4">Nenhuma receita no período.</div>
            @endforelse
        </div>
    </div>
</div>
