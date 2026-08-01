@props(['transactions', 'hidePendingBadge' => false])

<x-table {{ $attributes }}>
    @if($transactions->isNotEmpty())
        <x-table.header class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr]">
            <x-table.column>Data</x-table.column>
            <x-table.column>Descrição</x-table.column>
            <x-table.column>Conta/Fatura</x-table.column>
            <x-table.column>Tags</x-table.column>
            <x-table.column align="right">Valor</x-table.column>
        </x-table.header>
    @endif

    <x-table.body>
        @forelse($transactions as $transaction)
            @php
                $href = '#';
                if (!empty($transaction->is_invoice) && $transaction->invoice) {
                    $href = route('financial.cards.invoices.show', [$transaction->invoice->financial_credit_card_id, $transaction->invoice->id]);
                } elseif (!empty($transaction->is_recurrence) && !empty($transaction->recurrence_id)) {
                    $href = route('financial.recurrences.edit', $transaction->recurrence_id);
                } elseif ($transaction->id) {
                    $href = route('financial.transactions.show', $transaction->id);
                }
            @endphp
            @php
                $tagsIds = $transaction->relationLoaded('tags') ? $transaction->tags->pluck('id')->toJson() : '[]';
            @endphp
            <div style="display: contents"
                 x-data="{ tags: {{ $tagsIds }} }"
                 x-show="typeof selectedTagId === 'undefined' || selectedTagId === null || tags.includes(selectedTagId) || (selectedTagId === 0 && tags.length === 0)">
                <x-table.row href="{{ $href }}" class="hidden sm:grid sm:grid-cols-[1fr_2fr_1.5fr_1fr_1fr] group transition-all">
                    <x-table.cell>
                        <span class="font-medium text-neutral-900">{{ formatShort($transaction->date) }}</span>
                    </x-table.cell>

                <x-table.cell>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-neutral-900 truncate">
                            {{ $transaction->description }}
                            @if($transaction->installment_total > 1)
                                <span class="text-neutral-500 font-normal ml-1">({{ $transaction->installment_current }}/{{ $transaction->installment_total }})</span>
                            @endif
                        </span>
                        @if(!$transaction->is_posted && !$hidePendingBadge && empty($transaction->is_recurrence) && empty($transaction->is_invoice))
                            <span class="text-xxs uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Pendente</span>
                        @endif
                        @if(isset($transaction->is_recurrence) && $transaction->is_recurrence)
                            <span class="text-xxs uppercase font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-blue-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-arrow-path class="size-3" /> Recorrência</span>
                        @endif
                        @if(isset($transaction->is_invoice) && $transaction->is_invoice)
                            <span class="text-xxs uppercase font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-purple-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-document-text class="size-3" /> Fatura</span>
                        @endif
                    </div>
                </x-table.cell>

                <x-table.cell>
                    @php
                        $paymentsCount = $transaction->relationLoaded('payments') && $transaction->payments ? $transaction->payments->count() : 0;
                        
                        $payments = $transaction->relationLoaded('payments') ? $transaction->payments : collect();
                        $paymentsCount = $payments->count();
                        
                        // Fake relations set by ReportsService/DashboardService
                        $fakeInvoice = $transaction->relationLoaded('invoice') ? $transaction->invoice : null;
                        $fakeAccount = $transaction->relationLoaded('account') ? $transaction->account : null;
                        
                        $mainPayment = $paymentsCount > 0 ? $payments->sortByDesc('amount')->first() : null;
                        $invoice = $fakeInvoice ?? ($mainPayment ? $mainPayment->invoice : null);
                        $account = $fakeAccount ?? ($mainPayment ? $mainPayment->account : null);
                        
                        $otherPaymentsCount = $paymentsCount > 1 ? $paymentsCount - 1 : 0;
                        $otherPaymentsNames = '';
                        if ($otherPaymentsCount > 0) {
                            $otherPayments = $payments->sortByDesc('amount')->skip(1);
                            $otherPaymentsNames = $otherPayments->map(function($p) {
                                if (!empty($p->financial_credit_card_invoice_id) && $p->invoice) return $p->invoice->creditCard->name;
                                if (!empty($p->financial_account_id) && $p->account) return $p->account->name;
                                return 'Desconhecido';
                            })->join(', ');
                        }
                    @endphp
                    
                    @if($invoice)
                        <div class="flex items-center gap-1.5 text-neutral-600 truncate">
                            <x-heroicon-o-credit-card class="size-4 shrink-0" />
                            <span class="truncate">{{ $invoice->creditCard->name }}</span>
                            @if($otherPaymentsCount > 0)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xxs font-bold bg-neutral-100 text-neutral-500 ring-1 ring-inset ring-neutral-200 cursor-help" title="{{ $otherPaymentsNames }}">
                                    +{{ $otherPaymentsCount }}
                                </span>
                            @endif
                        </div>
                    @elseif($account)
                        <div class="flex items-center gap-1.5 text-neutral-600 truncate">
                            <x-heroicon-o-building-library class="size-4 shrink-0" />
                            <span class="truncate">{{ $account->name }}</span>
                            @if($otherPaymentsCount > 0)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xxs font-bold bg-neutral-100 text-neutral-500 ring-1 ring-inset ring-neutral-200 cursor-help" title="{{ $otherPaymentsNames }}">
                                    +{{ $otherPaymentsCount }}
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="text-neutral-400">-</span>
                    @endif
                </x-table.cell>

                <x-table.cell>
                    @php
                        $tags = $transaction->tags;
                        $primary = $tags->firstWhere('pivot.is_primary', true);
                        $others = $tags->reject(fn($t) => $t->id === optional($primary)->id);
                        
                        $visibleTags = collect(array_filter([$primary, $others->first()]))->take(2);
                        $remainingCount = $tags->count() - $visibleTags->count();
                    @endphp
                    
                    <div class="flex items-center gap-1.5">
                        @foreach($visibleTags as $tag)
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xxs font-semibold border"
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
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xxs font-bold bg-neutral-100 text-neutral-500 ring-1 ring-inset ring-neutral-200 cursor-help"
                                  title="{{ $others->skip($visibleTags->count() - ($primary ? 1 : 0))->pluck('name')->join(', ') }}">
                                +{{ $remainingCount }}
                            </span>
                        @endif
                    </div>
                </x-table.cell>

                <x-table.cell align="right">
                    <div class="flex justify-end gap-2 w-full">
                        <span class="font-bold tracking-tight text-base {{ $transaction->type === 'expense' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $transaction->type === 'expense' ? '-' : '+' }} {{ formatCurrency($transaction->amount) }}
                        </span>
                    </div>
                </x-table.cell>

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
                                    <span class="text-xxs uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20 shrink-0">Pendente</span>
                                @endif
                                @if(isset($transaction->is_recurrence) && $transaction->is_recurrence)
                                    <span class="text-xxs uppercase font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-blue-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-arrow-path class="size-3" /> Recorrência</span>
                                @endif
                                @if(isset($transaction->is_invoice) && $transaction->is_invoice)
                                    <span class="text-xxs uppercase font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-purple-600/20 shrink-0 flex items-center gap-1"><x-heroicon-o-document-text class="size-3" /> Fatura</span>
                                @endif
                            </h3>
                            <div class="flex items-center gap-2 text-sm text-neutral-500">
                                <span>{{ formatShort($transaction->date) }}</span>
                                <span>&bull;</span>
                                @if($invoice)
                                    <span class="truncate flex items-center gap-1">
                                        <x-heroicon-o-credit-card class="size-3" /> {{ $invoice->creditCard->name }}
                                        @if($otherPaymentsCount > 0)
                                            <span class="text-xxs ml-0.5 opacity-75">(+{{ $otherPaymentsCount }})</span>
                                        @endif
                                    </span>
                                @elseif($account)
                                    <span class="truncate flex items-center gap-1">
                                        <x-heroicon-o-building-library class="size-3" /> {{ $account->name }}
                                        @if($otherPaymentsCount > 0)
                                            <span class="text-xxs ml-0.5 opacity-75">(+{{ $otherPaymentsCount }})</span>
                                        @endif
                                    </span>
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
            </x-table.row>
            </div>
        @empty
            <x-empty-state 
                icon="heroicon-o-banknotes" 
                title="Nenhuma transação encontrada" 
                description="Não há transações disponíveis no momento."
            />
        @endforelse
    </x-table.body>
</x-table>
