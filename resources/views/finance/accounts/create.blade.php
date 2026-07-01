<x-layouts.financial>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('financial.accounts.index') }}">Contas</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Nova Conta</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Nova Conta">
        <x-button color="outline" href="{{ route('financial.accounts.index') }}" class="bg-white">
            <x-heroicon-o-arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="create-account-form">
            <x-heroicon-o-check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-account-form" action="{{ route('financial.accounts.store') }}" method="POST">
        @csrf
        
        @include('finance.accounts.partials.form')
    </form>
</x-layouts.financial>
