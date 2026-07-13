<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.tags.index') }}">Tags</x-breadcrumbs.item>
            <x-breadcrumbs.item>Nova Tag</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Nova Tag" mobileBottom>
        <x-form-actions fallback="{{ route('financial.tags.index') }}" form="create-tag-form" />
    </x-page-header>

    <form id="create-tag-form" action="{{ route('financial.tags.store') }}" method="POST">
        @csrf
        <div class="mt-6">
            @include('finance.tags.partials.form')

            <x-form-actions fallback="{{ route('financial.tags.index') }}" form="create-tag-form" mobile />
        </div>
    </form>
</x-layouts.financial>
