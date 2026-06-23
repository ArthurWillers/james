<x-layouts.app>
    <x-page-header title="Contatos" :action="route('contacts.create')" actionText="Novo Contato" icon="heroicon-o-plus">
        @if ($hasTrashed)
            <x-button color="outline" href="{{ route('contacts.trashed') }}">
                <x-heroicon-o-trash class="size-5!" />
                Lixeira
            </x-button>
        @endif
    </x-page-header>

    <x-filter-bar 
        action="{{ route('contacts.index') }}" 
        searchPlaceholder="Buscar nome ou anotações..." 
        :filters="['search', 'category']">
        
        <div class="w-full sm:w-auto">
            <select name="category" onchange="this.form.submit()" 
                    class="w-full sm:w-auto bg-transparent border-0 py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                <option value="">Todas categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($contacts as $contact)
            <x-card href="{{ route('contacts.show', $contact) }}" size="sm" class="flex items-center gap-4">
                <x-avatar :model="$contact" size="lg" />
                
                <div class="overflow-hidden">
                    <h3 class="font-semibold text-neutral-900 truncate">{{ $contact->name }}</h3>
                    <div class="mt-1">
                        <x-badge color="accent" size="sm">
                            {{ $contact->relationship_category ?: 'Sem categoria' }}
                        </x-badge>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-neutral-200">
                <x-empty-state 
                    icon="heroicon-o-users" 
                    message="Nenhum contato encontrado." 
                    actionText="Novo Contato" 
                    :actionRoute="route('contacts.create')" 
                />
            </div>
        @endforelse
    </div>

    <div class="mt-6 pb-6">
        {{ $contacts->links() }}
    </div>
</x-layouts.app>
