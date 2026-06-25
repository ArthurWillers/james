<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
            <x-breadcrumbs.item>#{{ $transaction->id }}</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Detalhes da Transação">
        <x-button color="outline" href="{{ route('financial.transactions.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Voltar
        </x-button>

        <x-button color="outline" href="{{ route('financial.transactions.edit', $transaction->id) }}" class="bg-white">
            <x-heroicon-o-pencil-square class="size-4" />
            Editar
        </x-button>

        <x-modal.trigger name="delete-transaction-{{ $transaction->id }}">
            <x-button type="button" color="danger-outline">
                <x-heroicon-o-trash class="size-4" />
                Excluir
            </x-button>
        </x-modal.trigger>

        <x-modal 
            name="delete-transaction-{{ $transaction->id }}"
            title="Excluir Transação" 
            message="Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita." 
            confirmVariant="danger">
            <form action="{{ route('financial.transactions.destroy', $transaction->id) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Excluir
                </x-button>
            </form>
        </x-modal>
    </x-page-header>

    <x-card class="mb-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            @php
                $icon = match($transaction->type) {
                    'income' => 'heroicon-o-arrow-trending-up',
                    'expense' => 'heroicon-o-arrow-trending-down',
                    'transfer' => 'heroicon-o-arrows-right-left',
                    default => 'heroicon-o-currency-dollar'
                };
                $iconBg = match($transaction->type) {
                    'income' => 'bg-green-100 text-green-600',
                    'expense' => 'bg-red-100 text-red-600',
                    'transfer' => 'bg-blue-100 text-blue-600',
                    default => 'bg-neutral-100 text-neutral-600'
                };
            @endphp
            <div class="w-16 h-16 rounded-full flex items-center justify-center shrink-0 {{ $iconBg }}">
                <x-dynamic-component :component="$icon" class="size-8" />
            </div>
            
            <div class="flex flex-col gap-3 w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h2 class="text-2xl font-bold text-neutral-900">{{ $transaction->description }}</h2>
                    <div class="text-3xl font-bold {{ $transaction->type === 'income' ? 'text-green-600' : ($transaction->type === 'expense' ? 'text-red-600' : 'text-neutral-900') }}">
                        {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}{{ formatCurrency(abs($transaction->amount)) }}
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <x-badge color="neutral" size="sm">
                        <x-heroicon-o-calendar class="size-3 mr-1 inline" />
                        {{ $transaction->date->format('d/m/Y') }}
                    </x-badge>
                    
                    @if($transaction->is_posted)
                        <x-badge color="success" size="sm">Efetivada</x-badge>
                    @else
                        <x-badge color="warning" size="sm">Pendente</x-badge>
                    @endif

                    @if($transaction->type === 'transfer')
                        <x-badge color="info" size="sm">Transferência</x-badge>
                    @endif
                </div>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <!-- Conta/Fatura -->
        <x-card class="p-6">
            <h3 class="text-sm font-bold text-neutral-400 uppercase tracking-widest mb-4">Conta / Origem</h3>
            @if($transaction->invoice)
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-neutral-100 rounded-lg text-neutral-600">
                        <x-heroicon-o-credit-card class="size-5" />
                    </div>
                    <div>
                        <p class="font-bold text-neutral-900">{{ $transaction->invoice->creditCard->name }}</p>
                        <p class="text-xs text-neutral-500">Fatura de {{ $transaction->invoice->closing_date->format('M/Y') }}</p>
                    </div>
                </div>
            @elseif($transaction->account)
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-neutral-100 rounded-lg text-neutral-600">
                        <x-heroicon-o-building-library class="size-5" />
                    </div>
                    <div>
                        <p class="font-bold text-neutral-900">{{ $transaction->account->name }}</p>
                        <p class="text-xs text-neutral-500">Conta Corrente</p>
                    </div>
                </div>
            @else
                <span class="text-neutral-400 text-sm">-</span>
            @endif
        </x-card>

        <!-- Tags -->
        <x-card class="p-6">
            <h3 class="text-sm font-bold text-neutral-400 uppercase tracking-widest mb-4">Tags Globais</h3>
            @if($transaction->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @php
                        $primary = $transaction->tags->firstWhere('pivot.is_primary', true);
                        $others = $transaction->tags->reject(fn($t) => $t->id === optional($primary)->id);
                    @endphp
                    
                    @if($primary)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border relative shadow-sm"
                              style="background-color: {{ $primary->color_hex }}15; color: {{ $primary->color_hex }}; border-color: {{ $primary->color_hex }}40;">
                            <span class="text-yellow-500 absolute -top-1.5 -right-1.5 bg-white rounded-full border border-yellow-200 p-0.5 shadow-sm">
                                <x-heroicon-s-star class="size-2.5" />
                            </span>
                            <x-dynamic-component :component="$primary->icon" class="size-3.5" />
                            {{ $primary->name }}
                        </span>
                    @endif
                    
                    @foreach($others as $tag)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border"
                              style="background-color: {{ $tag->color_hex }}15; color: {{ $tag->color_hex }}; border-color: {{ $tag->color_hex }}40;">
                            <x-dynamic-component :component="$tag->icon" class="size-3.5" />
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-neutral-500 italic">Nenhuma tag vinculada.</p>
            @endif
        </x-card>

        <!-- Info -->
        <x-card class="p-6">
            <h3 class="text-sm font-bold text-neutral-400 uppercase tracking-widest mb-4">Metadados</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-neutral-500">Criado em:</span>
                    <span class="font-medium text-neutral-900">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-neutral-500">Atualizado:</span>
                    <span class="font-medium text-neutral-900">{{ $transaction->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-neutral-500">ID Interno:</span>
                    <span class="font-mono text-neutral-500 bg-neutral-100 px-1.5 py-0.5 rounded text-xs">#{{ $transaction->id }}</span>
                </div>
            </div>
        </x-card>
    </div>

    @if($transaction->type === 'transfer' && $transaction->transferPair)
        <x-card class="mb-6 p-6 border-blue-200 bg-blue-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-xl text-blue-600 shrink-0">
                        <x-heroicon-o-arrows-right-left class="size-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-900">Transferência Vinculada</h3>
                        <p class="text-sm text-blue-700 mt-0.5">
                            @if($transaction->amount < 0)
                                Esta transação é a saída. O destino é a conta <strong>{{ $transaction->transferPair->account->name }}</strong>.
                            @else
                                Esta transação é a entrada. A origem é a conta <strong>{{ $transaction->transferPair->account->name }}</strong>.
                            @endif
                        </p>
                    </div>
                </div>
                <x-button href="{{ route('financial.transactions.show', $transaction->transferPair->id) }}" color="outline" class="bg-white border-blue-200 text-blue-700 hover:bg-blue-100 shrink-0">
                    Acessar Contrapartida
                </x-button>
            </div>
        </x-card>
    @endif

    @if($transaction->items->isNotEmpty())
        <div class="flex justify-between items-center mb-4 mt-8">
            <h3 class="text-lg font-bold text-neutral-900">Itens da Transação</h3>
        </div>

        <x-card class="overflow-hidden mb-6">
            <x-ui.table>
                <x-ui.table.header class="hidden sm:grid sm:grid-cols-[2fr_1.5fr_1fr_1fr_1fr]">
                    <x-ui.table.column>Descrição</x-ui.table.column>
                    <x-ui.table.column>Tags</x-ui.table.column>
                    <x-ui.table.column align="right">Qtd</x-ui.table.column>
                    <x-ui.table.column align="right">Unitário</x-ui.table.column>
                    <x-ui.table.column align="right">Total</x-ui.table.column>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($transaction->items as $item)
                        <x-ui.table.row class="hidden sm:grid sm:grid-cols-[2fr_1.5fr_1fr_1fr_1fr]">
                            <x-ui.table.cell>
                                <span class="font-medium text-neutral-900">{{ $item->description }}</span>
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                @if($item->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @php
                                            $itemPrimary = $item->tags->firstWhere('pivot.is_primary', true);
                                            $itemOthers = $item->tags->reject(fn($t) => $t->id === optional($itemPrimary)->id);
                                        @endphp
                                        
                                        @if($itemPrimary)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold border relative shadow-sm"
                                                  style="background-color: {{ $itemPrimary->color_hex }}15; color: {{ $itemPrimary->color_hex }}; border-color: {{ $itemPrimary->color_hex }}40;">
                                                <span class="text-yellow-500 absolute -top-1 -right-1 bg-white rounded-full border border-yellow-200" style="padding: 1px;">
                                                    <x-heroicon-s-star class="size-2" />
                                                </span>
                                                <x-dynamic-component :component="$itemPrimary->icon" class="size-3" />
                                                {{ $itemPrimary->name }}
                                            </span>
                                        @endif
                                        
                                        @foreach($itemOthers as $tag)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium border"
                                                  style="background-color: {{ $tag->color_hex }}15; color: {{ $tag->color_hex }}; border-color: {{ $tag->color_hex }}40;">
                                                <x-dynamic-component :component="$tag->icon" class="size-3" />
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </x-ui.table.cell>
                            <x-ui.table.cell align="right">
                                <span class="text-neutral-700">{{ $item->quantity }}</span>
                            </x-ui.table.cell>
                            <x-ui.table.cell align="right">
                                <span class="text-neutral-700">{{ formatCurrency($item->unit_price) }}</span>
                            </x-ui.table.cell>
                            <x-ui.table.cell align="right">
                                <span class="font-bold text-neutral-900">{{ formatCurrency($item->quantity * $item->unit_price) }}</span>
                            </x-ui.table.cell>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
            <div class="bg-neutral-50 px-6 py-4 border-t border-neutral-200 flex justify-end">
                <div class="text-right flex items-center gap-4">
                    <span class="text-xs font-bold text-neutral-500 uppercase tracking-widest">Soma dos Itens</span>
                    <span class="text-xl font-bold tracking-tight text-neutral-900">{{ formatCurrency($transaction->items->sum(fn($i) => $i->quantity * $i->unit_price)) }}</span>
                </div>
            </div>
        </x-card>
    @endif
</x-layouts.financial>
