<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.cards.index') }}">Cartões</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.cards.show', $card) }}">{{ $card->name }}</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Cartão">
        <x-ui.back-button fallback="{{ route('financial.cards.show', $card) }}" text="Cancelar" />

        <x-button type="submit" form="edit-card-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="edit-card-form" action="{{ route('financial.cards.update', $card) }}" method="POST">
        @csrf
        @method('PUT')
        
        @include('finance.cards.partials.form', ['card' => $card])
    </form>
</x-layouts.financial>
