w<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.cards.show', $card) }}">{{ $card->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Fatura {{ Str::title(Carbon\Carbon::parse($invoice->reference_month)->isoFormat('MMM/YY')) }}</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

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
            'partially_paid' => 'Parcialmente Paga',
            'open' => 'Aberta',
            'overdue' => 'Atrasada',
            'closed' => 'Fechada',
        ];
        
        $total = $invoice->total();
        $isFavorable = $total < 0;
        $remaining = max(0, $total - $invoice->amount_paid);
    @endphp

    <x-page-header title="Fatura de {{ Str::title(Carbon\Carbon::parse($invoice->reference_month)->isoFormat('MMMM YYYY')) }}">
        <x-slot:subtitle>
            <div class="flex items-center gap-2 mt-2">
                <x-badge :color="$badgeColor">
                    {{ $statusLabels[$status] ?? 'Desconhecido' }}
                </x-badge>
                <span class="text-sm text-neutral-500">Cartão: {{ $card->name }}</span>
            </div>
        </x-slot:subtitle>

        <x-modal.trigger name="edit-dates-modal">
            <x-button type="button" color="outline" class="bg-white">
                <x-heroicon-o-calendar-days class="size-4" />
                Editar Datas
            </x-button>
        </x-modal.trigger>

        @if($status !== 'paid' && !$isFavorable && $total > 0)
            <x-button x-data x-on:click="$dispatch('open-modal', 'pay-invoice-modal')">
                <x-heroicon-o-currency-dollar class="size-4" />
                Registrar Pagamento
            </x-button>
        @endif
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-card class="p-5">
            <h3 class="text-sm font-medium text-neutral-500 mb-1">Fechamento</h3>
            <div class="text-lg font-semibold text-neutral-900">{{ formatShort($invoice->closing_date) }}</div>
        </x-card>
        
        <x-card class="p-5">
            <h3 class="text-sm font-medium text-neutral-500 mb-1">Vencimento</h3>
            <div class="text-lg font-semibold text-neutral-900">{{ formatShort($invoice->due_date) }}</div>
        </x-card>
        
        <x-card class="p-5">
            <h3 class="text-sm font-medium text-neutral-500 mb-1">Total da Fatura</h3>
            <div class="text-lg font-bold {{ $isFavorable ? 'text-green-600' : 'text-neutral-900' }}">
                @if($isFavorable)
                    Saldo a seu favor: {{ formatCurrency(abs($total)) }}
                @else
                    {{ formatCurrency($total) }}
                @endif
            </div>
        </x-card>

        <x-card class="p-5">
            <h3 class="text-sm font-medium text-neutral-500 mb-1">Falta Pagar</h3>
            <div class="text-lg font-bold {{ $remaining > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ formatCurrency($remaining) }}
            </div>
            @if($invoice->amount_paid > 0)
                <div class="text-xs text-neutral-400 mt-1">Pago: {{ formatCurrency($invoice->amount_paid) }}</div>
            @endif
        </x-card>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-neutral-900">Transações</h2>
    </div>

    <x-finance.transaction-table :transactions="$transactions" class="lg:mb-8" />

    @if($status !== 'paid' && !$isFavorable && $total > 0)
        <!-- Modal Pagamento -->
        <x-ui.modal name="pay-invoice-modal" title="Pagar Fatura" maxWidth="sm">
            <form action="{{ route('financial.cards.invoices.pay', [$card, $invoice]) }}" method="POST" id="pay-form">
                @csrf
                <div class="space-y-4">
                    <p class="text-sm text-neutral-600 mb-4">
                        O pagamento será debitado da conta <strong>{{ $card->financialAccount->name }}</strong>.
                    </p>
                    
                    <x-form-input 
                        name="amount" 
                        type="number" 
                        step="0.01" 
                        label="Valor do Pagamento" 
                        value="{{ number_format($remaining, 2, '.', '') }}" 
                        required 
                    />
                    
                    <x-form-input 
                        name="paid_at" 
                        type="date" 
                        label="Data do Pagamento" 
                        value="{{ date('Y-m-d') }}" 
                        required 
                    />
                    
                    <x-form-input 
                        name="interest_amount" 
                        type="number" 
                        step="0.01" 
                        label="Juros e Multas (Opcional)" 
                        placeholder="0.00" 
                    />
                </div>
            </form>
            
            <x-slot:footer>
                <div class="flex justify-end gap-2 w-full">
                    <x-button color="outline" x-on:click="$dispatch('close-modal', 'pay-invoice-modal')" type="button">
                        Cancelar
                    </x-button>
                    <x-button form="pay-form" type="submit">
                        Confirmar Pagamento
                    </x-button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Editar Datas -->
    <x-ui.modal name="edit-dates-modal" title="Editar Datas da Fatura" confirmVariant="info">
        <x-slot name="content">
            <form action="{{ route('financial.cards.invoices.update', [$card, $invoice]) }}" method="POST" id="edit-dates-form" class="mt-4">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <x-form-input 
                        name="closing_date" 
                        type="date" 
                        label="Data de Fechamento" 
                        value="{{ old('closing_date', $invoice->closing_date->format('Y-m-d')) }}" 
                        required 
                    />
                    
                    <x-form-input 
                        name="due_date" 
                        type="date" 
                        label="Data de Vencimento" 
                        value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" 
                        required 
                    />
                </div>
            </form>
        </x-slot>
        
        <x-button form="edit-dates-form" type="submit" class="w-full sm:w-auto">
            Salvar Alterações
        </x-button>
    </x-ui.modal>
</x-layouts.financial>
