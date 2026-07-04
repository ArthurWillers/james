<x-layouts.financial>
    <x-page-header title="Dashboard Financeiro" :action="route('financial.transactions.create')" actionText="Nova Transação" icon="heroicon-o-plus"></x-page-header>

    <!-- 1. Linha de Destaque: Os Grandes Números -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-finance.kpi-card 
            title="Saldo Líquido" 
            :value="formatCurrency($kpi['netBalance'])" 
            icon="heroicon-o-building-library" 
            :color="$kpi['netBalance'] >= 0 ? 'green' : 'red'" 
        />

        <x-finance.kpi-card 
            title="Total de Receitas" 
            :value="formatCurrency($kpi['income'])" 
            icon="heroicon-o-arrow-trending-up" 
            color="green" 
            :href="route('financial.transactions.index', ['type' => 'income'])"
        />

        <x-finance.kpi-card 
            title="Total de Despesas" 
            :value="formatCurrency($kpi['expense'])" 
            icon="heroicon-o-arrow-trending-down" 
            color="red" 
            :href="route('financial.transactions.index', ['type' => 'expense'])"
        />

        <x-finance.kpi-card 
            title="Saldo Atual" 
            :value="formatCurrency($kpi['currentBalance'])" 
            icon="heroicon-o-scale" 
            :color="$kpi['currentBalance'] >= 0 ? 'green' : 'red'" 
            :href="route('financial.transactions.index', ['is_posted' => 1])"
        />
    </div>

    <!-- Gráfico de Evolução do Saldo (Net Worth) -->
    <x-financial.net-worth-chart />

    <!-- 2. Previsibilidade e Saldos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 mt-8">
        
        <!-- Previsibilidade de Caixa -->
        <div class="lg:col-span-1 flex flex-col">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Previsibilidade de Caixa</h3>
            <div class="grid grid-cols-1 gap-4 flex-1">
                <x-card class="p-6 border-brand-100 bg-brand-50/30 flex flex-col justify-center">
                    <p class="text-sm font-medium text-neutral-600 mb-1">Projeção Mês Atual</p>
                    <p class="text-2xl font-bold {{ $projections['currentMonth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ formatCurrency($projections['currentMonth']) }}
                    </p>
                </x-card>

                <x-card class="p-6 border-brand-100 bg-brand-50/30 flex flex-col justify-center">
                    <p class="text-sm font-medium text-neutral-600 mb-1">Projeção Próximo Mês</p>
                    <p class="text-2xl font-bold {{ $projections['nextMonth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ formatCurrency($projections['nextMonth']) }}
                    </p>
                </x-card>
            </div>
        </div>

        <!-- Top Despesas (30 Dias) -->
        <div class="lg:col-span-1 flex flex-col">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Top Despesas (30 Dias)</h3>
            <x-card class="p-6 flex-1 flex flex-col items-center justify-center">
                @if(count($topExpensesChart['data']) > 0)
                    <div 
                        class="w-full flex-1 flex items-center justify-center min-h-[12rem]"
                        x-data="{
                            initChart() {
                                if (typeof echarts === 'undefined') {
                                    console.error('Apache ECharts is not loaded.');
                                    return;
                                }
                                
                                const chart = echarts.init(this.$refs.expensesChartContainer);
                                const data = {{ json_encode($topExpensesChart['data']) }};
                                const totalValue = {{ json_encode($topExpensesChart['total']) }};
                                
                                const formattedTotal = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(totalValue);
                                
                                const option = {
                                    tooltip: {
                                        trigger: 'item',
                                        formatter: function (params) {
                                            const val = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(params.value);
                                            return `${params.name}<br/><strong>${val}</strong> (${params.percent}%)`;
                                        },
                                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                        borderColor: '#e5e7eb',
                                        textStyle: { color: '#374151' },
                                        extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
                                    },
                                    graphic: {
                                        type: 'text',
                                        left: 'center',
                                        top: 'center',
                                        style: {
                                            text: 'Total\n' + formattedTotal,
                                            textAlign: 'center',
                                            fill: '#4b5563',
                                            fontSize: 14,
                                            fontWeight: 'bold'
                                        }
                                    },
                                    series: [
                                        {
                                            name: 'Despesas',
                                            type: 'pie',
                                            radius: ['55%', '75%'],
                                            minAngle: 15,
                                            itemStyle: {
                                                borderRadius: 8,
                                                borderColor: '#ffffff',
                                                borderWidth: 3,
                                                shadowBlur: 10,
                                                shadowColor: 'rgba(0, 0, 0, 0.05)',
                                                shadowOffsetX: 0,
                                                shadowOffsetY: 4
                                            },
                                            label: {
                                                show: false
                                            },
                                            data: data
                                        }
                                    ]
                                };
                                
                                chart.setOption(option);
                                
                                window.addEventListener('resize', () => {
                                    chart.resize();
                                });
                            }
                        }"
                        x-init="initChart"
                    >
                        <div x-ref="expensesChartContainer" class="w-full h-full"></div>
                    </div>
                @else
                    <div class="w-full h-full min-h-[12rem] flex flex-col items-center justify-center text-neutral-500">
                        <x-heroicon-o-chart-pie class="w-12 h-12 mb-2 text-neutral-300" />
                        <p class="text-sm">Nenhuma despesa registrada.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Saldos por Conta -->
        <div class="lg:col-span-1 flex flex-col">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Saldos por Conta</h3>
            <x-card class="p-6 flex-1 flex flex-col items-center justify-center">
                @if(count($accountBalancesChart) > 0)
                    <div 
                        class="w-full flex-1 flex items-center justify-center min-h-[12rem]"
                        x-data="{
                            initChart() {
                                if (typeof echarts === 'undefined') {
                                    console.error('Apache ECharts is not loaded.');
                                    return;
                                }
                                
                                const chart = echarts.init(this.$refs.chartContainer);
                                const data = {{ json_encode($accountBalancesChart) }};
                                
                                const total = data.reduce((acc, item) => acc + item.value, 0);
                                const formattedTotal = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(total);
                                
                                const option = {
                                    color: [
                                        '#4F46E5', // Indigo 600
                                        '#10B981', // Emerald 500
                                        '#F59E0B', // Amber 500
                                        '#EC4899', // Pink 500
                                        '#8B5CF6', // Violet 500
                                        '#06B6D4', // Cyan 500
                                        '#F43F5E', // Rose 500
                                    ],
                                    tooltip: {
                                        trigger: 'item',
                                        formatter: function (params) {
                                            const val = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(params.value);
                                            return `${params.name}<br/><strong>${val}</strong> (${params.percent}%)`;
                                        },
                                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                        borderColor: '#e5e7eb',
                                        textStyle: { color: '#374151' },
                                        extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 8px;'
                                    },
                                    graphic: {
                                        type: 'text',
                                        left: 'center',
                                        top: 'center',
                                        style: {
                                            text: 'Total\n' + formattedTotal,
                                            textAlign: 'center',
                                            fill: '#4b5563', // text-neutral-600
                                            fontSize: 14,
                                            fontWeight: 'bold'
                                        }
                                    },
                                    series: [
                                        {
                                            name: 'Saldo',
                                            type: 'pie',
                                            radius: ['55%', '75%'], // Make the donut ring a bit thinner for premium feel
                                            minAngle: 15, // Ensure small balances (like R$ 9) are visible
                                            itemStyle: {
                                                borderRadius: 8,
                                                borderColor: '#ffffff',
                                                borderWidth: 3,
                                                shadowBlur: 10,
                                                shadowColor: 'rgba(0, 0, 0, 0.05)',
                                                shadowOffsetX: 0,
                                                shadowOffsetY: 4
                                            },
                                            label: {
                                                show: false
                                            },
                                            data: data
                                        }
                                    ]
                                };
                                
                                chart.setOption(option);
                                
                                window.addEventListener('resize', () => {
                                    chart.resize();
                                });
                            }
                        }"
                        x-init="initChart"
                    >
                        <div x-ref="chartContainer" class="w-full h-full"></div>
                    </div>
                @else
                    <div class="w-full h-full min-h-[12rem] flex flex-col items-center justify-center text-neutral-500">
                        <x-heroicon-o-building-library class="w-12 h-12 mb-2 text-neutral-300" />
                        <p class="text-sm">Nenhuma conta com saldo positivo.</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- 4. Cartões de Crédito -->
    @if($cardsWidget->isNotEmpty())
        <div class="mb-8">
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
        <div class="lg:col-span-3">
            <div class="flex items-center gap-2 mb-4">
                <h3 class="text-lg font-bold text-neutral-900">Próximo Mês</h3>
                <span class="text-sm font-medium text-neutral-500 bg-neutral-100 px-2 py-0.5 rounded-md">até {{ now()->addMonthNoOverflow()->format('d/m') }}</span>
            </div>
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
                        <li class="p-8 text-center text-sm text-neutral-500">Nada no radar para o próximo mês.</li>
                    @endforelse
                </ul>
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