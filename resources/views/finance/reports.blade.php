<x-layouts.financial>
    <x-page-header title="Relatórios Financeiros" icon="heroicon-o-chart-pie"></x-page-header>

    <div x-data="reportsPage()" x-init="initCharts()" class="pb-2">

        <!-- Mobile Filters Toggle -->
        <div class="sm:hidden mb-6">
            <button @click="showFilters = !showFilters"
                    class="w-full flex justify-between items-center bg-white p-4 rounded-xl border border-neutral-200 shadow-sm">
                <span class="font-bold text-neutral-800">Filtros</span>
                <span class="flex items-center gap-2 text-sm font-semibold text-neutral-600">
                    <x-heroicon-o-funnel class="w-4 h-4" />
                    <span x-text="showFilters ? 'Ocultar' : 'Mostrar'"></span>
                    <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform"
                                               x-bind:class="showFilters && 'rotate-180'" />
                </span>
            </button>
        </div>

        <!-- Filters Bar -->
        <div class="w-full mb-6" x-bind:class="{ 'hidden sm:block': !showFilters }">
            <x-filter-bar :show-search="false" action="{{ route('financial.reports') }}" class="items-end! pe-2 py-3" button-class="sm:w-[44px] h-[44px]">
                <div class="flex flex-col sm:flex-row items-end gap-4 px-2 py-0">
                <div class="flex items-center gap-4 w-full md:w-auto flex-wrap sm:flex-nowrap">
                    <!-- Period -->
                    <div class="flex flex-col w-full sm:w-48">
                        <label class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1">Período</label>
                        <x-select name="period" x-model="period" @change="submitIfNotCustom()" class="w-full">
                            <option value="this_month" @selected($period === 'this_month')>Este Mês</option>
                            <option value="last_month" @selected($period === 'last_month')>Mês Passado</option>
                            <option value="last_3m" @selected($period === 'last_3m')>Últimos 3 Meses</option>
                            <option value="last_6m" @selected($period === 'last_6m')>Últimos 6 Meses</option>
                            <option value="this_year" @selected($period === 'this_year')>Este Ano</option>
                            <option value="next_month" @selected($period === 'next_month')>Próximo Mês</option>
                            <option value="next_6m" @selected($period === 'next_6m')>Próximos 6 Meses</option>
                            <option value="next_12m" @selected($period === 'next_12m')>Próximos 12 Meses</option>
                            <option value="all_time" @selected($period === 'all_time')>Todo o Tempo</option>
                            <option value="until_today" @selected($period === 'until_today')>Até Hoje</option>
                            <option value="custom" @selected($period === 'custom')>Personalizado</option>
                        </x-select>
                    </div>

                    <!-- Custom Dates -->
                    <div class="flex flex-col w-full sm:w-36">
                        <label class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1">Início</label>
                        <input type="date" name="startDate" value="{{ $startDate }}"
                               @change="period = 'custom'"
                               x-bind:disabled="period === 'all_time' || period === 'until_today'"
                               class="w-full border-neutral-200 text-sm rounded-xl block py-2.5 px-4 bg-white shadow-xs focus:shadow-lg text-neutral-700 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors disabled:opacity-50">
                    </div>
                    <div class="flex flex-col w-full sm:w-36">
                        <label class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1">Fim</label>
                        <input type="date" name="endDate" value="{{ $endDate }}"
                               @change="period = 'custom'"
                               x-bind:disabled="period === 'all_time' || period === 'until_today'"
                               class="w-full border-neutral-200 text-sm rounded-xl block py-2.5 px-4 bg-white shadow-xs focus:shadow-lg text-neutral-700 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors disabled:opacity-50">
                    </div>
                </div>

                <div class="flex items-center gap-4 w-full md:w-auto">
                    <!-- Interval -->
                    <div class="flex flex-col w-full md:w-36">
                        <label class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1">Intervalo</label>
                        <x-select name="interval" @change="submit()" class="w-full" :disabled="$isSingleDay">
                            <option value="auto" @selected($interval === 'auto')>Automático</option>
                            <option value="daily" @selected($interval === 'daily')>Diário</option>
                            <option value="weekly" @selected($interval === 'weekly')>Semanal</option>
                            <option value="monthly" @selected($interval === 'monthly')>Mensal</option>
                            <option value="yearly" @selected($interval === 'yearly')>Anual</option>
                        </x-select>
                    </div>

                    <!-- Accounts -->
                    <div class="flex flex-col w-full md:w-56">
                        <label class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1">Contas</label>
                        <x-select name="account" @change="submit()" class="w-full">
                            <option value="">Todas as Contas</option>
                            
                            <optgroup label="Por Tipo">
                                @foreach(\App\Enums\FinancialAccountType::cases() as $type)
                                    <option value="type:{{ $type->value }}" @selected($accountId === 'type:'.$type->value)>Todas: {{ $type->label() }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="Contas Específicas">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" @selected($accountId == $acc->id)>{{ $acc->name }}</option>
                                @endforeach
                            </optgroup>
                        </x-select>
                    </div>
                </div>
                </div>
            </x-filter-bar>
        </div>

        @if($isSingleDay)
            <div class="bg-accent/10 border border-accent/20 rounded-xl p-4 text-accent-700 flex items-center gap-3 mb-6">
                <x-heroicon-o-calendar-days class="w-6 h-6" />
                <div>
                    <strong class="block">Data Específica Selecionada</strong>
                    <span class="text-sm">Exibindo fluxo, categorias e transações de um único dia. Gráficos de evolução temporal ocultos.</span>
                </div>
            </div>
        @endif

        <!-- Sankey Chart -->
        <x-card class="p-6 mb-6">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Fluxo de Caixa</h3>
            <div class="relative w-full h-[400px]">
                <div x-ref="chartSankey" class="w-full h-full"></div>
            </div>
        </x-card>

        <!-- Evolution Chart -->
        @if(!$isSingleDay)
            <x-card class="p-6 mb-6">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Evolução de Saldo</h3>
                <div class="relative w-full h-[350px]">
                    <div x-ref="chartEvolution" class="w-full h-full"></div>
                </div>
            </x-card>

            <!-- Net Worth Evolution Chart -->
            <x-card class="p-6 mb-6">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Evolução do Saldo Líquido</h3>
                <div class="relative w-full h-[350px]">
                    <div x-ref="chartNetWorthEvolution" class="w-full h-full"></div>
                </div>
            </x-card>
        @endif

        <!-- Tags and Accounts Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 items-start">
            
            <!-- Coluna da Esquerda (2/3) -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Top Categories -->
                <x-card class="p-6">
                    <h3 class="text-lg font-bold text-neutral-900 mb-4 px-1">Top Tags</h3>
                    @include('finance.partials.reports-tags')
                </x-card>

                <!-- All Tags -->
                <x-card class="p-6">
                    <h3 class="text-lg font-bold text-neutral-900 mb-4 px-1">Todas as Tags</h3>
                    @include('finance.partials.reports-all-tags')
                </x-card>
            </div>

            <!-- Coluna da Direita (1/3) -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- Net Balance -->
                <x-card class="p-6">
                    <h3 class="text-lg font-bold text-neutral-900 mb-4 px-1">Saldo Líquido por Tag</h3>
                    @include('finance.partials.reports-net-tags')
                </x-card>

                @if(count($accountBalancesChart) > 0)
                <!-- Account Balances -->
                <x-card class="p-6 flex flex-col h-full min-h-[400px]">
                    <h3 class="text-lg font-bold text-neutral-900 mb-4 px-1">Saldos por Conta</h3>
                    <x-finance.account-balances-chart :chartData="$accountBalancesChart" />
                </x-card>
                @endif
            </div>
        </div>

        <!-- Transactions -->
        <div id="transactions-table" class="mb-6 pt-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="text-lg font-bold text-neutral-900">Transações do Período</h3>
                
                <button x-cloak x-show="selectedTagId !== null" 
                        @click="selectedTagId = null" 
                        class="cursor-pointer text-xs font-bold text-neutral-500 hover:text-neutral-900 bg-neutral-100 hover:bg-neutral-200 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                    <x-heroicon-s-x-mark class="size-3.5" />
                    Limpar Filtro
                </button>
            </div>
            @include('finance.partials.reports-transactions')
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportsPage', () => ({
            period: @json($period),
            showFilters: false,
            selectedTagId: null,

            filterByTag(tagId) {
                this.selectedTagId = tagId;
                const table = document.getElementById('transactions-table');
                if (table) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            },

            submitIfNotCustom() {
                if (this.period !== 'custom') {
                    this.submit();
                }
            },

            submit() {
                this.$root.querySelector('form').submit();
            },

            initCharts() {
                if (!window.echarts) {
                    console.error('ECharts not loaded.');
                    return;
                }

                this.renderSankey();

                @if(!$isSingleDay)
                    this.renderEvolution();
                    this.renderNetWorthEvolution();
                @endif
            },

            renderSankey() {
                const chart = window.echarts.init(this.$refs.chartSankey);
                const data = @json($sankey);

                chart.setOption({
                    tooltip: { trigger: 'item', triggerOn: 'mousemove' },
                    series: [{
                        type: 'sankey',
                        data: data.nodes,
                        links: data.links,
                        emphasis: { focus: 'adjacency' },
                        lineStyle: { color: 'gradient', curveness: 0.5 },
                        label: { color: 'rgba(0,0,0,0.7)', fontFamily: 'sans-serif' }
                    }]
                });

                window.addEventListener('resize', () => chart.resize());
            },

            @if(!$isSingleDay)
            renderEvolution() {
                const chart = window.echarts.init(this.$refs.chartEvolution);
                const raw = @json($evolution);
                const values = raw.map(i => i.value);
                const dates = raw.map(i => {
                    const p = String(i.date).split('-');
                    if (p.length === 1) return p[0];
                    if (p.length === 2) return p[1] + '/' + p[0];
                    return p[2] + '/' + p[1] + '/' + p[0];
                });
                const last = values[values.length - 1] || 0;
                const color = last >= 0 ? '#10b981' : '#ef4444';

                const fmt = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);

                chart.setOption({
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: '#ffffff',
                        padding: [12, 16],
                        textStyle: { color: '#1f2937', fontSize: 14 },
                        extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); border-radius: 0.5rem; border: 1px solid #f3f4f6;',
                        formatter: (params) => {
                            const item = params[0];
                            const d = raw[item.dataIndex];
                            return '<div class="text-xs text-gray-500 mb-2 uppercase font-medium">' + item.name + '</div>'
                                 + '<div class="text-sm font-bold text-gray-900 mb-2">' + fmt(item.value) + '</div>'
                                 + '<div class="text-xs text-emerald-600">↑ Receita: ' + fmt(d.income) + '</div>'
                                 + '<div class="text-xs text-red-600 mt-1">↓ Despesa: ' + fmt(d.expense) + '</div>';
                        },
                        axisPointer: { lineStyle: { color: '#d1d5db', type: 'dashed' } }
                    },
                    grid: { top: 20, right: 20, bottom: 20, left: 20, containLabel: true },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: dates,
                        axisLine: { show: false },
                        axisTick: { show: false },
                        axisLabel: { color: '#9ca3af', fontSize: 11 }
                    },
                    yAxis: {
                        type: 'value',
                        scale: true,
                        min: 'dataMin',
                        axisLabel: {
                            color: '#9ca3af',
                            fontSize: 11,
                            formatter: (v) => new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(v)
                        },
                        splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } }
                    },
                    series: [{
                        data: values,
                        type: 'line',
                        smooth: false,
                        showSymbol: false,
                        itemStyle: { color: color },
                        lineStyle: { color: color, width: 2 },
                        areaStyle: {
                            color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: color + '33' },
                                { offset: 1, color: color + '00' }
                            ])
                        },
                        markLine: {
                            data: [{ yAxis: 0 }],
                            symbol: 'none',
                            lineStyle: { color: '#d1d5db', type: 'solid', width: 1 },
                            label: { show: false }
                        }
                    }]
                });

                window.addEventListener('resize', () => chart.resize());
            },

            renderNetWorthEvolution() {
                const chart = window.echarts.init(this.$refs.chartNetWorthEvolution);
                const raw = @json($netWorthEvolution);
                const values = raw.map(i => i.value);
                const dates = raw.map(i => {
                    const p = String(i.date).split('-');
                    if (p.length === 1) return p[0];
                    if (p.length === 2) return p[1] + '/' + p[0];
                    return p[2] + '/' + p[1] + '/' + p[0];
                });
                const last = values[values.length - 1] || 0;
                const color = last >= 0 ? '#8B5CF6' : '#ef4444';

                const fmt = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);

                chart.setOption({
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: '#ffffff',
                        padding: [12, 16],
                        textStyle: { color: '#1f2937', fontSize: 14 },
                        extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); border-radius: 0.5rem; border: 1px solid #f3f4f6;',
                        formatter: (params) => {
                            const item = params[0];
                            const d = raw[item.dataIndex];
                            return '<div class="text-xs text-gray-500 mb-2 uppercase font-medium">' + item.name + '</div>'
                                 + '<div class="text-sm font-bold text-gray-900 mb-2">' + fmt(item.value) + '</div>'
                                 + '<div class="text-xs text-emerald-600">↑ Receita (Competência): ' + fmt(d.income) + '</div>'
                                 + '<div class="text-xs text-red-600 mt-1">↓ Despesa (Competência): ' + fmt(d.expense) + '</div>';
                        },
                        axisPointer: { lineStyle: { color: '#d1d5db', type: 'dashed' } }
                    },
                    grid: { top: 20, right: 20, bottom: 20, left: 20, containLabel: true },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: dates,
                        axisLine: { show: false },
                        axisTick: { show: false },
                        axisLabel: { color: '#9ca3af', fontSize: 11 }
                    },
                    yAxis: {
                        type: 'value',
                        scale: true,
                        min: 'dataMin',
                        axisLabel: {
                            color: '#9ca3af',
                            fontSize: 11,
                            formatter: (v) => new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(v)
                        },
                        splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } }
                    },
                    series: [{
                        data: values,
                        type: 'line',
                        smooth: false,
                        showSymbol: false,
                        itemStyle: { color: color },
                        lineStyle: { color: color, width: 2 },
                        areaStyle: {
                            color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: color + '33' },
                                { offset: 1, color: color + '00' }
                            ])
                        },
                        markLine: {
                            data: [{ yAxis: 0 }],
                            symbol: 'none',
                            lineStyle: { color: '#d1d5db', type: 'solid', width: 1 },
                            label: { show: false }
                        }
                    }]
                });

                window.addEventListener('resize', () => chart.resize());
            },
            @endif
        }));
    });
    </script>
</x-layouts.financial>
