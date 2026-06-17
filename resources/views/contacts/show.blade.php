<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('contacts.index') }}">Contatos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Detalhes</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Detalhes do Contato">
        <x-button color="outline" href="{{ route('contacts.index') }}" class="bg-white">
            <x-icons.outline.arrow-left class="size-4" />
            Voltar
        </x-button>

        <x-button color="outline" href="{{ route('contacts.edit', $contact) }}" class="bg-white">
            <x-icons.outline.pencil-square class="size-4" />
            Editar
        </x-button>

        <x-modal.trigger name="delete-contact-{{ $contact->id }}">
            <x-button type="button" color="outline" class="bg-white text-red-500 hover:text-red-600 border-red-200 hover:border-red-300 hover:bg-red-50">
                <x-icons.outline.trash class="size-4" />
                Excluir
            </x-button>
        </x-modal.trigger>

        <x-modal 
            name="delete-contact-{{ $contact->id }}"
            title="Excluir Contato" 
            message="Tem certeza que deseja mover este contato para a lixeira? Você poderá restaurá-lo depois se precisar." 
            confirmVariant="danger">
            <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="red" class="w-full sm:w-auto">
                    Mover para Lixeira
                </x-button>
            </form>
        </x-modal>
    </x-page-header>

    <x-card class="mb-4 p-6">
        <div class="flex items-center gap-6">
            <x-avatar :model="$contact" size="2xl"/>
            
            <div class="flex flex-col gap-2">
                <h2 class="text-2xl font-bold text-neutral-900">{{ $contact->name }}</h2>
                @if($contact->relationship_category)
                    <div>
                        <x-badge color="accent" size="sm">
                            {{ $contact->relationship_category }}
                        </x-badge>
                    </div>
                @endif
            </div>
        </div>

        @if($contact->birthdate)
            <div class="flex items-center gap-2 text-sm text-neutral-600 mt-2">
                {{ $contact->birthdate->translatedFormat('d \d\e F \d\e Y') }}
            </div>
        @endif
    </x-card>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <x-card class="h-full p-6 flex flex-col">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6 shrink-0">Telefones</h3>
            @if(!empty($contact->phones))
                <div class="overflow-y-auto max-h-[300px] pr-2 -mr-2">
                    <div class="divide-y divide-neutral-100">
                        @foreach($contact->phones as $phone)
                            @php
                                $label = is_array($phone) && !empty($phone['label']) ? $phone['label'] : 'Principal';
                                $value = is_array($phone) ? ($phone['value'] ?? '') : $phone;
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-6 py-3 first:pt-0 last:pb-0">
                                <span class="text-sm font-medium text-neutral-400 sm:w-24 shrink-0">{{ $label }}</span>
                                <span class="text-[15px] text-neutral-800 break-all">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-sm text-neutral-400 italic">Nenhum telefone cadastrado.</p>
            @endif
        </x-card>

        <x-card class="h-full p-6 flex flex-col">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6 shrink-0">E-mails</h3>
            @if(!empty($contact->emails))
                <div class="overflow-y-auto max-h-[300px] pr-2 -mr-2">
                    <div class="divide-y divide-neutral-100">
                        @foreach($contact->emails as $email)
                            @php
                                $label = is_array($email) && !empty($email['label']) ? $email['label'] : 'Principal';
                                $value = is_array($email) ? ($email['value'] ?? '') : $email;
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-6 py-3 first:pt-0 last:pb-0">
                                <span class="text-sm font-medium text-neutral-400 sm:w-24 shrink-0">{{ $label }}</span>
                                <span class="text-[15px] text-neutral-800 break-all">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-sm text-neutral-400 italic">Nenhum e-mail cadastrado.</p>
            @endif
        </x-card>
    </div>


    <x-card class="mb-4 p-6">
        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6">Notas</h3>
        @if($contact->notes)
            <div class="markdown-content text-[15px] text-neutral-700">
                {!! Str::of((string) $contact->notes)->markdown() !!}
            </div>
        @else
            <p class="text-sm text-neutral-400 italic">Nenhuma anotação.</p>
        @endif
    </x-card>

</x-layouts.app>
