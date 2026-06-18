<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('contacts.index') }}">Contatos</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Novo Contato</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Contato">
        <x-button color="outline" href="{{ route('contacts.index') }}" class="bg-white">
            <x-icons.outline.arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="create-contact-form">
            <x-icons.outline.check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-contact-form" action="{{ route('contacts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-card class="mb-4 p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                <x-form-image-cropper
                    name="avatar"
                    :model="new \App\Models\Contact()"
                    label-add="Adicionar foto"
                />
                
                <div class="flex-1 w-full flex flex-col gap-4">
                    <div>
                        <x-form-input name="name" label="Nome" value="{{ old('name') }}" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-form-combobox 
                                name="relationship_category" 
                                label="Categoria" 
                                :value="old('relationship_category')" 
                                :options="$categories" 
                            />
                        </div>
                        <div>
                            <x-form-input type="date" name="birthdate" label="Data de Nascimento" value="{{ old('birthdate') }}" />
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-form-key-value-repeater 
                name="phones" 
                title="Telefones" 
                :items="$phones" 
                value-placeholder="Número" 
                empty-message="Nenhum telefone cadastrado." 
            />

            <x-form-key-value-repeater 
                name="emails" 
                title="E-mails" 
                :items="$emails" 
                value-placeholder="Endereço de e-mail" 
                empty-message="Nenhum e-mail cadastrado." 
            />
        </div>

        <x-form-markdown-editor
            name="notes"
            label="Notas"
            placeholder="Anotações sobre o contato..."
            :value="old('notes')"
        />
    </form>
</x-layouts.app>
