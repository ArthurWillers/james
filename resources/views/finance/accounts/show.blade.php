<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas Financeiras</x-breadcrumbs.item>
            <x-breadcrumbs.item>{{ $account->name }}</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Detalhes da Conta">
        <x-button color="outline" href="{{ route('financial.accounts.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Voltar
        </x-button>

        <x-button color="outline" href="{{ route('financial.accounts.edit', $account) }}" class="bg-white">
            <x-heroicon-o-pencil-square class="size-4" />
            Editar
        </x-button>

        <x-modal.trigger name="delete-account-{{ $account->id }}">
            <x-button type="button" color="danger-outline">
                <x-heroicon-o-trash class="size-4" />
                Excluir
            </x-button>
        </x-modal.trigger>

        <x-modal 
            name="delete-account-{{ $account->id }}"
            title="Excluir Conta" 
            message="Tem certeza que deseja mover esta conta para a lixeira? Você poderá restaurá-la depois se precisar." 
            confirmVariant="danger">
            <form action="{{ route('financial.accounts.destroy', $account) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Mover para Lixeira
                </x-button>
            </form>
        </x-modal>
    </x-page-header>

    <x-card class="mb-6 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <x-ui.avatar :icon="$account->type->icon()" size="xl" />
                
                <div class="flex flex-col gap-2">
                    <h2 class="text-2xl font-bold text-neutral-900">{{ $account->name }}</h2>
                    <div>
                        <x-badge color="accent" size="sm">
                            {{ $account->type->label() }}
                        </x-badge>
                    </div>
                </div>
            </div>

            @if(!empty($account->pix_keys))
                <div class="md:text-right flex flex-col md:items-end gap-2 border-t md:border-t-0 md:border-l border-neutral-100 pt-4 md:pt-0 md:pl-6 overflow-x-auto">
                    <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Chaves Pix</h3>
                    <div class="flex gap-6 md:justify-end">
                        @foreach(array_chunk($account->pix_keys, 3) as $chunk)
                            <div class="flex flex-col gap-1.5 min-w-max">
                                @foreach($chunk as $pixKey)
                                    <div class="flex items-center md:justify-end gap-2 text-sm">
                                        <span class="font-medium text-neutral-500">{{ $pixKey['label'] }}:</span>
                                        <span class="font-semibold text-neutral-900">{{ $pixKey['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-card>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-card href="#" class="p-6 hover:shadow-xl transition-shadow duration-200 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600 group-hover:text-primary-600 transition-colors">Total de Receitas</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold text-green-600 break-words group-hover:text-green-700 transition-colors">
                        {{ formatCurrency($globalIncome) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-green-200 transition-colors">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </x-card>

        <x-card href="#" class="p-6 hover:shadow-xl transition-shadow duration-200 group">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600 group-hover:text-primary-600 transition-colors">Total de Despesas</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold text-red-600 break-words group-hover:text-red-700 transition-colors">
                        {{ formatCurrency($globalExpense) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-red-200 transition-colors">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </x-card>

        <x-card class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                        <p class="text-sm font-medium text-neutral-600">Saldo Atual</p>
                    </div>
                    <p class="text-xl sm:text-2xl font-semibold {{ $account->balance > 0 ? 'text-green-600' : ($account->balance < 0 ? 'text-red-600' : 'text-neutral-900') }} break-words">
                        @if($account->balance > 0)+@elseif($account->balance < 0)-@endif{{ formatCurrency(abs($account->balance)) }}
                    </p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $account->balance > 0 ? 'bg-green-100' : ($account->balance < 0 ? 'bg-red-100' : 'bg-neutral-100') }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-scale class="w-5 h-5 sm:w-6 sm:h-6 {{ $account->balance > 0 ? 'text-green-600' : ($account->balance < 0 ? 'text-red-600' : 'text-neutral-500') }}" />
                </div>
            </div>
        </x-card>
    </div>

    @if($creditCards->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Cartões de Crédito</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($creditCards as $card)
                    <x-card class="p-4 flex flex-col justify-between hover:border-neutral-300 transition-colors">
                        <div>
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="p-1.5 bg-neutral-100 rounded text-neutral-600 shrink-0">
                                        <x-heroicon-o-credit-card class="size-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-sm text-neutral-900 truncate" title="{{ $card->name }}">{{ $card->name }}</h3>
                                        <p class="text-[10px] text-neutral-500 truncate">Vence dia {{ $card->due_day }} • Fecha dia {{ $card->closing_day }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('financial.cards.show', $card) }}" class="p-1 -mr-1 -mt-1 text-neutral-400 hover:text-brand-600 transition-colors" title="Ver Cartão">
                                    <x-heroicon-o-arrow-top-right-on-square class="size-4" />
                                </a>
                            </div>

                            @if($card->credit_limit)
                                <div class="flex justify-between items-center text-xs text-neutral-500 mb-3">
                                    <span>Limite Total</span>
                                    <span class="font-semibold text-neutral-900">{{ formatCurrency($card->credit_limit) }}</span>
                                </div>
                            @endif

                            @php
                                $currentInvoice = $card->invoices->first();
                            @endphp

                            @if($currentInvoice)
                                <div class="flex justify-between items-end border-t border-neutral-100 pt-3">
                                    <div>
                                        <div class="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold mb-1">Fatura Atual</div>
                                        <div class="font-bold text-base leading-none {{ $currentInvoice->status() === 'paid' ? 'text-green-600' : 'text-neutral-900' }}">
                                            {{ formatCurrency($currentInvoice->total()) }}
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end gap-1">
                                        <x-badge :color="$currentInvoice->status() === 'paid' ? 'success' : 'warning'" class="text-[9px] px-1.5 py-0.5 leading-none">
                                            {{ $currentInvoice->status() === 'paid' ? 'Paga' : 'Pendente' }}
                                        </x-badge>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-xs text-neutral-400 italic border-t border-neutral-100 pt-3">
                                    Nenhuma fatura em aberto.
                                </div>
                            @endif
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    @endif

    @if($recentTransactions->isNotEmpty())
        <div class="flex justify-between items-center mb-4 mt-8">
            <h3 class="text-lg font-bold text-neutral-900">Últimas Transações</h3>
            <a href="{{ route('financial.transactions.index', ['financial_account_id' => $account->id]) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                Ver todas &rarr;
            </a>
        </div>

        <x-finance.transaction-table :transactions="$recentTransactions" class="lg:mb-8" />
    @endif
</x-layouts.financial>
