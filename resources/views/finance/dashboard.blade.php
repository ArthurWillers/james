<x-layouts.financial>
    <x-page-header title="Dashboard Financeiro" :action="route('financial.transactions.create')" actionText="Nova Transação" icon="heroicon-o-plus"></x-page-header>

    <!-- 1. Linha de Destaque: Os Grandes Números -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-finance.kpi-card 
            title="Saldo Líquido" 
            :value="formatCurrency($kpi['netBalance'])" 
            icon="heroicon-o-building-library" 
            color="neutral" 
        />

        <x-finance.kpi-card 
            title="Total de Receitas" 
            :value="formatCurrency($kpi['income'])" 
            icon="heroicon-o-arrow-trending-up" 
            color="green" 
        />

        <x-finance.kpi-card 
            title="Total de Despesas" 
            :value="formatCurrency($kpi['expense'])" 
            icon="heroicon-o-arrow-trending-down" 
            color="red" 
        />

        <x-finance.kpi-card 
            title="Saldo Atual" 
            :value="formatCurrency($kpi['currentBalance'])" 
            icon="heroicon-o-scale" 
            :color="$kpi['currentBalance'] >= 0 ? 'green' : 'red'" 
        />
    </div>

    <!-- 2. O Motor de Previsibilidade -->
    <h3 class="text-lg font-bold text-neutral-900 mb-4 mt-8">Previsibilidade de Caixa</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-card class="p-6 border-brand-100 bg-brand-50/30">
            <p class="text-sm font-medium text-neutral-600 mb-1">Projeção Fim do Mês Atual</p>
            <p class="text-2xl font-bold {{ $projections['currentMonth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ formatCurrency($projections['currentMonth']) }}
            </p>
            <p class="mt-1 text-xs text-neutral-500">
                Considerando transações agendadas e faturas abertas do mês.
            </p>
        </x-card>

        <x-card class="p-6 border-brand-100 bg-brand-50/30">
            <p class="text-sm font-medium text-neutral-600 mb-1">Projeção Fim do Próximo Mês</p>
            <p class="text-2xl font-bold {{ $projections['nextMonth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ formatCurrency($projections['nextMonth']) }}
            </p>
            <p class="mt-1 text-xs text-neutral-500">
                Projeção com base nas suas assinaturas e gastos fixos já conhecidos.
            </p>
        </x-card>
    </div>

    <!-- 3. Cartões de Crédito -->
    @if($cardsWidget->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Cartões de Crédito</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($cardsWidget as $card)
                    <x-finance.credit-card :card="$card" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 mt-8">
        
        <!-- Radar -->
        <div class="lg:col-span-2">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Próximos Dias</h3>
            <x-card class="p-0 overflow-hidden">
                <ul class="divide-y divide-neutral-100">
                    @forelse($radar as $item)
                        <li class="p-4 hover:bg-neutral-50 transition-colors flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $item->type === 'expense' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                        <x-dynamic-component :component="$item->icon" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 truncate max-w-[200px] sm:max-w-xs">{{ $item->title }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-neutral-500">{{ \Carbon\Carbon::parse($item->date)->formatShort() }}</span>
                                        <span class="text-xs text-neutral-400">&bull;</span>
                                        <span class="text-xs text-neutral-500">{{ $item->type_label }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold {{ $item->type === 'expense' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $item->type === 'expense' ? '-' : '+' }}{{ formatCurrency($item->amount) }}
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-sm text-neutral-500">Nada no radar para os próximos dias.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>

        <!-- Top 5 -->
        <div class="lg:col-span-1">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Top 5 Gastos do Mês</h3>
            <x-card class="p-4 space-y-4">
                @forelse($topExpenses as $expense)
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <x-dynamic-component :component="$expense->icon" class="w-4 h-4" style="color: {{ $expense->color_hex }}" />
                            <span class="text-sm font-medium text-neutral-700">
                                {{ $expense->tag_name }}
                            </span>
                        </div>
                        <span class="text-sm font-bold text-neutral-900">
                            {{ formatCurrency($expense->total) }}
                        </span>
                    </div>
                @empty
                    <div class="text-sm text-neutral-500 text-center py-4">Nenhuma despesa registrada neste mês.</div>
                @endforelse
            </x-card>
        </div>
    </div>

    <!-- 4. Últimas Transações -->
    @if($recentTransactions->isNotEmpty())
        <div class="flex justify-between items-center mb-4 mt-8">
            <h3 class="text-lg font-bold text-neutral-900">Últimas Transações</h3>
            <a href="{{ route('financial.transactions.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 flex items-center gap-1">
                Ver todas <x-heroicon-m-arrow-right class="size-4" />
            </a>
        </div>

        <x-finance.transaction-table :transactions="$recentTransactions" class="lg:mb-8" />
    @endif
</x-layouts.financial>