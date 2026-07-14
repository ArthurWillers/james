<x-layouts.financial>
    <x-page-header title="Recorrências" :action="route('financial.recurrences.create')" actionText="Nova Recorrência" icon="heroicon-o-plus">
        @if($hasTrashed)
            <x-button color="outline" href="{{ route('financial.recurrences.trashed') }}" class="bg-white">
                <x-heroicon-o-trash class="size-4" />
                Lixeira
            </x-button>
        @endif
    </x-page-header>

    <div x-data="{
             selectedRecurrenceId: null,
             selectedRecurrenceTitle: '',
             openDelete(id, title) {
                 this.selectedRecurrenceId = id;
                 this.selectedRecurrenceTitle = title;
                 $dispatch('modal-open', 'delete-recurrence');
             }
         }">
        
        @if($recurrences->isNotEmpty())
            <x-table>
                <x-table.header class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr_1.5fr_1fr_1.5fr]">
                    <x-table.column>Título</x-table.column>
                    <x-table.column>Valor</x-table.column>
                    <x-table.column>Frequência</x-table.column>
                    <x-table.column>Conta/Cartão</x-table.column>
                    <x-table.column>Próximo Proc.</x-table.column>
                    <x-table.column align="right">Ações</x-table.column>
                </x-table.header>

                <x-table.body>
                    @foreach($recurrences as $recurrence)
                        <x-table.row class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr_1.5fr_1fr_1.5fr] group transition-all">
                            <x-table.cell>
                                <div class="flex items-center gap-3 w-full">
                                    <div class="shrink-0 flex items-center justify-center size-10 rounded-xl {{ $recurrence->type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    @if($recurrence->type === 'expense')
                                        <x-heroicon-o-arrow-trending-down class="size-5" />
                                    @else
                                        <x-heroicon-o-arrow-trending-up class="size-5" />
                                    @endif
                                </div>
                                <div class="overflow-hidden">
                                    <div class="font-medium text-neutral-900 truncate flex items-center gap-2">
                                        {{ $recurrence->title }}
                                        @if(!$recurrence->is_active)
                                            <span class="inline-flex items-center rounded-md bg-neutral-100 px-2 py-1 text-xs font-medium text-neutral-600">Pausada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="font-medium {{ $recurrence->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $recurrence->type === 'income' ? '+' : '-' }}{{ formatCurrency($recurrence->amount) }}
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex flex-col">
                                <span class="text-sm text-neutral-900">{{ $recurrence->frequency === 'monthly' ? 'Mensal' : 'Anual' }}</span>
                                <span class="text-xs text-neutral-400">Dia {{ $recurrence->start_date->format('d') }}</span>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="text-sm text-neutral-600 flex items-center gap-1">
                                @if($recurrence->financial_credit_card_id)
                                    <x-heroicon-o-credit-card class="size-4 text-neutral-400" />
                                    {{ $recurrence->financialCreditCard->name }}
                                @else
                                    <x-heroicon-o-building-library class="size-4 text-neutral-400" />
                                    {{ $recurrence->financialAccount->name }}
                                @endif
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="text-sm text-neutral-600">
                                {{ $recurrence->next_processing_date ? formatShort($recurrence->next_processing_date) : 'N/A' }}
                            </span>
                        </x-table.cell>

                        <x-table.cell align="right">
                            <div class="flex justify-end gap-2 w-full">
                                <x-button type="button" color="outline" size="sm" href="{{ route('financial.recurrences.edit', $recurrence) }}" class="bg-white hover:bg-neutral-50 text-neutral-600 border-neutral-300">
                                    <x-heroicon-o-pencil-square class="size-4" />
                                    Editar
                                </x-button>

                                <x-button type="button" color="outline" size="sm" class="bg-white hover:bg-red-50 text-red-600 border-red-200" @click="openDelete({{ $recurrence->id }}, '{{ addslashes($recurrence->title) }}')">
                                    <x-heroicon-o-trash class="size-4" />
                                    Excluir
                                </x-button>
                            </div>
                        </x-table.cell>

                        <x-slot name="mobile">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0 flex items-center gap-3">
                                    <div class="shrink-0 flex items-center justify-center size-10 rounded-xl {{ $recurrence->type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                        @if($recurrence->type === 'expense')
                                            <x-heroicon-o-arrow-trending-down class="size-5" />
                                        @else
                                            <x-heroicon-o-arrow-trending-up class="size-5" />
                                        @endif
                                    </div>
                                    <div class="overflow-hidden">
                                        <h3 class="text-base font-semibold text-neutral-900 leading-tight mb-1 truncate flex items-center gap-2">
                                            {{ $recurrence->title }}
                                            @if(!$recurrence->is_active)
                                                <span class="inline-flex items-center rounded-md bg-neutral-100 px-2 py-0.5 text-xxs font-medium text-neutral-600">Pausada</span>
                                            @endif
                                        </h3>
                                        <div class="flex flex-col gap-1 text-sm text-neutral-500 mt-1">
                                            <div class="truncate text-xs font-medium {{ $recurrence->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $recurrence->type === 'income' ? '+' : '-' }}{{ formatCurrency($recurrence->amount) }}
                                            </div>
                                            <span class="text-xs">Próx: {{ $recurrence->next_processing_date ? formatShort($recurrence->next_processing_date) : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <x-dropdown position="bottom-end" accent contentClass="min-w-max">
                                        <x-slot name="trigger">
                                            <button type="button" class="cursor-pointer rounded-md border border-neutral-300 p-2 transition duration-150 ease-in-out hover:bg-neutral-100">
                                                <x-heroicon-o-ellipsis-horizontal class="size-5" />
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <a href="{{ route('financial.recurrences.edit', $recurrence) }}" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 cursor-pointer">
                                                <x-heroicon-o-pencil-square class="size-5 shrink-0" />
                                                <span class="whitespace-nowrap">Editar</span>
                                            </a>

                                            <button type="button" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-neutral-100 cursor-pointer" @click="openDelete({{ $recurrence->id }}, '{{ addslashes($recurrence->title) }}')">
                                                <x-heroicon-o-trash class="size-5 shrink-0" />
                                                <span class="whitespace-nowrap">Excluir</span>
                                            </button>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                        </x-slot>
                    </x-table.row>
                @endforeach
            </x-table.body>
        </x-table>
        @else
            <x-card class="p-6">
                <x-empty-state 
                    icon="heroicon-o-arrow-path" 
                    title="Nenhuma recorrência cadastrada" 
                    description="Cadastre suas assinaturas ou contas fixas para que o sistema gere as transações automaticamente." 
                />
            </x-card>
        @endif

        <x-modal 
            name="delete-recurrence"
            title="Excluir Recorrência" 
            confirmVariant="danger">
            <x-slot name="content">
                <p>Tem certeza que deseja excluir a assinatura/conta fixa <span class="font-medium text-neutral-900" x-text="selectedRecurrenceTitle"></span>?</p>
                <p class="mt-2 text-sm text-neutral-500">Isso não apagará as transações que já foram geradas no passado.</p>
            </x-slot>
            <form :action="'{{ route('financial.recurrences.destroy', 'REPLACE_ID') }}'.replace('REPLACE_ID', selectedRecurrenceId)" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Confirmar Exclusão
                </x-button>
            </form>
        </x-modal>
        
        @if($recurrences->hasPages())
            <div class="mt-4">
                {{ $recurrences->links() }}
            </div>
        @endif
    </div>
</x-layouts.financial>
