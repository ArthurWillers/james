<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Novo Cartão</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Cartão">
        <x-back-button fallback="{{ route('financial.cards.index') }}" text="Cancelar" />

        <x-button type="submit" form="create-card-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-card-form" action="{{ route('financial.cards.store') }}" method="POST">
        @csrf
        
        @include('finance.cards.partials.form')
    </form>
</x-layouts.financial>
