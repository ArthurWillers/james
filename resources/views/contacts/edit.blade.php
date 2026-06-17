@php
    $phones = collect(old('phones', $contact->phones ?? []))->map(function($phone) {
        return [
            'label' => is_array($phone) && !empty($phone['label']) ? $phone['label'] : 'Principal',
            'value' => is_array($phone) ? ($phone['value'] ?? '') : $phone,
        ];
    })->values()->all();

    $emails = collect(old('emails', $contact->emails ?? []))->map(function($email) {
        return [
            'label' => is_array($email) && !empty($email['label']) ? $email['label'] : 'Principal',
            'value' => is_array($email) ? ($email['value'] ?? '') : $email,
        ];
    })->values()->all();
@endphp

<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('contacts.index') }}">Contatos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('contacts.show', $contact) }}">{{ $contact->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Contato">
        <x-button color="outline" href="{{ route('contacts.show', $contact) }}" class="bg-white">
            <x-icons.outline.arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="edit-contact-form">
            <x-icons.outline.check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="edit-contact-form" action="{{ route('contacts.update', $contact) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <x-card class="mb-4 p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <!-- Avatar -->
                <div class="flex flex-col items-center gap-4 shrink-0">
                    <x-avatar :model="$contact" size="2xl"/>
                    <label class="cursor-pointer">
                        <span class="text-sm font-medium text-accent hover:text-accent/80 transition-colors">Alterar foto</span>
                        <input type="file" name="avatar" class="hidden" accept="image/jpeg,image/png,image/webp,image/heic,image/heif">
                    </label>
                    <x-form-error name="avatar" />
                </div>
                
                <div class="flex-1 w-full flex flex-col gap-4">
                    <div>
                        <x-form-input name="name" label="Nome" value="{{ old('name', $contact->name) }}" required />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-form-input name="relationship_category" label="Categoria" value="{{ old('relationship_category', $contact->relationship_category) }}" />
                        </div>
                        <div>
                            <x-form-input type="date" name="birthdate" label="Data de Nascimento" value="{{ old('birthdate', $contact->birthdate?->format('Y-m-d')) }}" />
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Phones -->
            <x-card class="h-full p-6 flex flex-col" x-data="{
                phones: {{ Js::from($phones) }},
                addPhone() {
                    this.phones.push({ label: 'Principal', value: '' });
                },
                removePhone(index) {
                    this.phones.splice(index, 1);
                }
            }">
                <div class="flex justify-between items-center mb-6 shrink-0">
                    <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Telefones</h3>
                    <button type="button" @click="addPhone" class="text-xs font-medium text-accent hover:text-accent/80 transition-colors cursor-pointer">
                        + Adicionar
                    </button>
                </div>
                
                <div class="overflow-y-auto max-h-[400px] pr-2 -mr-2">
                    <div class="flex flex-col gap-4">
                        <template x-for="(phone, index) in phones" :key="index">
                            <div class="flex gap-2 items-center">
                                <div class="w-1/3">
                                    <x-form-input required x-model="phone.label" x-bind:name="`phones[${index}][label]`" placeholder="Rótulo" />
                                </div>
                                <div class="flex-1">
                                    <x-form-input required x-model="phone.value" x-bind:name="`phones[${index}][value]`" placeholder="Número" />
                                </div>
                                <button type="button" @click="removePhone(index)" class="text-red-400 hover:text-red-600 transition-colors">
                                    <x-icons.outline.trash class="size-5" />
                                </button>
                            </div>
                        </template>
                        <p x-show="phones.length === 0" class="text-sm text-neutral-400 italic">Nenhum telefone cadastrado.</p>
                    </div>
                </div>
            </x-card>

            <!-- Emails -->
            <x-card class="h-full p-6 flex flex-col" x-data="{
                emails: {{ Js::from($emails) }},
                addEmail() {
                    this.emails.push({ label: 'Principal', value: '' });
                },
                removeEmail(index) {
                    this.emails.splice(index, 1);
                }
            }">
                <div class="flex justify-between items-center mb-6 shrink-0">
                    <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">E-mails</h3>
                    <button type="button" @click="addEmail" class="text-xs font-medium text-accent hover:text-accent/80 transition-colors cursor-pointer">
                        + Adicionar
                    </button>
                </div>
                
                <div class="overflow-y-auto max-h-[400px] pr-2 -mr-2">
                    <div class="flex flex-col gap-4">
                        <template x-for="(email, index) in emails" :key="index">
                            <div class="flex gap-2 items-center">
                                <div class="w-1/3">
                                    <x-form-input required x-model="email.label" x-bind:name="`emails[${index}][label]`" placeholder="Rótulo" />
                                </div>
                                <div class="flex-1">
                                    <x-form-input type="email" required x-model="email.value" x-bind:name="`emails[${index}][value]`" placeholder="Endereço de e-mail" />
                                </div>
                                <button type="button" @click="removeEmail(index)" class="text-red-400 hover:text-red-600 transition-colors">
                                    <x-icons.outline.trash class="size-5" />
                                </button>
                            </div>
                        </template>
                        <p x-show="emails.length === 0" class="text-sm text-neutral-400 italic">Nenhum e-mail cadastrado.</p>
                    </div>
                </div>
            </x-card>
        </div>

        <x-card class="mb-4 p-6">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6">Notas</h3>
            <div class="grid w-full items-center gap-1.5" x-data="{ mde: null }" x-init="mde = new EasyMDE({ element: $refs.editor, forceSync: true, status: false, spellChecker: false })">
                <textarea x-ref="editor" id="notes" name="notes" rows="6" placeholder="Anotações sobre o contato..." class="w-full border border-neutral-200 appearance-none text-sm rounded-xl block py-2.5 px-4 bg-white disabled:shadow-none shadow-xs focus:shadow-lg text-neutral-700 disabled:text-neutral-400 placeholder-neutral-400 disabled:placeholder-neutral-400/70 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors duration-300 {{ $errors->has('notes') ? 'border-red-500 focus:border-red-500 focus:ring-red-400/30' : '' }}">{{ old('notes', $contact->notes) }}</textarea>
                <x-form-error name="notes" />
            </div>
        </x-card>
    </form>
</x-layouts.app>
