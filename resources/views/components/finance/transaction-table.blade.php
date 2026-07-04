@props(['transactions', 'hidePendingBadge' => false])

<x-ui.table {{ $attributes }}>
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
            <x-ui.table.row href="{{ $transaction->id ? route('financial.transactions.show', $transaction->id) : '#' }}" class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr] group transition-all">
                <x-ui.table.cell>
                    <span class="font-medium text-neutral-900">{{ $transaction->date->format('d/m/Y') }}</span>
                </x-ui.table.cell>

                <x-ui.table.cell>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-neutral-900 truncate">
                            {{ $transaction->description }}
                            @if($transaction->installment_total > 1)
                                <span class="text-neutral-500 font-normal ml-1">({{ $transaction->installment_current }}/{{ $transaction->installment_total }})</span>
                            @endif
                        </span>
                        @if(!$transaction->is_posted && !$hidePendingBadge && empty($transaction->is_recurrence) && empty($transaction->is_invoice))
                            <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Pendente</span>
                        @endif
                        @if(isset($transaction->is_recurrence) && $transaction->is_recurrence)
                            <span class="text-[10px] uppercase font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-blue-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-arrow-path class="size-3" /> Recorrência</span>
                        @endif
                        @if(isset($transaction->is_invoice) && $transaction->is_invoice)
                            <span class="text-[10px] uppercase font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-purple-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-document-text class="size-3" /> Fatura</span>
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
                            <h3 class="text-base font-semibold text-neutral-900 leading-tight flex flex-wrap items-center gap-2">
                                <span class="truncate max-w-full">
                                    {{ $transaction->description }}
                                    @if($transaction->installment_total > 1)
                                        <span class="text-neutral-500 font-normal text-sm ml-1">({{ $transaction->installment_current }}/{{ $transaction->installment_total }})</span>
                                    @endif
                                </span>
                                @if(!$transaction->is_posted && !$hidePendingBadge && empty($transaction->is_recurrence) && empty($transaction->is_invoice))
                                    <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Pendente</span>
                                @endif
                                @if(isset($transaction->is_recurrence) && $transaction->is_recurrence)
                                    <span class="text-[10px] uppercase font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-blue-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-arrow-path class="size-3" /> Recorrência</span>
                                @endif
                                @if(isset($transaction->is_invoice) && $transaction->is_invoice)
                                    <span class="text-[10px] uppercase font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-purple-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-document-text class="size-3" /> Fatura</span>
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
                description="Não há transações disponíveis no momento."
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
