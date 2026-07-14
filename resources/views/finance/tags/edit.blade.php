<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.tags.index') }}">Tags</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar Tag</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Tag" mobileBottom>
        <x-form-actions fallback="{{ route('financial.tags.index') }}" form="edit-tag-form" submitText="Atualizar" />
    </x-page-header>

    <form id="edit-tag-form" action="{{ route('financial.tags.update', $financialTag) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mt-6">
            @include('finance.tags.partials.form', ['tag' => $financialTag])

            <x-form-actions fallback="{{ route('financial.tags.index') }}" form="edit-tag-form" submitText="Atualizar" mobile />
        </div>
    </form>
</x-layouts.financial>
