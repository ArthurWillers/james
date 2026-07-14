<x-layouts.app>
    <x-page-header title="Contatos" :action="route('contacts.create')" actionText="Novo Contato" icon="heroicon-o-plus">
        <x-button color="outline" href="{{ route('contacts.groups.index') }}" class="bg-white">
            <x-heroicon-o-tag class="size-5!" />
            Grupos
        </x-button>
        @if ($hasTrashed)
            <x-button color="outline" href="{{ route('contacts.trashed') }}" class="bg-white">
                <x-heroicon-o-trash class="size-5!" />
                Lixeira
            </x-button>
        @endif
    </x-page-header>

    <x-filter-bar 
        action="{{ route('contacts.index') }}" 
        searchPlaceholder="Buscar nome ou anotações..." 
        :filters="['search', 'category', 'group_id']">
        
        <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2">
            <select name="group_id" 
                    class="w-full sm:w-auto bg-transparent border-0 py-2 sm:py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                <option value="">Todos os grupos</option>
                @foreach($groups as $grp)
                    <option value="{{ $grp->id }}" @selected((int)request('group_id') === $grp->id)>{{ $grp->name }}</option>
                @endforeach
            </select>

            <select name="category" 
                    class="w-full sm:w-auto bg-transparent border-0 py-2 sm:py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                <option value="">Todas categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($contacts as $contact)
            <x-card href="{{ route('contacts.show', $contact) }}" size="sm" class="flex items-center gap-4 relative group/card">
                <x-avatar :model="$contact" size="lg" />
                
                <div class="overflow-hidden flex-1">
                    <h3 class="font-semibold text-neutral-900 truncate">{{ $contact->name }}</h3>
                    <div class="mt-1">
                        <x-badge color="accent" size="sm">
                            {{ $contact->relationship_category ?: 'Sem categoria' }}
                        </x-badge>
                    </div>
                </div>

                <div class="text-neutral-400 group-hover/card:text-accent transition-colors">
                    <x-heroicon-o-chevron-right class="size-5" />
                </div>
            </x-card>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-neutral-200">
                <x-empty-state 
                    icon="heroicon-o-users" 
                    message="Nenhum contato encontrado." 
                />
            </div>
        @endforelse
    </div>

    <div class="mt-6 pb-6">
        {{ $contacts->links() }}
    </div>
</x-layouts.app>
