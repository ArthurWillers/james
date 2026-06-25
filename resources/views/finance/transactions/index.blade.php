<x-layouts.financial>
    <x-page-header title="Transações" :action="route('financial.transactions.create')" actionText="Nova Transação" icon="heroicon-o-plus">
        <x-button color="outline" href="{{ route('financial.transactions.transfer.create') }}" class="bg-white">
            <x-heroicon-o-arrows-right-left class="size-5!" />
            Transferência
        </x-button>
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

    <x-ui.table class="lg:mb-8">
        @if($transactions->isNotEmpty())
            <x-ui.table.header class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr]">
                <x-ui.table.column>Data</x-ui.table.column>
                <x-ui.table.column>Descrição</x-ui.table.column>
                <x-ui.table.column>Conta/Fatura</x-ui.table.column>
                <x-ui.table.column>Tags</x-ui.table.column>
                <x-ui.table.column align="right">Valor</x-ui.table.column>
            </x-ui.table.header>
        @endif

        <x-ui.table.body>
            @forelse($transactions as $transaction)
                <x-ui.table.row href="{{ route('financial.transactions.show', $transaction) }}" class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr] group transition-all">
                    <x-ui.table.cell>
                        <span class="font-medium text-neutral-900">{{ $transaction->date->format('d/m/Y') }}</span>
                    </x-ui.table.cell>

                    <x-ui.table.cell>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-neutral-900 truncate">{{ $transaction->description }}</span>
                            @if(!$transaction->is_posted)
                                <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Pendente</span>
                            @endif
                        </div>
                    </x-ui.table.cell>

                    <x-ui.table.cell>
                        @if($transaction->invoice)
                            <div class="flex items-center gap-1.5 text-neutral-600 truncate">
                                <x-heroicon-o-credit-card class="size-4 shrink-0" />
                                <span class="truncate">{{ $transaction->invoice->creditCard->name }}</span>
                            </div>
                        @elseif($transaction->account)
                            <div class="flex items-center gap-1.5 text-neutral-600 truncate">
                                <x-heroicon-o-building-library class="size-4 shrink-0" />
                                <span class="truncate">{{ $transaction->account->name }}</span>
                            </div>
                        @else
                            <span class="text-neutral-400">-</span>
                        @endif
                    </x-ui.table.cell>

                    <x-ui.table.cell>
                        @php
                            $tags = $transaction->tags;
                            $primary = $tags->firstWhere('pivot.is_primary', true);
                            $others = $tags->reject(fn($t) => $t->id === optional($primary)->id);
                            
                            $visibleTags = collect(array_filter([$primary, $others->first()]))->take(2);
                            $remainingCount = $tags->count() - $visibleTags->count();
                        @endphp
                        
                        <div class="flex items-center gap-1.5">
                            @foreach($visibleTags as $tag)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                                      style="background-color: {{ $tag->color_hex }}15; color: {{ $tag->color_hex }}; border-color: {{ $tag->color_hex }}40;"
                                      title="{{ $tag->name }}">
                                    @if(isset($primary) && $tag->id === $primary->id)
                                        <span class="text-yellow-500 shrink-0 relative -ml-0.5">
                                            <x-heroicon-s-star class="size-2.5" />
                                        </span>
                                    @endif
                                    <x-dynamic-component :component="$tag->icon" class="size-3" />
                                    <span class="truncate max-w-[80px]">{{ $tag->name }}</span>
                                </span>
                            @endforeach

                            @if($remainingCount > 0)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-500 ring-1 ring-inset ring-neutral-200 cursor-help"
                                      title="{{ $others->skip($visibleTags->count() - ($primary ? 1 : 0))->pluck('name')->join(', ') }}">
                                    +{{ $remainingCount }}
                                </span>
                            @endif
                        </div>
                    </x-ui.table.cell>

                    <x-ui.table.cell align="right">
                        <div class="flex justify-end gap-2 w-full">
                            <span class="font-bold tracking-tight text-base {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-green-600' }}">
                                {{ $transaction->type === 'expense' ? '-' : '+' }} {{ formatCurrency($transaction->amount) }}
                            </span>
                        </div>
                    </x-ui.table.cell>

                    <x-slot name="mobile">
                        <div class="flex items-start justify-between gap-3 w-full">
                            <div class="flex-1 min-w-0 flex flex-col gap-1.5">
                                <h3 class="text-base font-semibold text-neutral-900 leading-tight flex items-center gap-2 truncate">
                                    {{ $transaction->description }}
                                    @if(!$transaction->is_posted)
                                        <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Pendente</span>
                                    @endif
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-neutral-500">
                                    <span>{{ $transaction->date->format('d/m/Y') }}</span>
                                    <span>&bull;</span>
                                    @if($transaction->invoice)
                                        <span class="truncate flex items-center gap-1"><x-heroicon-o-credit-card class="size-3" /> {{ $transaction->invoice->creditCard->name }}</span>
                                    @elseif($transaction->account)
                                        <span class="truncate flex items-center gap-1"><x-heroicon-o-building-library class="size-3" /> {{ $transaction->account->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="font-bold tracking-tight text-base {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $transaction->type === 'expense' ? '-' : '+' }} {{ formatCurrency($transaction->amount) }}
                                </div>
                            </div>
                        </div>
                    </x-slot>
                </x-ui.table.row>
            @empty
                <x-empty-state 
                    icon="heroicon-o-banknotes" 
                    title="Nenhuma transação encontrada" 
                    description="Não há transações com os filtros atuais."
                >
                    <x-slot name="actions">
                        <x-button href="{{ route('financial.transactions.create') }}">
                            Nova Transação
                        </x-button>
                    </x-slot>
                </x-empty-state>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div class="mt-6 pb-6">
        {{ $transactions->links() }}
    </div>
</x-layouts.financial>
