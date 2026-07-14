<x-card x-data="netWorthChart()" x-init="initChart()" class="w-full mb-6">
    <!-- Header: Title and value + Periods -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500 mb-1">Evolução do Saldo</h3>
            <div class="text-3xl font-bold text-gray-900" x-text="currentFormattedValue">
                R$ 0,00
            </div>
            <!-- Diff subtitle -->
            <div class="text-sm mt-1 flex items-center gap-1 min-h-[20px]" :class="diff >= 0 ? 'text-emerald-500' : 'text-red-500'">
                <template x-if="!loading">
                    <div class="flex items-center gap-1">
                        <span x-text="diff >= 0 ? '↑' : '↓'"></span>
                        <span x-text="diffFormatted"></span>
                        <span class="text-gray-400 font-normal">no período</span>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Period Selector -->
        <div class="mt-4 sm:mt-0 flex bg-gray-50 rounded-lg p-1 border border-gray-200">
            <template x-for="p in periods" :key="p">
                <button 
                    @click="setPeriod(p)"
                    class="px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200 uppercase cursor-pointer"
                    :class="period === p ? 'bg-white text-gray-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-700'"
                    x-text="p">
                </button>
            </template>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative w-full h-[300px]">
        <div x-ref="chartContainer" class="w-full h-full"></div>
        
        <!-- Loading overlay -->
        <div class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg" style="display: none;" x-show="loading">
            <svg class="animate-spin h-8 w-8 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</x-card>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('netWorthChart', () => ({
        period: '1m',
        periods: ['1m', '3m', '6m', '1y', 'all'],
        loading: true,
        chartInstance: null,
        currentValue: 0,
        diff: 0,
        
        get currentFormattedValue() {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(this.currentValue);
        },
        
        get diffFormatted() {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Math.abs(this.diff));
        },

        initChart() {
            // ECharts needs to be available via window.echarts
            if (!window.echarts) {
                console.error("ECharts is not loaded.");
                return;
            }
            
            this.chartInstance = window.echarts.init(this.$refs.chartContainer);
            
            // Handle window resize
            window.addEventListener('resize', () => {
                if (this.chartInstance) {
                    this.chartInstance.resize();
                }
            });

            this.fetchData();
        },

        setPeriod(p) {
            if (this.period === p) return;
            this.period = p;
            this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            try {
                const response = await fetch(`/financial/dashboard/chart-data?period=${this.period}`);
                const data = await response.json();
                this.updateChart(data);
            } catch (error) {
                console.error("Error fetching chart data", error);
            } finally {
                this.loading = false;
            }
        },

        updateChart(data) {
            if (!data || data.length === 0) return;

            const dates = data.map(item => {
                // Parse date string securely timezone-independent
                const parts = item.date.split('-');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                return d.toLocaleDateString('pt-BR', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: (this.period === 'all' || this.period === '1y') ? 'numeric' : undefined 
                });
            });
            const values = data.map(item => item.value);

            // Find index of today's date or the closest upcoming date
            const todayRaw = new Date();
            const todayYMD = todayRaw.getFullYear() + '-' + String(todayRaw.getMonth() + 1).padStart(2, '0') + '-' + String(todayRaw.getDate()).padStart(2, '0');
            
            let todayIndex = data.findIndex(item => item.date >= todayYMD);
            if (todayIndex === -1) {
                todayIndex = data.length > 0 ? data.length - 1 : 0;
            }

            this.currentValue = values[todayIndex !== -1 ? todayIndex : values.length - 1] || values[values.length - 1] || 0;
            const startValue = values[0] || 0;
            this.diff = this.currentValue - startValue;

            const mainColor = this.currentValue >= 0 ? '#10b981' : '#ef4444'; // Emerald or Red

            const option = {
                grid: {
                    top: 20,
                    right: 20,
                    bottom: 20,
                    left: 20,
                    containLabel: true
                },
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: '#ffffff',
                    padding: [12, 16],
                    textStyle: {
                        color: '#1f2937',
                        fontSize: 14,
                        fontFamily: 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
                    },
                    extraCssText: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-radius: 0.5rem; border: 1px solid #f3f4f6;',
                    formatter: (params) => {
                        const item = params[0];
                        const date = item.name;
                        const dataIndex = item.dataIndex;
                        const val = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(item.value);
                        
                        const inc = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(data[dataIndex].income);
                        const exp = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(data[dataIndex].expense);

                        return `<div class="font-medium text-gray-500 text-xs mb-2 uppercase">${date}</div>
                                <div class="font-bold text-gray-900 text-sm mb-2">${val}</div>
                                <div class="flex justify-between items-center gap-4 text-xs">
                                    <span class="text-emerald-600 flex items-center gap-1">↑ Receita</span>
                                    <span class="font-medium text-emerald-600">${inc}</span>
                                </div>
                                <div class="flex justify-between items-center gap-4 text-xs mt-1">
                                    <span class="text-red-600 flex items-center gap-1">↓ Despesa</span>
                                    <span class="font-medium text-red-600">${exp}</span>
                                </div>`;
                    },
                    axisPointer: {
                        lineStyle: {
                            color: '#d1d5db',
                            type: 'dashed'
                        }
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: dates,
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: {
                        color: '#9ca3af',
                        fontSize: 11,
                        margin: 16,
                        showMaxLabel: true,
                        showMinLabel: true,
                        align: 'center'
                    },
                    splitLine: { show: false }
                },
                yAxis: {
                    type: 'value',
                    show: true,
                    min: 'dataMin',
                    scale: true,
                    axisLabel: {
                        color: '#9ca3af',
                        fontSize: 11,
                        formatter: (value) => new Intl.NumberFormat('pt-BR', { notation: 'compact', compactDisplay: 'short' }).format(value)
                    },
                    splitLine: { 
                        show: true,
                        lineStyle: {
                            color: '#f3f4f6',
                            type: 'dashed'
                        }
                    }
                },
                series: [
                    {
                        data: values,
                        type: 'line',
                        smooth: false,
                        showSymbol: false,
                        symbolSize: 8,
                        itemStyle: {
                            color: mainColor,
                            borderWidth: 2
                        },
                        lineStyle: {
                            color: mainColor,
                            width: 2
                        },
                        areaStyle: {
                            color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: mainColor + '33' }, // 20% opacity
                                { offset: 1, color: mainColor + '00' }  // 0% opacity
                            ])
                        },
                        markLine: {
                            data: [
                                { yAxis: 0 },
                                { 
                                    xAxis: todayIndex, 
                                    label: { 
                                        show: true, 
                                        formatter: 'Hoje', 
                                        position: 'insideStartTop', 
                                        color: '#9ca3af',
                                        fontSize: 10
                                    }, 
                                    lineStyle: { 
                                        color: '#9ca3af', 
                                        type: 'dashed',
                                        width: 1
                                    } 
                                }
                            ],
                            symbol: 'none',
                            lineStyle: { color: '#d1d5db', type: 'solid', width: 1 },
                            label: { show: false }
                        }
                    }
                ]
            };

            this.chartInstance.setOption(option);
        }
    }));
});
</script>
