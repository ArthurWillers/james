<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.contact.show', $contact) }}">{{ $contact->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar Lançamento</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Lançamento">
        <div class="flex items-center gap-2">
            <x-button color="outline" href="{{ route('settlements.contact.show', $contact) }}" class="bg-white">
                <x-heroicon-o-arrow-left class="size-4" />
                Cancelar
            </x-button>
            <x-button color="danger" onclick="alert('Exclusão simulada com sucesso! (Fase 2)')">
                <x-heroicon-o-trash class="size-4" />
                Excluir
            </x-button>
        </div>
    </x-page-header>

    <div class="mt-6 max-w-2xl bg-white rounded-xl border border-neutral-200 p-6 shadow-sm">
        <form action="{{ route('settlements.update', $settlement) }}" method="POST">
            @csrf
            @method('PUT')
            @include('settlements.partials.form', ['settlement' => $settlement])
        </form>
    </div>
</x-layouts.app>
