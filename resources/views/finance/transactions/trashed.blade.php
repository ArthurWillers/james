<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
            <x-breadcrumbs.item>Lixeira</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header 
        title="Lixeira" 
        description="Transações financeiras excluídas. Elas podem ser restauradas ou excluídas permanentemente." 
    >
        <x-button color="outline" href="{{ route('financial.transactions.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Voltar
        </x-button>
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.transactions.trashed') }}" 
        searchPlaceholder="Buscar transações na lixeira..." 
        :filters="['search']">
    </x-filter-bar>

    <x-ui.table class="lg:mb-8"
         x-data="{
             selectedTransactionId: null,
             selectedTransactionName: '',
             openRestore(id, name) {
                 this.selectedTransactionId = id;
                 this.selectedTransactionName = name;
                 $dispatch('modal-open', 'restore-transaction');
             },
             openForceDelete(id, name) {
                 this.selectedTransactionId = id;
                 this.selectedTransactionName = name;
                 $dispatch('modal-open', 'force-delete-transaction');
             }
         }">
        @if($transactions->isNotEmpty())
            {{-- Header - Desktop --}}
            <x-ui.table.header class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr_1.5fr]">
                <x-ui.table.column>Transação</x-ui.table.column>
                <x-ui.table.column>Valor</x-ui.table.column>
                <x-ui.table.column>Data da Exclusão</x-ui.table.column>
                <x-ui.table.column align="right">Ações</x-ui.table.column>
            </x-ui.table.header>
        @endif

        <x-ui.table.body>
            @forelse($transactions as $transaction)
                <x-ui.table.row class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr_1.5fr]">
                    <x-ui.table.cell>
                        <div class="flex items-center gap-3 w-full">
                            @php
                                $icon = match($transaction->type) {
                                    'income' => 'heroicon-o-arrow-trending-up',
                                    'expense' => 'heroicon-o-arrow-trending-down',
                                    'transfer' => 'heroicon-o-arrows-right-left',
                                    default => 'heroicon-o-currency-dollar'
                                };
                            @endphp
                            <div class="shrink-0 flex items-center justify-center size-10 rounded-full bg-neutral-100 text-neutral-400 grayscale opacity-80">
                                <x-dynamic-component :component="$icon" class="size-5" />
                            </div>
                            <div class="overflow-hidden">
                                <div class="font-medium text-neutral-900 truncate">{{ $transaction->description }}</div>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs text-neutral-500">{{ $transaction->date->format('d/m/Y') }}</span>
                                    @if($transaction->invoice)
                                        <x-badge color="neutral" size="sm"><x-heroicon-o-credit-card class="size-3 mr-1 inline"/> {{ $transaction->invoice->creditCard->name }}</x-badge>
                                    @elseif($transaction->account)
                                        <x-badge color="neutral" size="sm"><x-heroicon-o-building-library class="size-3 mr-1 inline"/> {{ $transaction->account->name }}</x-badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-ui.table.cell>

                    <x-ui.table.cell>
                        <span class="font-medium {{ $transaction->type === 'income' ? 'text-green-600' : ($transaction->type === 'expense' ? 'text-red-600' : 'text-neutral-900') }} opacity-80">
                            {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}{{ formatCurrency(abs($transaction->amount)) }}
                        </span>
                    </x-ui.table.cell>

                    <x-ui.table.cell>
                        <span class="text-sm text-neutral-500">
                            {{ $transaction->deleted_at->formatDateTime() }}
                        </span>
                    </x-ui.table.cell>

                    <x-ui.table.cell align="right">
                        <div class="flex justify-end gap-2 w-full">
                            <x-button type="button" color="outline" class="bg-white hover:bg-neutral-50 text-neutral-600 border-neutral-300" @click="openRestore({{ $transaction->id }}, '{{ addslashes($transaction->description) }}')">
                                <x-heroicon-o-arrow-uturn-left class="size-4" />
                                Restaurar
                            </x-button>

                            <x-button type="button" color="outline" class="bg-white hover:bg-red-50 text-red-600 border-red-200" @click="openForceDelete({{ $transaction->id }}, '{{ addslashes($transaction->description) }}')">
                                <x-heroicon-o-trash class="size-4" />
                                Excluir
                            </x-button>
                        </div>
                    </x-ui.table.cell>

                    <x-slot name="mobile">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0 flex items-center gap-3">
                                <div class="shrink-0 flex items-center justify-center size-10 rounded-full bg-neutral-100 text-neutral-400 grayscale opacity-80">
                                    <x-dynamic-component :component="$icon" class="size-5" />
                                </div>
                                <div class="overflow-hidden">
                                    <h3 class="text-base font-semibold text-neutral-900 leading-tight mb-1 truncate">
                                        {{ $transaction->description }}
                                    </h3>
                                    <div class="flex flex-col gap-1 text-sm text-neutral-500 mt-1">
                                        <div class="truncate text-xs font-medium {{ $transaction->type === 'income' ? 'text-green-600' : ($transaction->type === 'expense' ? 'text-red-600' : 'text-neutral-900') }}">
                                            {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}{{ formatCurrency(abs($transaction->amount)) }}
                                        </div>
                                        <span class="text-xs">Excluída em {{ $transaction->deleted_at->formatShort() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <x-dropdown position="bottom-end" accent>
                                    <x-slot name="trigger">
                                        <button type="button" class="cursor-pointer rounded-md border border-neutral-300 p-2 transition duration-150 ease-in-out hover:bg-neutral-100">
                                            <x-heroicon-o-ellipsis-horizontal class="size-5" />
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <button type="button" @click="openRestore({{ $transaction->id }}, '{{ addslashes($transaction->description) }}')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 cursor-pointer">
                                            <x-heroicon-o-arrow-uturn-left class="size-5" />
                                            Restaurar
                                        </button>

                                        <button type="button" @click="openForceDelete({{ $transaction->id }}, '{{ addslashes($transaction->description) }}')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-neutral-100 cursor-pointer">
                                            <x-heroicon-o-trash class="size-5" />
                                            Excluir Permanentemente
                                        </button>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>
                    </x-slot>
                </x-ui.table.row>
            @empty
                <x-empty-state 
                    icon="heroicon-o-trash" 
                    title="Nenhuma transação excluída" 
                    description="Não há transações excluídas recentemente na lixeira." 
                />
            @endforelse
        </x-ui.table.body>

        <x-modal 
            name="restore-transaction"
            title="Restaurar Transação" 
            confirmVariant="success">
            <x-slot name="content">
                Tem certeza que deseja restaurar a transação <span class="font-medium text-neutral-900" x-text="selectedTransactionName"></span>? Ela voltará a impactar os saldos da sua conta ou fatura.
            </x-slot>
            <form :action="'{{ route('financial.transactions.restore', 'REPLACE_ID') }}'.replace('REPLACE_ID', selectedTransactionId)" method="POST" class="m-0">
                @csrf
                @method('PATCH')
                <x-button type="submit" class="w-full sm:w-auto">
                    Confirmar Restauração
                </x-button>
            </form>
        </x-modal>

        <x-modal 
            name="force-delete-transaction"
            title="Exclusão Permanente" 
            confirmVariant="danger">
            <x-slot name="content">
                <p class="mb-3">Tem certeza que deseja excluir a transação <span class="font-medium text-neutral-900" x-text="selectedTransactionName"></span> permanentemente? Esta ação é irreversível.</p>
                <div class="rounded-md bg-amber-50 p-3 border border-amber-200">
                    <div class="flex">
                        <div class="shrink-0">
                            <x-heroicon-m-exclamation-triangle class="size-5 text-amber-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Cuidado</h3>
                            <div class="mt-1 text-sm text-amber-700">
                                <p>Todos os dados, tags e recibos atrelados a esta transação serão apagados da base de dados.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot>
            <form :action="'{{ route('financial.transactions.forceDestroy', 'REPLACE_ID') }}'.replace('REPLACE_ID', selectedTransactionId)" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Excluir Permanentemente
                </x-button>
            </form>
        </x-modal>
    </x-ui.table>

    @if($transactions->hasPages())
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    @endif
</x-layouts.financial>
