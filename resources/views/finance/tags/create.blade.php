<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.tags.index') }}">Tags</x-breadcrumbs.item>
            <x-breadcrumbs.item>Nova Tag</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Nova Tag">
        <x-button color="outline" href="{{ route('financial.tags.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="create-tag-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-tag-form" action="{{ route('financial.tags.store') }}" method="POST">
        @csrf
        <div class="mt-6">
            @include('finance.tags.partials.form')
        </div>
    </form>
</x-layouts.financial>
