<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Contas Divididas</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Contas Divididas">
            @if($hasTrashed)
                <x-button color="outline" href="{{ route('settlements.groups.trashed') }}" class="bg-white text-neutral-500 hover:text-neutral-700 w-full sm:w-auto">
                    <x-heroicon-o-trash class="size-4" />
                    Lixeira
                </x-button>
            @endif
    </x-page-header>

    <div class="mt-6">
        <x-table>
            <x-table.header class="hidden sm:grid grid-cols-6">
                <x-table.column>Data</x-table.column>
                <x-table.column class="col-span-2">Descrição</x-table.column>
                <x-table.column>Divisão</x-table.column>
                <x-table.column>Pagamento</x-table.column>
                <x-table.column class="text-right">Total</x-table.column>
            </x-table.header>

            <x-table.body>
                @forelse($groups as $group)
                    <x-table.row href="{{ route('settlements.groups.show', $group) }}" class="hidden sm:grid grid-cols-6">
                        <x-table.cell class="text-neutral-500">
                            {{ formatShort($group->date) }}
                        </x-table.cell>

                        <x-table.cell class="col-span-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 border border-red-100 flex items-center justify-center shrink-0">
                                    <x-heroicon-o-users class="size-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-medium text-neutral-900 truncate">{{ $group->description }}</span>
                                    <span class="text-xs text-neutral-500">{{ $group->settlements->count() }} participante(s)</span>
                                </div>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <x-badge color="accent" class="w-fit">
                                {{ $group->mode === 'equal' ? 'Partes Iguais' : 'Valores Exatos' }}
                            </x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            @php
                                $transaction = $group->financialTransaction;
                            @endphp
                            @if($transaction)
                                @if($transaction->invoice)
                                    <div class="flex items-center gap-1.5 truncate text-neutral-600">
                                        <x-heroicon-o-credit-card class="size-4 shrink-0" />
                                        <span class="truncate">{{ $transaction->invoice->creditCard->name }}</span>
                                    </div>
                                @elseif($transaction->account)
                                    <div class="flex items-center gap-1.5 truncate text-neutral-600">
                                        <x-heroicon-o-building-library class="size-4 shrink-0" />
                                        <span class="truncate">{{ $transaction->account->name }}</span>
                                    </div>
                                @endif
                            @else
                                <span class="text-neutral-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell class="text-right font-bold text-red-600">
                            - {{ formatCurrency($group->total_amount) }}
                        </x-table.cell>

                        <!-- Mobile View -->
                        <x-slot:mobile>
                            <div class="flex justify-between items-center gap-4">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 border border-red-100 flex items-center justify-center shrink-0">
                                        <x-heroicon-o-users class="size-5" />
                                    </div>
                                    <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                                        <span class="font-medium text-neutral-900 truncate">{{ $group->description }}</span>
                                        <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                                            <span>{{ formatShort($group->date) }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $group->settlements->count() }} pessoas</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-red-600">- {{ formatCurrency($group->total_amount) }}</span>
                                </div>
                            </div>
                        </x-slot:mobile>
                    </x-table.row>
                @empty
                    <div class="col-span-full">
                        <x-empty-state
                            icon="heroicon-o-users"
                            title="Nenhuma conta dividida"
                            description="Você ainda não dividiu nenhuma conta."
                        />
                    </div>
                @endforelse
            </x-table.body>
        </x-table>

        <div class="mt-6">
            {{ $groups->links() }}
        </div>
    </div>
</x-layouts.app>
