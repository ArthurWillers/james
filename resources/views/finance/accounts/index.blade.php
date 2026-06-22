<x-layouts.financial>
    <x-page-header title="Contas Financeiras" :action="route('financial.accounts.create')" actionText="Nova Conta" icon="plus">
        @if ($hasTrashed)
            <x-button color="outline" href="{{ route('financial.accounts.trashed') }}" class="bg-white">
                <x-icons.heroicons.outline.trash class="size-5!" />
                Lixeira
            </x-button>
        @endif
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.accounts.index') }}" 
        searchPlaceholder="Buscar por nome da conta..." 
        :filters="['search']">
    </x-filter-bar>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($accounts as $account)
            <x-card href="{{ route('financial.accounts.show', $account) }}" size="sm" class="flex flex-col gap-2 group">
                <div class="flex justify-between items-center gap-3">
                    <h3 class="font-semibold text-neutral-900 truncate">{{ $account->name }}</h3>
                    <div class="shrink-0">
                        <x-badge color="accent" size="sm">
                            {{ $account->type->label() }}
                        </x-badge>
                    </div>
                </div>
                
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 {{ $account->balance < 0 ? 'text-red-600' : ($account->balance > 0 ? 'text-green-600' : 'text-neutral-700') }}">
                        @if($account->balance < 0)
                            <x-icons.heroicons.outline.arrow-trending-down class="size-4" />
                        @elseif($account->balance > 0)
                            <x-icons.heroicons.outline.arrow-trending-up class="size-4" />
                        @endif
                        <span class="font-bold text-lg leading-none tracking-tight">
                            {{ formatCurrency($account->balance) }}
                        </span>
                    </div>
                    <div class="text-neutral-300 group-hover:text-primary-600 transition-colors">
                        <x-icons.heroicons.outline.chevron-right class="size-5" />
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-neutral-200">
                <x-empty-state 
                    icon="wallet" 
                    message="Nenhuma conta encontrada." 
                    actionText="Nova Conta" 
                    :actionRoute="route('financial.accounts.create')" 
                />
            </div>
        @endforelse
    </div>

    <div class="mt-6 pb-6">
        {{ $accounts->links() }}
    </div>
</x-layouts.financial>
