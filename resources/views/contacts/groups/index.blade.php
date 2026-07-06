<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('contacts.index') }}">Contatos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Grupos de Contato</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Grupos de Contato" icon="heroicon-o-tag">
        <x-button href="{{ route('contacts.groups.create') }}">
            <x-heroicon-o-plus class="size-4" />
            Novo Grupo
        </x-button>
    </x-page-header>

    <x-filter-bar 
        action="{{ route('contacts.groups.index') }}" 
        searchPlaceholder="Buscar grupo..." 
        :filters="['search']">
    </x-filter-bar>

    <x-ui.table.index>
        <x-ui.table.header>
            <x-ui.table.row>
                <x-ui.table.column>Nome do Grupo</x-ui.table.column>
                <x-ui.table.column>Membros</x-ui.table.column>
                <x-ui.table.column class="text-right">Ações</x-ui.table.column>
            </x-ui.table.row>
        </x-ui.table.header>

        <x-ui.table.body>
            @forelse($groups as $group)
                <x-ui.table.row>
                    <x-ui.table.cell class="font-medium text-neutral-900">
                        {{ $group->name }}
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <x-badge color="accent" size="sm">
                            <x-heroicon-o-users class="size-3 mr-1" />
                            {{ $group->contacts_count }} membros
                        </x-badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <x-button color="outline" size="sm" href="{{ route('contacts.groups.edit', $group) }}" class="bg-white">
                                Editar
                            </x-button>

                            <x-modal.trigger name="delete-group-{{ $group->id }}">
                                <x-button type="button" color="danger-outline" size="sm">
                                    Excluir
                                </x-button>
                            </x-modal.trigger>
                        </div>

                        <!-- Delete Modal -->
                        <x-modal name="delete-group-{{ $group->id }}" title="Excluir Grupo" message="Tem certeza que deseja excluir o grupo '{{ $group->name }}'? Os contatos não serão excluídos." confirmVariant="danger">
                            <form action="{{ route('contacts.groups.destroy', $group) }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" color="red" class="w-full sm:w-auto">Excluir</x-button>
                            </form>
                        </x-modal>
                    </x-ui.table.cell>
                </x-ui.table.row>
            @empty
                <x-ui.table.row>
                    <x-ui.table.cell colspan="3">
                        <div class="py-8">
                            <x-empty-state 
                                icon="heroicon-o-tag" 
                                message="Nenhum grupo encontrado." 
                            />
                        </div>
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforelse
        </x-ui.table.body>
    </x-ui.table.index>
</x-layouts.app>
