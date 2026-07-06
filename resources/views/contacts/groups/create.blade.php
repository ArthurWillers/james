<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('contacts.index') }}">Contatos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('contacts.groups.index') }}">Grupos de Contato</x-breadcrumbs.item>
            <x-breadcrumbs.item>Novo Grupo</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Grupo">
        <x-button color="outline" href="{{ route('contacts.groups.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="create-group-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-group-form" action="{{ route('contacts.groups.store') }}" method="POST">
        @csrf
        <x-card class="mb-4 p-6">
            <div class="mb-6">
                <x-form-input name="name" label="Nome do Grupo" value="{{ old('name') }}" placeholder="Ex: Futebol, Família, Trabalho..." required />
            </div>

            <div x-data="{
                search: '',
                contacts: {{ Js::from($allContacts) }},
                selectedIds: {{ Js::from(old('contact_ids', [])) }},
                get filteredContacts() {
                    if (this.search === '') return this.contacts;
                    const s = this.search.toLowerCase();
                    return this.contacts.filter(c => c.name.toLowerCase().includes(s));
                }
            }">
                <h3 class="text-sm font-semibold text-neutral-700 mb-3">Membros do Grupo</h3>
                
                <div class="relative mb-4">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-heroicon-o-magnifying-glass class="size-5 text-neutral-400" />
                    </div>
                    <input type="text" x-model="search" placeholder="Buscar contatos pelo nome..." class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-neutral-900 ring-1 ring-inset ring-neutral-300 placeholder:text-neutral-400 focus:ring-2 focus:ring-inset focus:ring-accent sm:text-sm sm:leading-6 transition-colors">
                </div>

                <div class="border border-neutral-200 rounded-xl max-h-[400px] overflow-y-auto divide-y divide-neutral-100 bg-neutral-50/30">
                    <template x-for="contact in filteredContacts" :key="contact.id">
                        <label class="flex items-center gap-4 p-4 hover:bg-white cursor-pointer transition-colors" :class="{'bg-accent/5': selectedIds.includes(String(contact.id)) || selectedIds.includes(contact.id)}">
                            <input type="checkbox" name="contact_ids[]" :value="contact.id" x-model="selectedIds" class="rounded border-neutral-300 text-accent focus:ring-accent size-4">
                            
                            <div class="flex items-center gap-3">
                                <div class="shrink-0">
                                    <template x-if="contact.avatar">
                                        <img :src="contact.avatar" class="size-10 rounded-full object-cover">
                                    </template>
                                    <template x-if="!contact.avatar">
                                        <div class="size-10 rounded-full bg-neutral-200 flex items-center justify-center text-neutral-500 font-semibold text-sm">
                                            <span x-text="contact.name.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                    </template>
                                </div>
                                <span class="text-sm font-medium text-neutral-700" x-text="contact.name"></span>
                            </div>
                        </label>
                    </template>
                    
                    <div x-show="filteredContacts.length === 0" class="p-8 text-center text-neutral-500">
                        Nenhum contato encontrado.
                    </div>
                </div>
            </div>
        </x-card>
    </form>
</x-layouts.app>
