<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.cards.show', $card) }}">{{ $card->name }}</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Cartão" mobileBottom>
        <x-form-actions fallback="{{ route('financial.cards.show', $card) }}" form="edit-card-form" />
    </x-page-header>

    <form id="edit-card-form" action="{{ route('financial.cards.update', $card) }}" method="POST">
        @csrf
        @method('PUT')
        
        @include('finance.cards.partials.form', ['card' => $card])

        <x-form-actions fallback="{{ route('financial.cards.show', $card) }}" form="edit-card-form" mobile />
    </form>
</x-layouts.financial>
