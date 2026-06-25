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
        :filters="['search', 'account_id', 'type', 'is_posted', 'date_from', 'date_to']">
        
        <x-slot:extraFilters>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 w-full sm:w-auto">
                <x-form-select name="account_id" id="account_id" class="!py-1.5 !text-sm">
                    <option value="">Todas as Contas</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->name }}
                        </option>
                    @endforeach
                </x-form-select>
                
                <x-form-select name="type" id="type" class="!py-1.5 !text-sm">
                    <option value="">Todos os Tipos</option>
                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Receita</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Despesa</option>
                </x-form-select>

                <x-form-select name="is_posted" id="is_posted" class="!py-1.5 !text-sm">
                    <option value="">Qualquer Status</option>
                    <option value="1" {{ request('is_posted') === '1' ? 'selected' : '' }}>Efetivada</option>
                    <option value="0" {{ request('is_posted') === '0' ? 'selected' : '' }}>Pendente</option>
                </x-form-select>

                <x-form-input type="date" name="date_from" :value="request('date_from')" placeholder="A partir de" class="!py-1.5 !text-sm" />
                <x-form-input type="date" name="date_to" :value="request('date_to')" placeholder="Até" class="!py-1.5 !text-sm" />
            </div>
        </x-slot:extraFilters>
    </x-filter-bar>

    <x-finance.transaction-table :transactions="$transactions" class="lg:mb-8" />
    
    <div class="mt-6 pb-6">
        {{ $transactions->links() }}
    </div>
</x-layouts.financial>
