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
            <x-card href="{{ route('financial.cards.show', $card) }}" size="sm" class="flex flex-col gap-3 group">
                <div class="flex items-center gap-4">
                    <x-ui.avatar icon="heroicon-o-credit-card" size="lg" />
                    
                    <div class="overflow-hidden flex-1 flex flex-col justify-center">
                        <div class="flex justify-between items-center gap-2">
                            <h3 class="font-semibold text-neutral-900 truncate">{{ $card->name }}</h3>
                        </div>
                        <p class="text-sm text-neutral-500 truncate">{{ $card->financialAccount->name }}</p>
                    </div>
                    <div class="text-neutral-300 group-hover:text-primary-600 transition-colors">
                        <x-heroicon-o-chevron-right class="size-5" />
                    </div>
                </div>
                
                @if($card->credit_limit > 0)
                    @php
                        $usedLimit = $card->usedLimit();
                        $percentage = min(100, max(0, ($usedLimit / $card->credit_limit) * 100));
                        $colorClass = $percentage > 90 ? 'bg-red-500' : ($percentage > 75 ? 'bg-amber-500' : 'bg-primary-500');
                    @endphp
                    <div class="flex flex-col gap-1 mt-2">
                        <div class="flex justify-between text-xs font-medium text-neutral-600">
                            <span>Usado: {{ formatCurrency($usedLimit) }}</span>
                            <span>Limite: {{ formatCurrency($card->credit_limit) }}</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2 overflow-hidden">
                            <div class="{{ $colorClass }} h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endif
            </x-card>
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
