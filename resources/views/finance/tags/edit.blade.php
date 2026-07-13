<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.tags.index') }}">Tags</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar Tag</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Tag">
        <x-back-button fallback="{{ route('financial.tags.index') }}" text="Cancelar" />

        <x-button type="submit" form="edit-tag-form">
            <x-heroicon-o-check class="size-4" />
            Atualizar
        </x-button>
    </x-page-header>

    <form id="edit-tag-form" action="{{ route('financial.tags.update', $financialTag) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mt-6">
            @include('finance.tags.partials.form', ['tag' => $financialTag])
        </div>
    </form>
</x-layouts.financial>
