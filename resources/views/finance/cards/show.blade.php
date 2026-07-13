<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>{{ $card->name }}</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Detalhes do Cartão">
        <x-ui.back-button fallback="{{ route('financial.cards.index') }}" />

        <x-button color="outline" href="{{ route('financial.cards.edit', $card) }}" class="bg-white">
            <x-heroicon-o-pencil-square class="size-4" />
            Editar
        </x-button>

        <x-ui.delete-modal 
            action="{{ route('financial.cards.destroy', $card) }}"
            item-name="o cartão"
            item-desc="{{ $card->name }}"
            title="Excluir Cartão"
        />
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-card class="col-span-full md:col-span-1 p-6 flex flex-col justify-center">
            <h3 class="text-neutral-500 font-medium text-sm mb-1">Conta Vinculada</h3>
            <div class="font-semibold text-lg text-neutral-900">{{ $card->financialAccount->name }}</div>
            
            <div class="mt-4 pt-4 border-t border-neutral-100 flex justify-between">
                <div>
                    <h3 class="text-neutral-500 font-medium text-xs mb-1">Fechamento</h3>
                    <div class="font-medium text-neutral-900">Dia {{ $card->closing_day }}</div>
                </div>
                <div>
                    <h3 class="text-neutral-500 font-medium text-xs mb-1">Vencimento</h3>
                    <div class="font-medium text-neutral-900">Dia {{ $card->due_day }}</div>
                </div>
            </div>
        </x-card>
        
        <x-card class="col-span-full md:col-span-2 p-6 flex flex-col justify-center">
            @if($card->credit_limit > 0)
                @php
                    $usedLimit = $card->usedLimit();
                    $percentage = min(100, max(0, ($usedLimit / $card->credit_limit) * 100));
                    $colorClass = $percentage > 90 ? 'bg-red-500' : ($percentage > 75 ? 'bg-amber-500' : 'bg-primary-500');
                    $available = max(0, $card->credit_limit - $usedLimit);
                @endphp
                
                <div class="flex justify-between items-end mb-4">
                    <div>
                        <h3 class="text-neutral-500 font-medium text-sm mb-1">Limite Disponível</h3>
                        <div class="font-bold text-3xl text-neutral-900">{{ formatCurrency($available) }}</div>
                    </div>
                    <div class="text-right">
                        <h3 class="text-neutral-500 font-medium text-sm mb-1">Limite Total</h3>
                        <div class="font-medium text-neutral-700">{{ formatCurrency($card->credit_limit) }}</div>
                    </div>
                </div>
                
                <x-progress :value="$usedLimit" :max="$card->credit_limit" :showValue="false" class="h-3" />
                <div class="mt-2 text-xs font-medium text-neutral-500">
                    Usado: {{ formatCurrency($usedLimit) }} ({{ number_format($percentage, 1, ',', '.') }}%)
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-4 text-center">
                    <x-heroicon-o-credit-card class="size-8 text-neutral-300 mb-2" />
                    <p class="text-neutral-500 font-medium">Este cartão não possui limite configurado.</p>
                </div>
            @endif
        </x-card>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-neutral-900">Faturas</h2>
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200">
                <thead class="bg-neutral-50/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Mês/Ano</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Fechamento</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Vencimento</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Valor</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ver</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-neutral-200">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-neutral-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-neutral-900">
                                    {{ formatMonthYearFull($invoice->reference_month) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                {{ formatShort($invoice->closing_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                                {{ formatShort($invoice->due_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @php $total = $invoice->total(); @endphp
                                <div class="font-medium {{ $total < 0 ? 'text-green-600' : 'text-neutral-900' }}">
                                    {{ formatCurrency($total) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $status = $invoice->status();
                                    $badgeColor = match($status) {
                                        'paid' => 'success',
                                        'partially_paid' => 'warning',
                                        'open' => 'primary',
                                        'overdue' => 'danger',
                                        'closed' => 'neutral',
                                        default => 'neutral'
                                    };
                                    $statusLabels = [
                                        'paid' => 'Paga',
                                        'partially_paid' => 'Parcial',
                                        'open' => 'Aberta',
                                        'overdue' => 'Atrasada',
                                        'closed' => 'Fechada',
                                    ];
                                @endphp
                                <x-badge :color="$badgeColor" size="sm">
                                    {{ $statusLabels[$status] ?? 'Desconhecido' }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('financial.cards.invoices.show', [$card, $invoice]) }}" class="text-primary-600 hover:text-primary-900 flex items-center justify-end gap-1">
                                    Detalhes <x-heroicon-o-chevron-right class="size-4" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-neutral-500">
                                Nenhuma fatura gerada para este cartão.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <div class="flex flex-col gap-1 text-xs text-neutral-500 mb-4 px-2 mt-4">
        <p>Criado em: {{ formatDateTime($card->created_at) }}</p>
        <p>Última atualização: {{ formatDateTime($card->updated_at) }}</p>
    </div>
</x-layouts.financial>
