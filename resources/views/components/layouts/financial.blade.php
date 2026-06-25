<x-layouts.app>
    <x-nav.module-navbar scrollable>
        <x-nav.module-nav-link :href="route('financial.dashboard')" :current="request()->routeIs('financial.dashboard')">
            Dashboard
        </x-nav.module-nav-link>
        <x-nav.module-nav-link href="#" :current="request()->routeIs('financial.transactions.*')">
            Transações
        </x-nav.module-nav-link>
        <x-nav.module-nav-link href="#" :current="request()->routeIs('financial.recurrences.*')">
            Recorrências
        </x-nav.module-nav-link>
        <x-nav.module-nav-link href="#" :current="request()->routeIs('financial.reports.*')">
            Relatórios
        </x-nav.module-nav-link>

        <!-- Configurações e Cadastros Base -->
        <x-nav.module-nav-link :href="route('financial.accounts.index')" :current="request()->routeIs('financial.accounts.*')">
            Contas
        </x-nav.module-nav-link>
        <x-nav.module-nav-link :href="route('financial.cards.index')" :current="request()->routeIs('financial.cards.*')">
            Cartões
        </x-nav.module-nav-link>
        <x-nav.module-nav-link :href="route('financial.tags.index')" :current="request()->routeIs('financial.tags.*')">
            Tags
        </x-nav.module-nav-link>
    </x-nav.module-navbar>

    <div class="mt-6">
        {{ $slot }}
    </div>
</x-layouts.app>
