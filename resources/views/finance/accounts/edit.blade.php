<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas Financeiras</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.accounts.show', $financialAccount) }}">{{ $financialAccount->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Conta">
        <x-ui.back-button fallback="{{ route('financial.accounts.show', $financialAccount) }}" text="Cancelar" />

        <x-button type="submit" form="edit-account-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="edit-account-form" action="{{ route('financial.accounts.update', $financialAccount) }}" method="POST">
        @csrf
        @method('PUT')

        @include('finance.accounts.partials.form', ['account' => $financialAccount])
    </form>
</x-layouts.financial>
