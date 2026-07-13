<x-finance.transaction-table :transactions="$transactions" />

@if($transactions->hasPages())
    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
@endif

@if($virtualTransactions->isNotEmpty())
    <div class="px-6 pt-4 pb-2">
        <h4 class="text-xs font-bold text-neutral-500 uppercase tracking-wider flex items-center gap-2">
            <x-heroicon-o-arrow-path class="size-3.5" />
            Projeções (Recorrências)
        </h4>
    </div>
    <x-table>
        <x-table.header class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr]">
            <x-table.column>Data</x-table.column>
            <x-table.column>Descrição</x-table.column>
            <x-table.column>Conta/Fatura</x-table.column>
            <x-table.column>Tags</x-table.column>
            <x-table.column align="right">Valor</x-table.column>
        </x-table.header>

        <x-table.body>
            @foreach($virtualTransactions as $transaction)
                @php
                    $virtualTagsIds = $transaction->relationLoaded('tags') ? $transaction->tags->pluck('id')->toJson() : '[]';
                @endphp
                <div style="display: contents"
                     x-data="{ tags: {{ $virtualTagsIds }} }"
                     x-show="typeof selectedTagId === 'undefined' || selectedTagId === null || tags.includes(selectedTagId) || (selectedTagId === 0 && tags.length === 0)">
                    <x-table.row class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr] opacity-60">
                    <x-table.cell>
                        <span class="font-medium text-neutral-900">{{ formatShort($transaction->date) }}</span>
                    </x-table.cell>

                    <x-table.cell>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-neutral-900 truncate">
                                {{ $transaction->description }}
                            </span>
                            <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Projeção</span>
                        </div>
                    </x-table.cell>

                    <x-table.cell>
                        @if($transaction->relationLoaded('invoice') && $transaction->invoice && $transaction->invoice->relationLoaded('creditCard') && $transaction->invoice->creditCard)
                            <div class="flex items-center gap-1.5 text-neutral-600 truncate">
                                <x-heroicon-o-credit-card class="size-4 shrink-0" />
                                <span class="truncate">{{ $transaction->invoice->creditCard->name }}</span>
                            </div>
                        @elseif($transaction->relationLoaded('account') && $transaction->account)
                            <div class="flex items-center gap-1.5 text-neutral-600 truncate">
                                <x-heroicon-o-building-library class="size-4 shrink-0" />
                                <span class="truncate">{{ $transaction->account->name }}</span>
                            </div>
                        @else
                            <span class="text-neutral-400">-</span>
                        @endif
                    </x-table.cell>

                    <x-table.cell>
                        @php
                            $tags = $transaction->relationLoaded('tags') ? $transaction->tags : collect();
                            $primary = $tags->where('pivot.is_primary', true)->first();
                            $others = $tags->reject(fn($t) => $t->id === optional($primary)->id);
                            $visibleTags = collect(array_filter([$primary, $others->first()]))->take(2);
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
                            @if($tags->count() > 2)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-500 ring-1 ring-inset ring-neutral-200 cursor-help"
                                      title="{{ $others->skip($visibleTags->count() - ($primary ? 1 : 0))->pluck('name')->join(', ') }}">
                                    +{{ $tags->count() - $visibleTags->count() }}
                                </span>
                            @endif
                        </div>
                    </x-table.cell>

                    <x-table.cell align="right">
                        <span class="font-bold tracking-tight text-base {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $transaction->type === 'expense' ? '-' : '+' }} {{ formatCurrency($transaction->amount) }}
                        </span>
                    </x-table.cell>

                    <x-slot name="mobile">
                        <div class="flex items-start justify-between gap-3 w-full">
                            <div class="flex-1 min-w-0 flex flex-col gap-1.5">
                                <h3 class="text-base font-semibold text-neutral-900 leading-tight flex items-center gap-2 truncate">
                                    {{ $transaction->description }}
                                    <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Projeção</span>
                                </h3>
                                <span class="text-sm text-neutral-500">{{ formatShort($transaction->date) }}</span>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="font-bold tracking-tight text-base {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $transaction->type === 'expense' ? '-' : '+' }} {{ formatCurrency($transaction->amount) }}
                                </div>
                            </div>
                        </div>
                    </x-slot>
                </x-table.row>
                </div>
            @endforeach
        </x-table.body>
    </x-table>

    @if($virtualTransactions->hasPages())
        <div class="mt-4">
            {{ $virtualTransactions->links() }}
        </div>
    @endif
@endif
