<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas Financeiras</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('financial.accounts.show', $financialAccount) }}">{{ $financialAccount->name }}</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Conta" mobileBottom>
        <x-form-actions fallback="{{ route('financial.accounts.show', $financialAccount) }}" form="edit-account-form" />
    </x-page-header>

    <form id="edit-account-form" action="{{ route('financial.accounts.update', $financialAccount) }}" method="POST">
        @csrf
        @method('PUT')

        @include('finance.accounts.partials.form', ['account' => $financialAccount])

        <x-form-actions fallback="{{ route('financial.accounts.show', $financialAccount) }}" form="edit-account-form" mobile />
    </form>
</x-layouts.financial>
