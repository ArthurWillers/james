<x-layouts.app>
    <x-module-navbar>
        <x-module-nav-link :href="route('dashboard')" :current="request()->routeIs('dashboard')">Dashboard</x-module-nav-link>
        <x-module-nav-link :href="route('analytics')" :current="request()->routeIs('analytics')">Analytics</x-module-nav-link>
        <x-module-nav-link :href="route('reports')" :current="request()->routeIs('reports')">Reports</x-module-nav-link>
    </x-module-navbar>

    <div class="py-4">
        <h1 class="text-2xl font-semibold text-neutral-900">Bem-vindo ao Dashboard</h1>
        <p class="mt-2 text-neutral-600">Este é um exemplo de como a navbar fica posicionada no topo da página, logo abaixo do header (se houver).</p>
    </div>
</x-layouts.app>
