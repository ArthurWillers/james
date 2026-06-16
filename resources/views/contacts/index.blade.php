<x-layouts.app>
    <x-page-header title="Contatos" :action="route('contacts.create')" actionText="Novo Contato" icon="plus" />

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
                    icon="users" 
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
