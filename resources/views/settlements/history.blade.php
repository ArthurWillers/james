<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Histórico Global</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Histórico Global">
        <div class="flex items-center gap-3">
            @if($hasTrashed)
                <x-button color="outline" href="{{ route('settlements.trashed') }}" class="bg-white text-neutral-500 hover:text-neutral-700">
                    <x-heroicon-o-trash class="size-4" />
                    Lixeira
                </x-button>
            @endif
            <x-button color="outline" href="{{ route('settlements.index') }}" class="bg-white">
                <x-heroicon-o-arrow-left class="size-4" />
                Voltar aos Acertos
            </x-button>
        </div>
    </x-page-header>

    <div class="mt-6">
        <x-table>
            <x-table.header class="hidden sm:grid grid-cols-5">
                <x-table.column>Data</x-table.column>
                <x-table.column>Contato</x-table.column>
                <x-table.column>Descrição</x-table.column>
                <x-table.column>Tipo</x-table.column>
                <x-table.column class="text-right">Valor</x-table.column>
            </x-table.header>

            <x-table.body>
                @forelse($settlements as $settlement)
                    @php
                        $isPositiveForMe = in_array($settlement->type->value, [\App\Enums\SettlementType::TheyOwe->value, \App\Enums\SettlementType::IPaid->value]);
                        $amountColor = $isPositiveForMe ? 'text-emerald-600' : 'text-red-600';
                        $amountPrefix = $isPositiveForMe ? '+' : '-';

                        // Temporary placeholder for contact ledger route
                        $settlementRoute = route('settlements.show_item', $settlement);
                    @endphp

                    <x-table.row href="{{ $settlementRoute }}" class="hidden sm:grid grid-cols-5">
                        <x-table.cell class="text-neutral-500">
                            {{ formatShort($settlement->date) }}
                        </x-table.cell>
                        
                        <x-table.cell>
                            <div class="flex items-center gap-3">
                                <x-avatar :model="$settlement->contact" size="sm" />
                                <span class="font-medium text-neutral-900 truncate">{{ $settlement->contact->name }}</span>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="text-neutral-700 font-medium truncate">{{ $settlement->description }}</span>
                        </x-table.cell>

                        <x-table.cell>
                            <x-badge :color="$settlement->type->color()" class="flex items-center gap-1 w-fit">
                                <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5" />
                                <span>{{ $settlement->type->label() }}</span>
                            </x-badge>
                        </x-table.cell>

                        <x-table.cell class="text-right font-semibold {{ $amountColor }}">
                            {{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}
                        </x-table.cell>

                        <!-- Mobile View -->
                        <x-slot:mobile>
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex flex-col gap-1 min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <x-badge :color="$settlement->type->color()" class="flex items-center shrink-0 w-fit">
                                            <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5" />
                                        </x-badge>
                                        <span class="font-medium text-neutral-900 truncate">{{ $settlement->description }}</span>

                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-neutral-500">
                                        <span class="truncate">{{ $settlement->contact->name }}</span>
                                        <x-heroicon-m-minus class="size-3 text-neutral-300" />
                                        <span>{{ formatShort($settlement->date) }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-semibold {{ $amountColor }}">{{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}</span>
                                    <div class="text-xs text-neutral-500 mt-0.5">{{ $settlement->type->label() }}</div>
                                </div>
                            </div>
                        </x-slot:mobile>
                    </x-table.row>
                @empty
                    <div class="col-span-full">
                        <x-empty-state 
                            icon="heroicon-o-queue-list" 
                            title="Nenhuma transação" 
                            description="Seu histórico global de acertos aparecerá aqui." 
                        />
                    </div>
                @endforelse
            </x-table.body>
        </x-table>

        <div class="mt-6">
            {{ $settlements->links() }}
        </div>
    </div>
</x-layouts.app>
