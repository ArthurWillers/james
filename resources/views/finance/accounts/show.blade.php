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

    @if(!empty($account->pix_keys) || $creditCards->isNotEmpty())
        <div class="grid grid-cols-1 {{ (!empty($account->pix_keys) && $creditCards->isNotEmpty()) ? 'md:grid-cols-2' : '' }} gap-6 mb-6">
            @if(!empty($account->pix_keys))
                <x-card class="h-full p-6 flex flex-col">
                    <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6 shrink-0">Chaves Pix</h3>
                    <div class="overflow-y-auto max-h-[300px] pr-2 -mr-2">
                        <div class="divide-y divide-neutral-100">
                            @foreach($account->pix_keys as $pixKey)
                                <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-6 py-3 first:pt-0 last:pb-0">
                                    <span class="text-sm font-medium text-neutral-400 sm:w-24 shrink-0">{{ $pixKey['label'] }}</span>
                                    <span class="text-[15px] text-neutral-800 break-all">{{ $pixKey['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endif

            @if($creditCards->isNotEmpty())
                <div class="flex flex-col gap-4">
                    @foreach($creditCards as $card)
                        <x-card class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-neutral-100 rounded-lg text-neutral-600">
                                        <x-heroicon-o-credit-card class="size-5" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-neutral-900">{{ $card->name }}</h3>
                                        <p class="text-xs text-neutral-500">
                                            Vencimento dia {{ $card->due_day }} • Fechamento dia {{ $card->closing_day }}
                                        </p>
                                    </div>
                                </div>
                                @if($card->credit_limit)
                                    <div class="text-right">
                                        <div class="text-xs text-neutral-500">Limite</div>
                                        <div class="font-medium text-neutral-900">{{ formatCurrency($card->credit_limit) }}</div>
                                    </div>
                                @endif
                            </div>

                            @php
                                $currentInvoice = $card->invoices->first();
                            @endphp

                            @if($currentInvoice)
                                <div class="bg-neutral-50 rounded-lg p-4 flex justify-between items-center">
                                    <div>
                                        <div class="text-xs text-neutral-500 mb-1">Fatura atual</div>
                                        <div class="font-bold text-lg {{ $currentInvoice->isPaid() ? 'text-green-600' : 'text-neutral-900' }}">
                                            {{ formatCurrency($currentInvoice->total()) }}
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <x-badge :color="$currentInvoice->isPaid() ? 'success' : 'warning'" size="sm" class="mb-1">
                                            {{ $currentInvoice->isPaid() ? 'Paga' : 'Pendente' }}
                                        </x-badge>
                                        <span class="text-xs text-neutral-500">Vence em {{ $currentInvoice->due_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="bg-neutral-50 rounded-lg p-4 text-center">
                                    <span class="text-sm text-neutral-500 italic">Nenhuma fatura em aberto.</span>
                                </div>
                            @endif

                            <div class="mt-4 pt-4 border-t border-neutral-100 text-center">
                                <a href="#" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                                    Ver todas as faturas &rarr;
                                </a>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($recentTransactions->isNotEmpty())
        <x-card class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Últimas Transações</h3>
                <a href="#" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                    Ver todas &rarr;
                </a>
            </div>

            <div class="divide-y divide-neutral-100">
                @foreach($recentTransactions as $transaction)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-full {{ $transaction->type === 'income' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                @if($transaction->type === 'income')
                                    <x-heroicon-o-arrow-down-left class="size-4" />
                                @else
                                    <x-heroicon-o-arrow-up-right class="size-4" />
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-neutral-900">{{ $transaction->description }}</div>
                                <div class="text-xs text-neutral-500">{{ $transaction->date->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium {{ $transaction->type === 'income' ? 'text-green-600' : 'text-neutral-900' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}{{ formatCurrency($transaction->amount) }}
                            </div>
                            @if(!$transaction->is_posted)
                                <x-badge color="warning" size="sm" class="mt-1">Pendente</x-badge>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</x-layouts.financial>
