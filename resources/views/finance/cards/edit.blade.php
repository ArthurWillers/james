<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.cards.show', $card) }}">{{ $card->name }}</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Cartão">
        <x-button color="outline" href="{{ route('financial.cards.show', $card) }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="edit-card-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="edit-card-form" action="{{ route('financial.cards.update', $card) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6">
            <div class="flex flex-col gap-6">
                <x-card class="p-6">
                    <div class="flex flex-col gap-4">
                        <div>
                            <x-form-input name="name" label="Nome do Cartão" value="{{ old('name', $card->name) }}" placeholder="Ex: Nubank, Itaú" />
                        </div>
                        <div>
                            <x-form-select name="financial_account_id" label="Conta para Pagamento">
                                <option value="" disabled>Selecione uma conta...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ old('financial_account_id', $card->financial_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </x-form-select>
                        </div>
                        <div>
                            <x-form-input name="credit_limit" type="number" step="0.01" label="Limite (Opcional)" value="{{ old('credit_limit', $card->credit_limit ? (float) $card->credit_limit : '') }}" placeholder="Ex: 5000.00" />
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="flex flex-col gap-6">
                <x-card class="p-6">
                    <h3 class="font-medium text-neutral-900 mb-4">Datas e Vencimento</h3>
                    <div class="flex flex-col gap-4">
                        <div>
                            <x-form-input name="closing_day" type="number" min="1" max="31" label="Dia de Fechamento" value="{{ old('closing_day', $card->closing_day) }}" />
                        </div>
                        <div>
                            <x-form-input name="due_day" type="number" min="1" max="31" label="Dia de Vencimento" value="{{ old('due_day', $card->due_day) }}" />
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</x-layouts.financial>
