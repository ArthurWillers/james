<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>{{ $contact->name }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        <div class="flex items-center gap-2">
            <x-button color="outline" class="bg-white" onclick="confirm('Tem certeza que deseja arquivar este contato?') || event.preventDefault()">
                <x-heroicon-o-archive-box class="size-4" />
                <span class="hidden sm:inline">Arquivar</span>
            </x-button>
            <x-button href="{{ route('settlements.create', $contact) }}">
                <x-heroicon-o-plus class="size-4" />
                <span>Novo Lançamento</span>
            </x-button>
        </div>
    </div>

    <!-- Contact Header -->
    <div class="bg-white border border-neutral-200 rounded-xl p-6 mb-6 flex items-center gap-4 shadow-sm">
        <x-ui.avatar :model="$contact" size="xl" />
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $contact->name }}</h1>
            <div class="flex items-center gap-2 text-neutral-500 text-sm mt-1">
                <span>{{ $contact->relationship_category ?? 'Contato' }}</span>
                @if($contact->phones && count($contact->phones) > 0)
                    <x-heroicon-m-minus class="size-3 text-neutral-300" />
                    <span class="flex items-center gap-1">
                        <x-heroicon-o-phone class="size-3.5" />
                        {{ collect($contact->phones)->first()['value'] ?? '' }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Balances -->
    <div class="mb-8 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
        <x-finance.kpi-card 
            title="A Receber" 
            :value="formatCurrency($toReceive)" 
            icon="heroicon-o-arrow-trending-up" 
            color="green" 
            :hide-icon-on-mobile="true"
        />
        
        <x-finance.kpi-card 
            title="A Pagar" 
            :value="formatCurrency($toPay)" 
            icon="heroicon-o-arrow-trending-down" 
            color="red" 
            :hide-icon-on-mobile="true"
        />
        
        <x-finance.kpi-card 
            title="Líquido" 
            :value="formatCurrency($netBalance)" 
            icon="heroicon-o-scale" 
            :color="$netBalance == 0 ? 'neutral' : ($netBalance > 0 ? 'green' : 'red')" 
            :hide-icon-on-mobile="true"
        />
    </div>

    <!-- Ledger Table -->
    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Histórico de Transações</h2>
    
    <div class="mb-12">
        <x-ui.table>
            <x-ui.table.header class="hidden sm:grid grid-cols-5">
                <x-ui.table.column>Data</x-ui.table.column>
                <x-ui.table.column>Descrição</x-ui.table.column>
                <x-ui.table.column>Tipo</x-ui.table.column>
                <x-ui.table.column>Pagamento</x-ui.table.column>
                <x-ui.table.column class="text-right">Valor</x-ui.table.column>
            </x-ui.table.header>

            <x-ui.table.body>
                @forelse($settlements as $settlement)
                    @php
                        $isPositiveForMe = in_array($settlement->type->value, [\App\Enums\SettlementType::TheyOwe->value, \App\Enums\SettlementType::IPaid->value]);
                        $amountColor = $isPositiveForMe ? 'text-emerald-600' : 'text-red-600';
                        $amountPrefix = $isPositiveForMe ? '+' : '-';
                    @endphp

                    <x-ui.table.row href="{{ $settlement->settlement_group_id ? route('settlements.groups.show', $settlement->settlement_group_id) : route('settlements.show_item', $settlement) }}" class="hidden sm:grid grid-cols-5">
                        <x-ui.table.cell class="text-neutral-500">
                            {{ formatShort($settlement->date) }}
                        </x-ui.table.cell>
                        
                        <x-ui.table.cell>
                            <span class="text-neutral-700 font-medium truncate">{{ $settlement->description }}</span>
                        </x-ui.table.cell>

                        <x-ui.table.cell>
                            <x-ui.badge :color="$settlement->type->color()" class="flex items-center gap-1 w-fit">
                                <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5" />
                                <span>{{ $settlement->type->label() }}</span>
                            </x-ui.badge>
                        </x-ui.table.cell>

                        <x-ui.table.cell class="text-neutral-500 text-sm">
                            @if($settlement->financialTransaction)
                                @if($settlement->financialTransaction->invoice)
                                    <div class="flex items-center gap-1.5 truncate">
                                        <x-heroicon-o-credit-card class="size-4 shrink-0 text-orange-500" />
                                        <span class="truncate">{{ $settlement->financialTransaction->invoice->creditCard->name }}</span>
                                    </div>
                                @elseif($settlement->financialTransaction->account)
                                    <div class="flex items-center gap-1.5 truncate">
                                        <x-heroicon-o-building-library class="size-4 shrink-0 text-blue-500" />
                                        <span class="truncate">{{ $settlement->financialTransaction->account->name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5 truncate">
                                        <x-heroicon-o-currency-dollar class="size-4 shrink-0 text-neutral-400" />
                                        <span class="truncate">Transação</span>
                                    </div>
                                @endif
                            @else
                                <span class="text-neutral-300">-</span>
                            @endif
                        </x-ui.table.cell>

                        <x-ui.table.cell class="text-right font-semibold {{ $amountColor }}">
                            {{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}
                        </x-ui.table.cell>

                        <!-- Mobile View -->
                        <x-slot:mobile>
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex flex-col gap-1 min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge :color="$settlement->type->color()" class="flex items-center shrink-0 w-fit">
                                            <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5" />
                                        </x-ui.badge>
                                        <span class="font-medium text-neutral-900 truncate">{{ $settlement->description }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-neutral-500">
                                        <span>{{ formatShort($settlement->date) }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-semibold {{ $amountColor }}">{{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}</span>
                                    <div class="text-xs text-neutral-500 mt-0.5">{{ $settlement->type->label() }}</div>
                                </div>
                            </div>
                        </x-slot:mobile>
                    </x-ui.table.row>
                @empty
                    <div class="col-span-full">
                        <x-ui.empty-state 
                            icon="heroicon-o-queue-list" 
                            title="Nenhuma transação" 
                            description="Você ainda não registrou nenhum acerto com este contato." 
                        />
                    </div>
                @endforelse
            </x-ui.table.body>
        </x-ui.table>

        <div class="mt-6">
            {{ $settlements->links() }}
        </div>
    </div>
</x-layouts.app>
