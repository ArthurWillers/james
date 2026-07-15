<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
            <x-breadcrumbs.item>#{{ $transaction->id }}</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Detalhes da Transação">
        <x-back-button fallback="{{ route('financial.transactions.index') }}" />

        @if(!$isSettlementTransaction)
            <x-delete-modal 
                action="{{ route('financial.transactions.destroy', $transaction->id) }}"
                item-name="a transação"
                item-desc="{{ $transaction->description }}"
                title="Excluir Transação"
            />
        @endif

        <x-button color="outline" href="{{ $editRoute }}" class="bg-white flex-1 sm:flex-initial">
            <x-heroicon-o-pencil-square class="size-4" />
            <span class="whitespace-nowrap">Editar</span>
        </x-button>
    </x-page-header>

    <x-card class="mb-6">
        <div class="flex items-start sm:items-center gap-4 sm:gap-6">
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
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 {{ $iconBg }}">
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
                        {{ formatShort($transaction->date) }}
                    </x-badge>
                    
                    @if($transaction->is_posted)
                        <x-badge color="accent" size="sm">Efetivada</x-badge>
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
        <x-card>
            <h3 class="text-sm font-bold text-neutral-400 uppercase tracking-widest mb-4">Conta / Origem</h3>
            @if($transaction->invoice)
                <div class="flex items-center gap-3">
                    <x-avatar icon="heroicon-o-credit-card" variant="soft" radius="lg" size="md" />
                    <div>
                        <p class="font-bold text-neutral-900">{{ $transaction->invoice->creditCard->name }}</p>
                        <p class="text-xs text-neutral-500">Fatura de {{ formatMonthYear($transaction->invoice->closing_date) }}</p>
                    </div>
                </div>
            @elseif($transaction->account)
                <div class="flex items-center gap-3">
                    <x-avatar icon="heroicon-o-building-library" variant="soft" radius="lg" size="md" />
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
        <x-card>
            <h3 class="text-sm font-bold text-neutral-400 uppercase tracking-widest mb-4">Tags</h3>
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
        <x-metadata-card :model="$transaction" />
    </div>

    @if($transaction->getMedia('attachments')->isNotEmpty())
        <x-card class="mb-6">
            <h3 class="text-sm font-bold text-neutral-800 mb-4">Anexos</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($transaction->getMedia('attachments') as $media)
                    <a href="{{ route('financial.transactions.attachment', [$transaction, $media]) }}" target="_blank" class="flex items-center gap-3 p-3 bg-neutral-50 rounded-lg border border-neutral-200 hover:border-accent hover:bg-accent/5 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-white border border-neutral-200 flex items-center justify-center shrink-0 group-hover:border-accent/30 text-neutral-400 group-hover:text-accent transition-colors">
                            <x-heroicon-o-document class="w-6 h-6" />
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-sm font-medium text-neutral-900 truncate group-hover:text-accent transition-colors">{{ $media->file_name }}</span>
                            <span class="text-xs text-neutral-500">{{ $media->human_readable_size }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-card>
    @endif

    @if(isset($settlementGroup) && $settlementGroup)
        <x-related-resource 
            class="mb-6"
            icon="heroicon-o-user-group"
            title="Gerada por Divisão de Conta"
            description="Esta transação foi gerada a partir da divisão de conta '{{ $settlementGroup->description }}'."
            :url="route('settlements.groups.show', $settlementGroup)"
            buttonText="Ver Divisão"
        />
    @endif

    @if($transaction->transfer_pair_id && $transaction->transferPair)
        <x-related-resource 
            class="mb-6"
            icon="heroicon-o-arrows-right-left"
            title="Transferência Vinculada"
            :description="$transaction->type === 'expense' ? 'Esta transação é a saída. O destino é a conta ' . $transaction->transferPair->account->name . '.' : 'Esta transação é a entrada. A origem é a conta ' . $transaction->transferPair->account->name . '.'"
            :url="route('financial.transactions.show', $transaction->transferPair->id)"
            buttonText="Acessar Contrapartida"
        />
    @endif

    @if($transaction->settlements->isNotEmpty())
        @php
            $settlement = $transaction->settlements->first();
        @endphp
        <x-related-resource 
            class="mb-6"
            icon="heroicon-o-user"
            title="Acerto Vinculado"
            :description="'Esta transação foi gerada a partir de um acerto com ' . $settlement->contact->name . '.'"
            :url="route('settlements.contact.show', $settlement->contact_id)"
            buttonText="Acessar Acertos"
        />
    @endif

    @if($transaction->items->isNotEmpty())
        <div class="flex justify-between items-center mb-4 mt-8">
            <h3 class="text-lg font-bold text-neutral-900">Itens da Transação</h3>
        </div>

        <x-card class="overflow-hidden mb-6">
            <x-table>
                <x-table.header class="hidden sm:grid sm:grid-cols-[2fr_1.5fr_1fr_1fr_1fr]">
                    <x-table.column>Descrição</x-table.column>
                    <x-table.column>Tags</x-table.column>
                    <x-table.column align="right">Qtd</x-table.column>
                    <x-table.column align="right">Unitário</x-table.column>
                    <x-table.column align="right">Total</x-table.column>
                </x-table.header>
                <x-table.body>
                    @foreach($transaction->items as $item)
                        <!-- Mobile View -->
                        <div class="sm:hidden p-4 border-b border-neutral-100 last:border-0 hover:bg-neutral-50 transition-colors flex flex-col gap-3">
                            <div class="flex justify-between items-start gap-3">
                                <span class="font-medium text-neutral-900 break-words line-clamp-2">{{ $item->description }}</span>
                                <span class="font-bold text-neutral-900 shrink-0">{{ formatCurrency($item->quantity * $item->unit_price) }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-neutral-500">{{ (float) $item->quantity }}x {{ formatCurrency($item->unit_price) }}</span>
                                @if($item->tags->isNotEmpty())
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        @php
                                            $itemPrimary = $item->tags->firstWhere('pivot.is_primary', true);
                                            $itemOthers = $item->tags->reject(fn($t) => $t->id === optional($itemPrimary)->id);
                                        @endphp
                                        
                                        @if($itemPrimary)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xxs font-semibold border relative shadow-sm"
                                                  style="background-color: {{ $itemPrimary->color_hex }}15; color: {{ $itemPrimary->color_hex }}; border-color: {{ $itemPrimary->color_hex }}40;">
                                                <span class="text-yellow-500 absolute -top-1 -right-1 bg-white rounded-full border border-yellow-200" style="padding: 1px;">
                                                    <x-heroicon-s-star class="size-2" />
                                                </span>
                                                <x-dynamic-component :component="$itemPrimary->icon" class="size-3" />
                                                <span class="max-w-[80px] truncate">{{ $itemPrimary->name }}</span>
                                            </span>
                                        @endif
                                        
                                        @foreach($itemOthers as $tag)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xxs font-medium border"
                                                  style="background-color: {{ $tag->color_hex }}15; color: {{ $tag->color_hex }}; border-color: {{ $tag->color_hex }}40;">
                                                <x-dynamic-component :component="$tag->icon" class="size-3" />
                                                <span class="max-w-[80px] truncate">{{ $tag->name }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Desktop View -->
                        <x-table.row class="hidden sm:grid sm:grid-cols-[2fr_1.5fr_1fr_1fr_1fr]">
                            <x-table.cell>
                                <span class="font-medium text-neutral-900">{{ $item->description }}</span>
                            </x-table.cell>
                            <x-table.cell>
                                @if($item->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @php
                                            $itemPrimary = $item->tags->firstWhere('pivot.is_primary', true);
                                            $itemOthers = $item->tags->reject(fn($t) => $t->id === optional($itemPrimary)->id);
                                        @endphp
                                        
                                        @if($itemPrimary)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xxs font-semibold border relative shadow-sm"
                                                  style="background-color: {{ $itemPrimary->color_hex }}15; color: {{ $itemPrimary->color_hex }}; border-color: {{ $itemPrimary->color_hex }}40;">
                                                <span class="text-yellow-500 absolute -top-1 -right-1 bg-white rounded-full border border-yellow-200" style="padding: 1px;">
                                                    <x-heroicon-s-star class="size-2" />
                                                </span>
                                                <x-dynamic-component :component="$itemPrimary->icon" class="size-3" />
                                                {{ $itemPrimary->name }}
                                            </span>
                                        @endif
                                        
                                        @foreach($itemOthers as $tag)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xxs font-medium border"
                                                  style="background-color: {{ $tag->color_hex }}15; color: {{ $tag->color_hex }}; border-color: {{ $tag->color_hex }}40;">
                                                <x-dynamic-component :component="$tag->icon" class="size-3" />
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </x-table.cell>
                            <x-table.cell align="right">
                                <span class="text-neutral-700">{{ (float) $item->quantity }}</span>
                            </x-table.cell>
                            <x-table.cell align="right">
                                <span class="text-neutral-700 tabular-nums">{{ formatCurrency($item->unit_price) }}</span>
                            </x-table.cell>
                            <x-table.cell align="right">
                                <span class="font-bold text-neutral-900 tabular-nums">{{ formatCurrency($item->quantity * $item->unit_price) }}</span>
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-table.body>
            </x-table>
        </x-card>
    @endif
</x-layouts.financial>
