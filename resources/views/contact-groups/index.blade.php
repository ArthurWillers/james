<x-layouts.app>
    <x-page-header title="Grupos de Contato">
        <x-modal.trigger name="create-group">
            <x-button color="accent">
                <x-heroicon-o-plus class="size-4" />
                Novo Grupo
            </x-button>
        </x-modal.trigger>
    </x-page-header>

    <div x-data="{ search: '' }" class="space-y-4">
        <!-- Barra de Busca Front-end -->
        <div class="flex items-center bg-white rounded-lg border border-neutral-200 p-2 shadow-sm">
            <x-heroicon-o-magnifying-glass class="size-5 text-neutral-400 ml-2" />
            <input x-model="search" type="text" placeholder="Buscar grupo..." class="w-full bg-transparent border-0 focus:ring-0 text-sm text-neutral-700 placeholder-neutral-400">
        </div>

        <x-ui.table.index>
            <table class="w-full text-left text-sm whitespace-nowrap">
                <x-ui.table.header>
                    <tr>
                        <x-ui.table.column>Nome do Grupo</x-ui.table.column>
                        <x-ui.table.column>Membros</x-ui.table.column>
                        <x-ui.table.column class="text-right">Ações</x-ui.table.column>
                    </tr>
                </x-ui.table.header>
                <x-ui.table.body>
                    @forelse($groups as $group)
                        <x-ui.table.row x-show="search === '' || '{{ strtolower($group->name) }}'.includes(search.toLowerCase())">
                            <x-ui.table.cell class="font-medium text-neutral-900">{{ $group->name }}</x-ui.table.cell>
                            <x-ui.table.cell>
                                <x-badge color="accent" size="sm">{{ $group->contacts_count }}</x-badge>
                            </x-ui.table.cell>
                            <x-ui.table.cell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-modal.trigger name="edit-group-{{ $group->id }}">
                                        <x-button type="button" color="outline" class="bg-white">Editar</x-button>
                                    </x-modal.trigger>
    
                                    <x-modal.trigger name="delete-group-{{ $group->id }}">
                                        <x-button type="button" color="danger-outline">Excluir</x-button>
                                    </x-modal.trigger>
                                </div>
                            </x-ui.table.cell>
                        </x-ui.table.row>

                        <!-- Edit Modal -->
                        <x-modal name="edit-group-{{ $group->id }}" title="Editar Grupo">
                            <form action="{{ route('contact-groups.update', $group) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-neutral-700 mb-1">Nome do Grupo</label>
                                    <input type="text" name="name" value="{{ old('name', $group->name) }}" class="w-full rounded-md border-neutral-300 shadow-sm focus:border-accent focus:ring focus:ring-accent/50" required>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <x-button type="submit" color="accent">Salvar</x-button>
                                </div>
                            </form>
                        </x-modal>

                        <!-- Delete Modal -->
                        <x-modal name="delete-group-{{ $group->id }}" title="Excluir Grupo" message="Tem certeza que deseja excluir o grupo '{{ $group->name }}'? Os contatos não serão excluídos." confirmVariant="danger">
                            <form action="{{ route('contact-groups.destroy', $group) }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" color="red" class="w-full sm:w-auto">Excluir</x-button>
                            </form>
                        </x-modal>
                    @empty
                        <x-ui.table.row>
                            <x-ui.table.cell colspan="3" class="text-center text-neutral-500 py-8">Nenhum grupo encontrado.</x-ui.table.cell>
                        </x-ui.table.row>
                    @endforelse
                </x-ui.table.body>
            </table>
        </x-ui.table.index>
    </div>

    <!-- Create Modal -->
    <x-modal name="create-group" title="Novo Grupo">
        <form action="{{ route('contact-groups.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-1">Nome do Grupo</label>
                <input type="text" name="name" class="w-full rounded-md border-neutral-300 shadow-sm focus:border-accent focus:ring focus:ring-accent/50" required placeholder="Ex: Futebol, Família, Trabalho...">
            </div>
            <div class="flex justify-end gap-2">
                <x-button type="submit" color="accent">Criar</x-button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
