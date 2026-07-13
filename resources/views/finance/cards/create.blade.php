<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Novo Cartão</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Cartão" mobileBottom>
        <x-form-actions fallback="{{ route('financial.cards.index') }}" form="create-card-form" />
    </x-page-header>

    <form id="create-card-form" action="{{ route('financial.cards.store') }}" method="POST">
        @csrf
        
        @include('finance.cards.partials.form')

        <x-form-actions fallback="{{ route('financial.cards.index') }}" form="create-card-form" mobile />
    </form>
</x-layouts.financial>
