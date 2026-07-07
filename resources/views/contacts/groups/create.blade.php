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

            <div class="mb-6">
                <x-form-markdown-editor
                    name="notes"
                    label="Notas"
                    placeholder="Anotações sobre o grupo..."
                    :value="old('notes')"
                />
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

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[500px] overflow-y-auto p-1">
                    <template x-for="contact in filteredContacts" :key="contact.id">
                        <x-form.form-checkbox name="contact_ids[]" ::value="contact.id" id="" x-model="selectedIds" class="w-full p-3 rounded-xl border border-neutral-200 hover:border-accent hover:bg-neutral-50 transition-colors" x-bind:class="{'bg-accent/5 border-accent': selectedIds.includes(String(contact.id)) || selectedIds.includes(contact.id)}">
                            <div class="flex items-center gap-3">
                                <template x-if="contact.avatar">
                                    <img :src="contact.avatar" class="shrink-0 border rounded-md object-cover bg-neutral-200 border-[var(--color-accent)] w-10 h-10 text-sm">
                                </template>
                                <template x-if="!contact.avatar">
                                    <div class="shrink-0 flex items-center justify-center border rounded-md font-medium bg-neutral-200 border-neutral-300 text-neutral-700 w-10 h-10 text-sm" x-text="contact.name.substring(0, 2).toUpperCase()"></div>
                                </template>
                                <span class="text-sm font-medium text-neutral-700 truncate" x-text="contact.name"></span>
                            </div>
                        </x-form.form-checkbox>
                    </template>
                    
                    <div x-show="filteredContacts.length === 0" class="col-span-full p-8 text-center text-neutral-500 bg-neutral-50 rounded-xl border border-dashed border-neutral-200">
                        Nenhum contato encontrado.
                    </div>
                </div>
            </div>
        </x-card>
    </form>
</x-layouts.app>
