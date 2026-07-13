<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Nova Conta</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Nova Conta" mobileBottom>
        <x-form-actions fallback="{{ route('financial.accounts.index') }}" form="create-account-form" />
    </x-page-header>

    <form id="create-account-form" action="{{ route('financial.accounts.store') }}" method="POST">
        @csrf
        
        @include('finance.accounts.partials.form')

        <x-form-actions fallback="{{ route('financial.accounts.index') }}" form="create-account-form" mobile />
    </form>
</x-layouts.financial>
