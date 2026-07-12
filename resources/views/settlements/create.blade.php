<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.contact.show', $contact) }}">{{ $contact->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Novo Lançamento</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Lançamento">
        <x-button color="outline" href="{{ route('settlements.contact.show', $contact) }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>
    </x-page-header>

    <div class="mt-6">
        <form action="{{ route('settlements.store', $contact) }}" method="POST" id="settlement-form" x-data="{
            type: '{{ old('type', 'they_owe') }}',
            createTransaction: {{ old('create_transaction', 'true') == '1' || old('create_transaction', 'true') === 'true' ? 'true' : 'false' }},
            targetType: '{{ old('targetType', 'account') }}',
            description: '{{ old('description', '') }}',
            init() {
                this.$watch('type', (value) => {
                    if (!this.description || this.description === 'Pagamento recebido' || this.description === 'Pagamento realizado') {
                        if (value === 'they_paid') {
                            this.description = 'Pagamento recebido';
                        } else if (value === 'i_paid') {
                            this.description = 'Pagamento realizado';
                        }
                    }
                });
            }
        }">
            @csrf
            
            @include('settlements.partials.form')

            <div class="flex items-center justify-end gap-3 mt-6">
                <x-button type="button" color="outline" href="{{ route('settlements.contact.show', $contact) }}" class="bg-white">
                    Cancelar
                </x-button>
                <x-button type="submit" form="settlement-form" class="bg-neutral-900 hover:bg-black text-white">
                    Salvar Lançamento
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
