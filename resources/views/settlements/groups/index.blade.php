<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Contas Divididas</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Contas Divididas">
        <div class="flex items-center gap-3">
            <x-button color="outline" href="{{ route('settlements.groups.trashed') }}" class="bg-white text-neutral-500 hover:text-neutral-700">
                <x-heroicon-o-trash class="size-4" />
                Ver Excluídos
            </x-button>
        </div>
    </x-page-header>

    <div class="mt-6">
        <x-ui.table>
            <x-ui.table.header class="hidden sm:grid grid-cols-5">
                <x-ui.table.column>Data</x-ui.table.column>
                <x-ui.table.column class="col-span-2">Descrição</x-ui.table.column>
                <x-ui.table.column>Divisão</x-ui.table.column>
                <x-ui.table.column class="text-right">Total</x-ui.table.column>
            </x-ui.table.header>

            <x-ui.table.body>
                @forelse($groups as $group)
                    <x-ui.table.row href="{{ route('settlements.groups.show', $group) }}" class="hidden sm:grid grid-cols-5">
                        <x-ui.table.cell class="text-neutral-500">
                            {{ formatShort($group->date) }}
                        </x-ui.table.cell>

                        <x-ui.table.cell class="col-span-2">
                            <div class="flex flex-col">
                                <span class="font-medium text-neutral-900 truncate">{{ $group->description }}</span>
                                <span class="text-xs text-neutral-500">{{ $group->settlements->count() }} participante(s)</span>
                            </div>
                        </x-ui.table.cell>

                        <x-ui.table.cell>
                            <x-ui.badge color="gray">
                                {{ $group->mode === 'equal' ? 'Partes Iguais' : 'Valores Exatos' }}
                            </x-ui.badge>
                        </x-ui.table.cell>

                        <x-ui.table.cell class="text-right font-semibold text-neutral-900">
                            {{ formatCurrency($group->total_amount) }}
                        </x-ui.table.cell>

                        <!-- Mobile View -->
                        <x-slot:mobile>
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex flex-col gap-1 min-w-0 flex-1">
                                    <span class="font-medium text-neutral-900 truncate">{{ $group->description }}</span>
                                    <div class="flex items-center gap-2 text-sm text-neutral-500">
                                        <span>{{ formatShort($group->date) }}</span>
                                        <x-heroicon-m-minus class="size-3 text-neutral-300" />
                                        <span>{{ $group->settlements->count() }} participante(s)</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-semibold text-neutral-900">{{ formatCurrency($group->total_amount) }}</span>
                                </div>
                            </div>
                        </x-slot:mobile>
                    </x-ui.table.row>
                @empty
                    <div class="col-span-full">
                        <x-ui.empty-state
                            icon="heroicon-o-users"
                            title="Nenhuma conta dividida"
                            description="Você ainda não dividiu nenhuma conta."
                        />
                    </div>
                @endforelse
            </x-ui.table.body>
        </x-ui.table>

        <div class="mt-6">
            {{ $groups->links() }}
        </div>
    </div>
</x-layouts.app>
