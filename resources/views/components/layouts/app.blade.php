<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased bg-neutral-50 text-neutral-900">

    <x-sidebar>
        <x-nav-link :href="route('dashboard')" :current="request()->routeIs('dashboard')">
            <x-heroicon-o-home class="w-4 h-4" />
            Dashboard
        </x-nav-link>

        <x-nav-link :href="route('contacts.index')" :current="request()->routeIs('contacts.*')">
            <x-heroicon-o-users class="w-4 h-4" />
            Contatos
        </x-nav-link>

        <x-nav-link :href="route('financial.dashboard')" :current="request()->routeIs('financial.*')">
            <x-heroicon-o-banknotes class="w-4 h-4" />
            Finanças
        </x-nav-link>

        <x-nav-link :href="route('settlements.index')" :current="request()->routeIs('settlements.*')">
            <x-heroicon-o-scale class="w-4 h-4" />
            Acertos
        </x-nav-link>
    </x-sidebar>

    <main class="lg:ml-64 p-6 lg:pt-8 lg:px-8 lg:pb-0">
        {{ $slot }}
    </main>

    <x-toast />
</body>

</html>
