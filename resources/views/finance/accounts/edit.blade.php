<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas Financeiras</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.accounts.show', $financialAccount) }}">{{ $financialAccount->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Conta">
        <x-button color="outline" href="{{ route('financial.accounts.show', $financialAccount) }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="edit-account-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="edit-account-form" action="{{ route('financial.accounts.update', $financialAccount) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6" x-data="{ type: '{{ old('type', $financialAccount->type->value) }}' }">
            <!-- Coluna da Esquerda: Informações Básicas -->
            <div class="flex flex-col gap-6">
                <x-card class="p-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                        <x-ui.avatar :icon="$financialAccount->type->icon()" size="2xl" />

                        <div class="flex-1 w-full flex flex-col gap-4">
                            <div>
                                <x-form-input name="name" label="Nome da Conta" value="{{ old('name', $financialAccount->name) }}" placeholder="Ex: Dinheiro Físico" />
                            </div>
                            <div>
                                <x-form-select name="type" label="Tipo de Conta" x-model="type" @change="$dispatch('account-type-changed', type)">
                                    <option value="" disabled>Selecione um tipo...</option>
                                    @foreach($types as $enum)
                                        <option value="{{ $enum->value }}">
                                            {{ $enum->label() }}
                                        </option>
                                    @endforeach
                                </x-form-select>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Coluna da Direita: Chaves Pix -->
            <div class="flex flex-col gap-6 transition-all duration-300" :class="type !== 'checking' ? 'opacity-40 grayscale pointer-events-none' : ''">
                <x-form-key-value-repeater 
                    name="pix_keys" 
                    title="Chaves Pix" 
                    :items="$pixKeys" 
                    value-placeholder="Chave Pix" 
                    empty-message="Nenhuma chave Pix adicionada." 
                    @account-type-changed.window="if ($event.detail !== 'checking') items = []"
                />
            </div>
        </div>
    </form>
</x-layouts.financial>
