<x-layouts.financial>
    <x-page-header title="Dashboard Financeiro" :action="route('financial.transactions.create')" actionText="Nova Transação" icon="heroicon-o-plus"></x-page-header>

    <!-- 1. Linha de Destaque: Os Grandes Números -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-card class="p-6 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600 group-hover:text-primary-600 transition-colors">Saldo Líquido</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold text-neutral-900 break-words transition-colors">
                        {{ formatCurrency($kpi['netBalance']) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-neutral-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-neutral-200 transition-colors">
                    <x-heroicon-o-building-library class="w-5 h-5 sm:w-6 sm:h-6 text-neutral-600 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </x-card>

        <x-card class="p-6 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600 group-hover:text-primary-600 transition-colors">Total de Receitas</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold text-green-600 break-words group-hover:text-green-700 transition-colors">
                        {{ formatCurrency($kpi['income']) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-green-200 transition-colors">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </x-card>

        <x-card class="p-6 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600 group-hover:text-primary-600 transition-colors">Total de Despesas</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold text-red-600 break-words group-hover:text-red-700 transition-colors">
                        {{ formatCurrency($kpi['expense']) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-red-200 transition-colors">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </x-card>

        <x-card class="p-6 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600">Saldo Atual</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold {{ $kpi['currentBalance'] >= 0 ? 'text-green-600' : 'text-red-600' }} break-words">
                        {{ formatCurrency($kpi['currentBalance']) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $kpi['currentBalance'] >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-scale class="w-5 h-5 sm:w-6 sm:h-6 {{ $kpi['currentBalance'] >= 0 ? 'text-green-600' : 'text-red-600' }}" />
                </div>
            </div>
        </x-card>
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
                    <x-card :href="route('financial.cards.show', $card->id)" class="p-4 flex flex-col justify-between group">
                        <div>
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="p-1.5 bg-neutral-100 rounded text-neutral-600 shrink-0">
                                        <x-heroicon-o-credit-card class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-sm text-neutral-900 truncate" title="{{ $card->name }}">{{ $card->name }}</h3>
                                        <p class="text-[10px] text-neutral-500 truncate">Vence dia {{ \Carbon\Carbon::parse($card->invoice_due_date)->format('d') }} • Fecha dia {{ \Carbon\Carbon::parse($card->invoice_closing_date)->format('d') }}</p>
                                    </div>
                                </div>
                                <div class="p-1 -mr-1 -mt-1 text-neutral-400 group-hover:text-brand-600 transition-colors" title="Ver Cartão">
                                    <x-heroicon-o-arrow-top-right-on-square class="size-4" />
                                </div>
                            </div>

                            @if($card->limit)
                                <div class="flex justify-between items-center text-xs text-neutral-500 mb-3">
                                    <span>Limite Total</span>
                                    <span class="font-semibold text-neutral-900">{{ formatCurrency($card->limit) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between items-end border-t border-neutral-100 pt-3">
                                <div>
                                    <div class="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold mb-1">Fatura Atual</div>
                                    <div class="font-bold text-base leading-none {{ $card->status === 'paid' ? 'text-green-600' : 'text-neutral-900' }}">
                                        {{ formatCurrency($card->invoice_total) }}
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end gap-1">
                                    <x-badge :color="$card->status === 'paid' ? 'success' : 'warning'" class="text-[9px] px-1.5 py-0.5 leading-none">
                                        {{ $card->status === 'paid' ? 'Paga' : 'Aberta' }}
                                    </x-badge>
                                </div>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 mt-8">
        
        <!-- Radar -->
        <div class="lg:col-span-2">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Radar do James (Próximos Dias)</h3>
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