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

        <x-page-header title="Editar Recorrência" mobileBottom>
            <x-form-actions fallback="{{ route('financial.recurrences.index') }}" form="recurrence-form" />
        </x-page-header>

        @include('finance.recurrences.partials.form', ['recurrence' => $recurrence])

        <x-form-actions fallback="{{ route('financial.recurrences.index') }}" form="recurrence-form" mobile />
    </form>
</x-layouts.financial>
