@if($recentTransactions->isNotEmpty())
    <div class="flex justify-between items-center mb-4 mt-8">
        <h3 class="text-lg font-bold text-neutral-900">Últimas Transações</h3>
        <a href="{{ $viewAllUrl }}" class="t-learn text-sm font-medium text-brand-600 hover:text-brand-700 flex items-center gap-1">
            Ver todas <x-heroicon-m-arrow-right class="t-learn-chevron size-4" />
        </a>
    </div>

    <x-finance.transaction-table :transactions="$recentTransactions" class="lg:mb-8" />
@endif
