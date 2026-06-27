<x-layouts.financial>
    <x-page-header title="Transações" :action="route('financial.transactions.create')" actionText="Nova Transação" icon="heroicon-o-plus">
        <x-button color="outline" href="{{ route('financial.transactions.transfer.create') }}" class="bg-white">
            <x-heroicon-o-arrows-right-left class="size-4!" />
            Transferência
        </x-button>
        @if($hasTrashed)
            <x-button color="outline" href="{{ route('financial.transactions.trashed') }}" class="bg-white">
                <x-heroicon-o-trash class="size-4" />
                Lixeira
            </x-button>
        @endif
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.transactions.index') }}" 
        searchPlaceholder="Buscar por descrição..." 
        :filters="['search', 'account_id', 'type', 'date']">
        
        <div class="flex flex-col sm:flex-row w-full sm:w-auto divide-y sm:divide-y-0 sm:divide-x divide-neutral-200">
            <select name="account_id" 
                    class="w-full sm:w-auto bg-transparent border-0 py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                <option value="">Todas as Contas</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>{{ $account->name }}</option>
                @endforeach
            </select>
            
            <select name="type" 
                    class="w-full sm:w-auto bg-transparent border-0 py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                <option value="">Todos os Tipos</option>
                <option value="income" @selected(request('type') == 'income')>Receita</option>
                <option value="expense" @selected(request('type') == 'expense')>Despesa</option>
            </select>

            <input type="date" name="date" value="{{ request('date') }}" 
                   class="w-full sm:w-auto bg-transparent border-0 py-1.5 px-3 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors"
                   title="Filtrar por data específica">
        </div>
    </x-filter-bar>

    <x-finance.transaction-table :transactions="$transactions" class="lg:mb-8" />
    
    <div class="mt-6 pb-6">
        {{ $transactions->links() }}
    </div>
</x-layouts.financial>
