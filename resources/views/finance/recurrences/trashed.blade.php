<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.recurrences.index') }}">Recorrências</x-breadcrumbs.item>
            <x-breadcrumbs.item>Lixeira</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header 
        title="Lixeira" 
        description="Recorrências excluídas. Elas podem ser restauradas ou excluídas permanentemente." 
    >
        <x-button color="outline" href="{{ route('financial.recurrences.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Voltar
        </x-button>
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.recurrences.trashed') }}" 
        searchPlaceholder="Buscar assinaturas na lixeira..." 
        :filters="['search']">
    </x-filter-bar>

    <x-card class="overflow-hidden p-0 sm:p-0"
         x-data="{
             selectedRecurrenceId: null,
             selectedRecurrenceTitle: '',
             openRestore(id, title) {
                 this.selectedRecurrenceId = id;
                 this.selectedRecurrenceTitle = title;
                 $dispatch('modal-open', 'restore-recurrence');
             },
             openForceDelete(id, title) {
                 this.selectedRecurrenceId = id;
                 this.selectedRecurrenceTitle = title;
                 $dispatch('modal-open', 'force-delete-recurrence');
             }
         }">
        
        @if($recurrences->isNotEmpty())
            <x-ui.table>
                <x-ui.table.header class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr_1.5fr_1fr_1.5fr]">
                    <x-ui.table.column>Título</x-ui.table.column>
                    <x-ui.table.column>Valor</x-ui.table.column>
                    <x-ui.table.column>Frequência</x-ui.table.column>
                    <x-ui.table.column>Conta/Cartão</x-ui.table.column>
                    <x-ui.table.column>Data Exclusão</x-ui.table.column>
                    <x-ui.table.column align="right">Ações</x-ui.table.column>
                </x-ui.table.header>

                <x-ui.table.body>
                    @foreach($recurrences as $recurrence)
                        <x-ui.table.row class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr_1.5fr_1fr_1.5fr] opacity-80">
                            <x-ui.table.cell>
                                <div class="flex items-center gap-3 w-full">
                                    <div class="shrink-0 flex items-center justify-center size-10 rounded-full bg-neutral-100 text-neutral-400 grayscale">
                                        @if($recurrence->type === 'expense')
                                            <x-heroicon-o-arrow-trending-down class="size-5" />
                                        @else
                                            <x-heroicon-o-arrow-trending-up class="size-5" />
                                        @endif
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="font-medium text-neutral-900 truncate flex items-center gap-2">
                                            {{ $recurrence->title }}
                                        </div>
                                    </div>
                                </div>
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                <span class="font-medium {{ $recurrence->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $recurrence->type === 'income' ? '+' : '-' }}{{ formatCurrency($recurrence->amount) }}
                                </span>
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                <div class="flex flex-col">
                                    <span class="text-sm text-neutral-900">{{ $recurrence->frequency === 'monthly' ? 'Mensal' : 'Anual' }}</span>
                                    <span class="text-xs text-neutral-400">Dia {{ $recurrence->start_date->format('d') }}</span>
                                </div>
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                <div class="text-sm text-neutral-600 flex items-center gap-1">
                                    @if($recurrence->financial_credit_card_id)
                                        <x-heroicon-o-credit-card class="size-4 text-neutral-400" />
                                        {{ $recurrence->financialCreditCard->name }}
                                    @else
                                        <x-heroicon-o-building-library class="size-4 text-neutral-400" />
                                        {{ $recurrence->financialAccount->name }}
                                    @endif
                                </div>
                            </x-ui.table.cell>

                            <x-ui.table.cell>
                                <span class="text-sm text-neutral-600">
                                    {{ $recurrence->deleted_at->format('d/m/Y H:i') }}
                                </span>
                            </x-ui.table.cell>

                            <x-ui.table.cell align="right">
                                <div class="flex justify-end gap-2 w-full">
                                    <x-button type="button" color="outline" size="sm" class="bg-white hover:bg-neutral-50 text-neutral-600 border-neutral-300" @click="openRestore({{ $recurrence->id }}, '{{ addslashes($recurrence->title) }}')">
                                        <x-heroicon-o-arrow-uturn-left class="size-4" />
                                        Restaurar
                                    </x-button>

                                    <x-button type="button" color="outline" size="sm" class="bg-white hover:bg-red-50 text-red-600 border-red-200" @click="openForceDelete({{ $recurrence->id }}, '{{ addslashes($recurrence->title) }}')">
                                        <x-heroicon-o-trash class="size-4" />
                                        Excluir
                                    </x-button>
                                </div>
                            </x-ui.table.cell>

                            <x-slot name="mobile">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0 flex items-center gap-3">
                                        <div class="shrink-0 flex items-center justify-center size-10 rounded-full bg-neutral-100 text-neutral-400 grayscale">
                                            @if($recurrence->type === 'expense')
                                                <x-heroicon-o-arrow-trending-down class="size-5" />
                                            @else
                                                <x-heroicon-o-arrow-trending-up class="size-5" />
                                            @endif
                                        </div>
                                        <div class="overflow-hidden">
                                            <h3 class="text-base font-semibold text-neutral-900 leading-tight mb-1 truncate flex items-center gap-2">
                                                {{ $recurrence->title }}
                                            </h3>
                                            <div class="flex flex-col gap-1 text-sm text-neutral-500 mt-1">
                                                <div class="truncate text-xs font-medium {{ $recurrence->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $recurrence->type === 'income' ? '+' : '-' }}{{ formatCurrency($recurrence->amount) }}
                                                </div>
                                                <span class="text-xs">Excluída: {{ $recurrence->deleted_at->format('d/m/Y') }}</span>
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
                                                <button type="button" @click="openRestore({{ $recurrence->id }}, '{{ addslashes($recurrence->title) }}')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 cursor-pointer">
                                                    <x-heroicon-o-arrow-uturn-left class="size-5" />
                                                    Restaurar
                                                </button>

                                                <button type="button" @click="openForceDelete({{ $recurrence->id }}, '{{ addslashes($recurrence->title) }}')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-neutral-100 cursor-pointer">
                                                    <x-heroicon-o-trash class="size-5" />
                                                    Excluir Permanentemente
                                                </button>
                                            </x-slot>
                                        </x-dropdown>
                                    </div>
                                </div>
                            </x-slot>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        @else
            <div class="p-6">
                <x-empty-state 
                    icon="heroicon-o-trash" 
                    title="Nenhuma recorrência excluída" 
                    description="Não há assinaturas ou contas fixas na lixeira." 
                />
            </div>
        @endif

        <x-modal 
            name="restore-recurrence"
            title="Restaurar Recorrência" 
            confirmVariant="success">
            <x-slot name="content">
                Tem certeza que deseja restaurar a recorrência <span class="font-medium text-neutral-900" x-text="selectedRecurrenceTitle"></span>? Ela voltará a gerar transações ativamente.
            </x-slot>
            <form :action="'{{ route('financial.recurrences.restore', 'REPLACE_ID') }}'.replace('REPLACE_ID', selectedRecurrenceId)" method="POST" class="m-0">
                @csrf
                @method('PATCH')
                <x-button type="submit" class="w-full sm:w-auto">
                    Confirmar Restauração
                </x-button>
            </form>
        </x-modal>

        <x-modal 
            name="force-delete-recurrence"
            title="Exclusão Permanente" 
            confirmVariant="danger">
            <x-slot name="content">
                <p class="mb-3">Tem certeza que deseja excluir a recorrência <span class="font-medium text-neutral-900" x-text="selectedRecurrenceTitle"></span> permanentemente? Esta ação é irreversível.</p>
                <div class="rounded-md bg-amber-50 p-3 border border-amber-200">
                    <div class="flex">
                        <div class="shrink-0">
                            <x-heroicon-m-exclamation-triangle class="size-5 text-amber-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Cuidado</h3>
                            <div class="mt-1 text-sm text-amber-700">
                                <p>Isso não excluirá as transações geradas por esta recorrência, mas o vínculo se perderá para sempre.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot>
            <form :action="'{{ route('financial.recurrences.forceDestroy', 'REPLACE_ID') }}'.replace('REPLACE_ID', selectedRecurrenceId)" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Excluir Permanentemente
                </x-button>
            </form>
        </x-modal>
        
        @if($recurrences->hasPages())
            <div class="px-6 py-4 border-t border-neutral-200">
                {{ $recurrences->links() }}
            </div>
        @endif
    </x-card>
</x-layouts.financial>
