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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 mt-8 items-stretch">
        
        <!-- Previsibilidade de Caixa -->
        <div class="lg:col-span-1 flex flex-col h-full">
            <x-card class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm h-full flex flex-col">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Projeção de Caixa</h3>
                
                <div class="flex-1 flex flex-col justify-center gap-4">
                    <!-- Mês Atual -->
                    <div class="bg-neutral-50/70 rounded-xl p-5 border border-neutral-100 relative overflow-hidden transition-all hover:shadow-sm">
                        <div class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l {{ $projections['currentMonth'] >= 0 ? 'from-green-50' : 'from-red-50' }} to-transparent opacity-50"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-neutral-500 flex items-center gap-1.5">
                                    <x-heroicon-s-calendar class="w-4 h-4 text-neutral-400" /> Fim do Mês Atual
                                </p>
                                <p class="text-2xl font-bold mt-1 {{ $projections['currentMonth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ formatCurrency($projections['currentMonth']) }}
                                </p>
                            </div>
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-sm {{ $projections['currentMonth'] >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                @if($projections['currentMonth'] >= 0)
                                    <x-heroicon-o-arrow-trending-up class="w-6 h-6" />
                                @else
                                    <x-heroicon-o-arrow-trending-down class="w-6 h-6" />
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Próximo Mês -->
                    <div class="bg-neutral-50/70 rounded-xl p-5 border border-neutral-100 relative overflow-hidden transition-all hover:shadow-sm">
                        <div class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l {{ $projections['nextMonth'] >= 0 ? 'from-green-50' : 'from-red-50' }} to-transparent opacity-50"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-neutral-500 flex items-center gap-1.5">
                                    <x-heroicon-s-calendar class="w-4 h-4 text-neutral-400" /> Fim do Próximo Mês
                                </p>
                                <p class="text-2xl font-bold mt-1 {{ $projections['nextMonth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ formatCurrency($projections['nextMonth']) }}
                                </p>
                            </div>
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-sm {{ $projections['nextMonth'] >= 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                @if($projections['nextMonth'] >= 0)
                                    <x-heroicon-o-arrow-trending-up class="w-6 h-6" />
                                @else
                                    <x-heroicon-o-arrow-trending-down class="w-6 h-6" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Lado Direito (lg:col-span-1): "Saldos por Conta" -->
        <div class="lg:col-span-1 flex flex-col h-full">
            <x-card class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm h-full flex flex-col">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Saldos por Conta</h3>
                @if(count($accountBalancesChart) > 0)
                    <div 
                        class="w-full flex-1 min-h-[12rem]"
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
                                        '#4F46E5', '#10B981', '#F59E0B', '#EC4899', '#8B5CF6', '#06B6D4', '#F43F5E',
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
                                            label: { show: false },
                                            data: data
                                        }
                                    ]
                                };
                                
                                chart.setOption(option);
                                window.addEventListener('resize', () => { chart.resize(); });
                            }
                        }"
                        x-init="initChart"
                    >
                        <div x-ref="chartContainer" class="w-full h-full absolute inset-0" style="position: relative;"></div>
                    </div>
                @else
                    <div class="w-full flex-1 min-h-[12rem] flex flex-col items-center justify-center text-neutral-500">
                        <x-heroicon-o-building-library class="w-12 h-12 mb-2 text-neutral-300" />
                        <p class="text-sm">Nenhuma conta com saldo positivo.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Top 5 Despesas -->
        <div class="lg:col-span-1 flex flex-col h-full">
            <x-card class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm h-full flex flex-col">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Top Despesas (30 Dias)</h3>
                @if(count($topExpenseTags) > 0)
                    <div class="space-y-6 flex-1 mt-2">
                        @foreach($topExpenseTags as $index => $tag)
                            <x-finance.tag-list-item :index="$index + 1" :item="$tag" type="expense" :showBar="true" />
                        @endforeach
                    </div>
                @else
                    <div class="w-full flex-1 min-h-[12rem] flex flex-col items-center justify-center text-neutral-500">
                        <x-heroicon-o-tag class="w-12 h-12 mb-2 text-neutral-300" />
                        <p class="text-sm">Nenhuma despesa registrada.</p>
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
            <x-finance.transaction-table :transactions="$radar" />
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