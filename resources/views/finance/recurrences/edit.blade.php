<x-layouts.financial>
    <form action="{{ route('financial.recurrences.update', $recurrence) }}" method="POST" id="recurrence-form" x-data='{
        targetType: "{{ old('financial_credit_card_id', $recurrence->financial_credit_card_id) ? 'card' : 'account' }}",
        type: "{{ old('type', $recurrence->type) }}",
        frequency: "{{ old('frequency', $recurrence->frequency) }}"
    }'>
        @csrf
        @method('PUT')

        <div class="flex justify-between items-center mb-6">
            <x-breadcrumbs>
                <x-breadcrumbs.item href="{{ route('financial.recurrences.index') }}">Recorrências</x-breadcrumbs.item>
                <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
            </x-breadcrumbs>
        </div>

        <!-- Header with Actions -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-neutral-900">Editar Recorrência</h1>
            <div class="flex items-center gap-3">
                <x-ui.back-button fallback="{{ route('financial.recurrences.index') }}" text="Cancelar" />
                <x-button type="submit" form="recurrence-form" class="bg-neutral-900 hover:bg-black text-white">Salvar</x-button>
            </div>
        </div>

        @include('finance.recurrences.partials.form', ['recurrence' => $recurrence])
    </form>
</x-layouts.financial>
