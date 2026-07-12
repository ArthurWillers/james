<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.history') }}">Histórico Global</x-breadcrumbs.item>
            <x-breadcrumbs.item>Detalhes do Acerto</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="{{ $settlement->description ?: 'Acerto' }}">
        <div class="flex items-center gap-2">
            <x-button color="outline" href="{{ route('settlements.history') }}" class="bg-white">
                <x-heroicon-o-arrow-left class="size-4" />
                Voltar
            </x-button>
            @if(!$settlement->trashed())
                @if(!$settlement->settlement_group_id)
                    <x-button color="outline" href="{{ route('settlements.edit', $settlement) }}" class="bg-white">
                        <x-heroicon-o-pencil class="size-4" />
                        Editar
                    </x-button>
                    <x-modal.trigger name="delete-settlement-{{ $settlement->id }}">
                        <x-button color="danger-outline">
                            <x-heroicon-o-trash class="size-4" />
                            Excluir
                        </x-button>
                    </x-modal.trigger>
                @endif
            @endif
        </div>
    </x-page-header>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Items -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <x-card class="p-6 border-neutral-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :model="$settlement->contact" size="lg" />
                        <div>
                            <h2 class="text-lg font-bold text-neutral-900">{{ $settlement->contact->name }}</h2>
                            <a href="{{ route('settlements.contact.show', $settlement->contact_id) }}" class="text-sm font-medium text-accent hover:text-accent-dark transition-colors inline-flex items-center gap-1 mt-0.5">
                                Ver Extrato <x-heroicon-m-arrow-right class="size-3" />
                            </a>
                        </div>
                    </div>
                    
                    <div class="sm:text-right">
                        @php
                            $isPositiveForMe = in_array($settlement->type->value, [\App\Enums\SettlementType::TheyOwe->value, \App\Enums\SettlementType::IPaid->value]);
                            $amountColor = $isPositiveForMe ? 'text-emerald-600' : 'text-red-600';
                            $amountPrefix = $isPositiveForMe ? '+' : '-';
                        @endphp
                        <div class="text-2xl font-bold {{ $amountColor }}">
                            {{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}
                        </div>
                        <div class="mt-1">
                            <x-ui.badge :color="$settlement->type->color()" class="shadow-sm inline-flex">
                                <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5 mr-1.5" />
                                {{ $settlement->type->label() }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>
                
                @if($settlement->financialTransaction)
                    <div class="mt-8 pt-6 border-t border-neutral-100">
                        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Meio de Pagamento</h3>
                        <div class="flex items-center gap-4">
                            @if($settlement->financialTransaction->invoice)
                                <x-ui.avatar icon="heroicon-o-credit-card" size="lg" />
                                <div>
                                    <div class="font-medium text-neutral-900">{{ $settlement->financialTransaction->invoice->creditCard->name }}</div>
                                    <div class="text-sm text-neutral-500">Cartão de Crédito • Fatura de {{ $settlement->financialTransaction->invoice->closing_date->format('m/Y') }}</div>
                                </div>
                            @elseif($settlement->financialTransaction->account)
                                <x-ui.avatar icon="heroicon-o-building-library" size="lg" />
                                <div>
                                    <div class="font-medium text-neutral-900">{{ $settlement->financialTransaction->account->name }}</div>
                                    <div class="text-sm text-neutral-500">Conta Corrente</div>
                                </div>
                            @else
                                <x-ui.avatar icon="heroicon-o-currency-dollar" size="lg" />
                                <div>
                                    <div class="font-medium text-neutral-900">Transação Avulsa</div>
                                    <div class="text-sm text-neutral-500">Sem conta ou cartão vinculado</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Right Column: Meta -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            <x-card class="p-6">
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Detalhes</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-neutral-500 mb-1">Data</div>
                        <div class="font-medium text-neutral-900">{{ formatShort($settlement->date) }}</div>
                    </div>
                    
                    @if($settlement->settlement_group_id)
                        <div class="pt-4 border-t border-neutral-100">
                            <div class="text-xs text-neutral-500 mb-2">Parte de Divisão de Conta</div>
                            <a href="{{ route('settlements.groups.show', $settlement->settlement_group_id) }}" class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 hover:border-neutral-300 transition-colors bg-neutral-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white border border-neutral-200 flex items-center justify-center text-neutral-500">
                                        <x-heroicon-o-users class="size-4" />
                                    </div>
                                    <span class="text-sm font-medium text-neutral-700">Ver Divisão</span>
                                </div>
                                <x-heroicon-m-chevron-right class="size-4 text-neutral-400" />
                            </a>
                        </div>
                    @endif

                    @if($settlement->financialTransaction)
                        <div class="pt-4 border-t border-neutral-100">
                            <div class="text-xs text-neutral-500 mb-2">Transação Financeira</div>
                            <a href="{{ route('financial.transactions.show', $settlement->financialTransaction) }}" class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 hover:border-neutral-300 transition-colors bg-neutral-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white border border-neutral-200 flex items-center justify-center text-neutral-500">
                                        <x-heroicon-o-receipt-percent class="size-4" />
                                    </div>
                                    <span class="text-sm font-medium text-neutral-700">Ver Transação</span>
                                </div>
                                <x-heroicon-m-chevron-right class="size-4 text-neutral-400" />
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-ui.metadata-card :model="$settlement" />
        </div>
    </div>

    @if(!$settlement->trashed() && !$settlement->settlement_group_id)
        <x-modal 
            name="delete-settlement-{{ $settlement->id }}"
            title="Excluir Acerto" 
            message="Tem certeza que deseja excluir este acerto? Caso tenha sido gerada uma transação financeira atrelada, ela também será movida para a lixeira." 
            confirmVariant="danger">
            <form action="{{ route('settlements.destroy', $settlement) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Sim, excluir
                </x-button>
            </form>
        </x-modal>
    @endif
</x-layouts.app>
