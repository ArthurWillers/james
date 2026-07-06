<x-layouts.financial>
    <x-page-header title="Cartões de Crédito" :action="route('financial.cards.create')" actionText="Novo Cartão" icon="heroicon-o-plus">
        @if ($hasTrashed)
            <x-button color="outline" href="{{ route('financial.cards.trashed') }}" class="bg-white">
                <x-heroicon-o-trash class="size-5!" />
                Lixeira
            </x-button>
        @endif
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.cards.index') }}" 
        searchPlaceholder="Buscar por nome do cartão..." 
        :filters="['search']">
    </x-filter-bar>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($cards as $card)
            <x-finance.credit-card :card="$card" />
        @empty
            <div class="col-span-full bg-white rounded-xl border border-neutral-200">
                <x-empty-state 
                    icon="heroicon-o-credit-card" 
                    message="Nenhum cartão encontrado." 
                    actionText="Novo Cartão" 
                    :actionRoute="route('financial.cards.create')" 
                />
            </div>
        @endforelse
    </div>

    <div class="mt-6 pb-6">
        {{ $cards->links() }}
    </div>
</x-layouts.financial>
