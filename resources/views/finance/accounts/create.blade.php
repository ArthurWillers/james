<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Nova Conta</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Nova Conta">
        <x-button color="outline" href="{{ route('financial.accounts.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="create-account-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-account-form" action="{{ route('financial.accounts.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6" x-data="{ type: '{{ old('type', '') }}' }">
            <!-- Coluna da Esquerda: Informações Básicas -->
            <div class="flex flex-col gap-6">
                <x-card class="p-6">
                    <div class="flex flex-col gap-4">
                        <div>
                            <x-form-input name="name" label="Nome da Conta" value="{{ old('name') }}" placeholder="Ex: Dinheiro Físico" />
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
                        <div>
                            <x-form-input label="Saldo Inicial (R$)" name="initial_balance" :numeric="true" placeholder="0,00" value="{{ old('initial_balance') }}" help="Opcional. Se preenchido, criará uma transação na data de hoje ajustando o saldo inicial." />
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
