<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.contact.show', $contact) }}">{{ $contact->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar Lançamento</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Lançamento">
        <x-back-button fallback="{{ route('settlements.contact.show', $contact) }}" text="Cancelar" />

        <x-button type="submit" form="settlement-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <div class="mt-6">
        <form action="{{ route('settlements.update', $settlement) }}" method="POST" enctype="multipart/form-data" id="settlement-form" x-data="{
            type: '{{ old('type', $settlement->type->value) }}',
            createTransaction: {{ old('create_transaction', $settlement->financial_transaction_id ? 'true' : 'false') == '1' || old('create_transaction', $settlement->financial_transaction_id ? 'true' : 'false') === 'true' ? 'true' : 'false' }},
            targetType: '{{ old('targetType', optional($settlement->financialTransaction ?? null)->invoice ? 'card' : 'account') }}',
            description: '{{ old('description', $settlement->description) }}',
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
            @method('PUT')
            
            @include('settlements.partials.form', ['settlement' => $settlement])
        </form>
    </div>
</x-layouts.app>
