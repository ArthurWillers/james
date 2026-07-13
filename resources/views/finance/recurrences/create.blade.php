<x-layouts.financial>
    <form action="{{ route('financial.recurrences.store') }}" method="POST" id="recurrence-form" x-data='{
        targetType: "{{ old('financial_credit_card_id') ? 'card' : 'account' }}",
        type: "{{ old('type', 'expense') }}",
        frequency: "{{ old('frequency', 'monthly') }}"
    }'>
        @csrf

        <div class="flex justify-between items-center mb-6">
            <x-breadcrumbs>
                <x-breadcrumbs.item href="{{ route('financial.recurrences.index') }}">Recorrências</x-breadcrumbs.item>
                <x-breadcrumbs.item>Nova Recorrência</x-breadcrumbs.item>
            </x-breadcrumbs>
        </div>

        <x-page-header title="Nova Recorrência">
            <x-back-button fallback="{{ route('financial.recurrences.index') }}" text="Cancelar" />

            <x-button type="submit" form="recurrence-form">
                <x-heroicon-o-check class="size-4" />
                Salvar
            </x-button>
        </x-page-header>

        @include('finance.recurrences.partials.form')
    </form>
</x-layouts.financial>
