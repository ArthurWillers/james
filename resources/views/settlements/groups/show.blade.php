<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.groups.index') }}">Contas Divididas</x-breadcrumbs.item>
            <x-breadcrumbs.item>Detalhes</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="{{ $settlementGroup->description }}">
        <div class="flex items-center gap-3">
            @if(!$settlementGroup->trashed())
                <x-button color="outline" href="{{ route('settlements.groups.edit', $settlementGroup) }}" class="bg-white">
                    <x-heroicon-o-pencil class="size-4" />
                    Editar
                </x-button>
                <x-modal.trigger name="delete-group-{{ $settlementGroup->id }}">
                    <x-button color="danger-outline">
                        <x-heroicon-o-trash class="size-4" />
                        Excluir
                    </x-button>
                </x-modal.trigger>
            @endif
        </div>
    </x-page-header>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Items -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <x-card class="p-6">
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6">Participantes</h3>
                
                <div class="space-y-4">
                    @php
                        // Calculate my share based on total - sum of all contacts
                        $contactsTotal = $settlementGroup->settlements->sum('amount');
                        $myShare = max(0, $settlementGroup->total_amount - $contactsTotal);
                    @endphp

                    <!-- Minha Parte -->
                    <div class="flex items-center justify-between p-4 rounded-xl border border-accent/30 bg-accent/5">
                        <div class="flex items-center gap-4">
                            <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-md bg-accent/20 text-accent font-bold text-sm">
                                EU
                            </div>
                            <div>
                                <div class="font-medium text-neutral-900">Minha Parte</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-neutral-900">{{ formatCurrency($myShare) }}</span>
                        </div>
                    </div>

                    <!-- Contatos -->
                    @foreach($settlementGroup->settlements as $settlement)
                        <div class="flex items-center justify-between p-4 rounded-xl border border-neutral-100 bg-white">
                            <div class="flex items-center gap-4">
                                <x-ui.avatar :model="$settlement->contact" size="md" />
                                <div>
                                    <div class="font-medium text-neutral-900">{{ $settlement->contact->name }}</div>
                                    <div class="text-xs text-neutral-500">Deve reembolsar</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-neutral-900">{{ formatCurrency($settlement->amount) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-4 border-t border-neutral-100 flex justify-between items-center">
                    <span class="text-sm font-medium text-neutral-500">Total</span>
                    <span class="text-lg font-bold text-neutral-900">{{ formatCurrency($settlementGroup->total_amount) }}</span>
                </div>
            </x-card>
        </div>

        <!-- Right Column: Meta -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            <x-card class="p-6">
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Detalhes</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-neutral-500 mb-1">Data</div>
                        <div class="font-medium text-neutral-900">{{ formatShort($settlementGroup->date) }}</div>
                    </div>
                    
                    <div>
                        <div class="text-xs text-neutral-500 mb-1">Modo de Divisão</div>
                        <div class="font-medium text-neutral-900">
                            {{ $settlementGroup->mode === 'equal' ? 'Partes Iguais' : 'Valores Exatos' }}
                        </div>
                    </div>

                    @if($settlementGroup->financialTransaction)
                        <div class="pt-4 border-t border-neutral-100">
                            <div class="text-xs text-neutral-500 mb-2">Transação Financeira</div>
                            <a href="{{ route('financial.transactions.show', $settlementGroup->financialTransaction) }}" class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 hover:border-neutral-300 transition-colors bg-neutral-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white border border-neutral-200 flex items-center justify-center text-neutral-500">
                                        <x-heroicon-o-receipt-percent class="size-4" />
                                    </div>
                                    <span class="text-sm font-medium text-neutral-700">Ver Transação</span>
                                </div>
                                <x-heroicon-m-chevron-right class="size-4 text-neutral-400" />
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    <x-modal
        name="delete-group-{{ $settlementGroup->id }}"
        title="Excluir Divisão de Conta"
        message="Tem certeza que deseja excluir esta divisão de conta? Isso removerá todos os acertos vinculados a ela."
        confirmVariant="danger">
        <form action="{{ route('settlements.groups.destroy', $settlementGroup) }}" method="POST" class="m-0">
            @csrf
            @method('DELETE')
            <x-button type="submit" color="red" class="w-full sm:w-auto">
                Sim, excluir
            </x-button>
        </form>
    </x-modal>
</x-layouts.app>
