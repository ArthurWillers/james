@props(['headerBg' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased bg-neutral-50 text-neutral-900">
    <x-sidebar :headerBg="$headerBg">
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

        <x-nav-link :href="route('notifications.index')" :current="request()->routeIs('notifications.*')">
            <x-heroicon-o-bell class="w-4 h-4" />
            <span>Notificações</span>
            @if($unreadNotificationCount > 0)
                <span class="ms-auto flex min-w-5 items-center justify-center h-5 px-1.5 rounded-full bg-red-500 text-white text-xxs font-bold leading-none">
                    {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                </span>
            @endif
        </x-nav-link>

        <x-nav-link :href="route('audit.index')" :current="request()->routeIs('audit.*')" class="hidden! lg:flex!">
            <x-heroicon-o-document-text class="w-4 h-4" />
            Logs do Sistema
        </x-nav-link>
    </x-sidebar>

    <main class="lg:ml-64 p-3 pb-12 sm:p-6 lg:pt-8 lg:px-8">
        {{ $slot }}
    </main>

    <x-toast />
</body>

</html>
