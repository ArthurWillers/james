<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.contact.show', $contact) }}">{{ $contact->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Novo Lançamento</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Lançamento">
        <x-button color="outline" href="{{ route('settlements.contact.show', $contact) }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>
    </x-page-header>

    <div class="mt-6 max-w-2xl bg-white rounded-xl border border-neutral-200 p-6 shadow-sm">
        <form action="{{ route('settlements.store', $contact) }}" method="POST">
            @csrf
            @include('settlements.partials.form')
        </form>
    </div>
</x-layouts.app>
