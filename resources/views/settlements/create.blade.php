<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.contact.show', $contact) }}">{{ $contact->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Novo Lançamento</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Lançamento" mobileBottom>
        <x-form-actions fallback="{{ route('settlements.contact.show', $contact) }}" form="settlement-form" />
    </x-page-header>

    <div class="mt-6">
        <form action="{{ route('settlements.store', $contact) }}" method="POST" enctype="multipart/form-data" id="settlement-form" x-data="{
            type: '{{ old('type', isset($settlement) ? $settlement->type->value : 'they_owe') }}',
            createTransaction: {{ old('create_transaction', isset($isSettling) && $isSettling ? 'true' : 'false') == '1' || old('create_transaction', isset($isSettling) && $isSettling ? 'true' : 'false') === 'true' ? 'true' : 'false' }},
            targetType: '{{ old('targetType', 'account') }}',
            description: '{{ old('description', isset($settlement) ? $settlement->description : '') }}',
            init() {
                this.$watch('type', (value) => {
                    if (!this.description || this.description === 'Pagamento recebido' || this.description === 'Pagamento realizado') {
                        if (value === 'they_paid') {
                            this.description = 'Pagamento recebido';
                        } else if (value === 'i_paid') {
                            this.description = 'Pagamento realizado';
                        } else {
                            this.description = '';
                        }
                    }
                });
            }

        }">
            @csrf
            
            @include('settlements.partials.form', ['settlement' => $settlement ?? null])
        </form>
    </div>

    <x-form-actions fallback="{{ route('settlements.contact.show', $contact) }}" form="settlement-form" mobile />
</x-layouts.app>
