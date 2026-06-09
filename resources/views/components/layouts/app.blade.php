<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased bg-neutral-50 text-neutral-900">

    <div x-data="{ open: false }">
        <aside
            class="fixed top-0 left-0 h-screen w-64 border-e bg-neutral-100 border-neutral-300 p-4 flex flex-col gap-4 z-40 transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="{ '-translate-x-full': !open }" x-cloak>
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}">
                    <x-app-logo />
                </a>

                <button @click="open = !open"
                    class="ms-auto lg:hidden cursor-pointer p-1 rounded-md hover:bg-neutral-200">
                    <x-icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>

            <nav class="flex flex-col overflow-visible min-h-auto space-y-[2px]">
                <x-nav-link :href="route('dashboard')" :current="request()->routeIs('dashboard')">
                    <x-icon name="home" class="w-4 h-4" />
                    Dashboard
                </x-nav-link>
            </nav>

            <x-dropdown position="top" class="mt-auto hidden lg:block" accent contentClass="w-full">
                <x-slot name="trigger">
                    <button class="w-full flex items-center rounded-lg p-1 hover:bg-neutral-800/5 group cursor-pointer">
                        <div class="shrink-0 border rounded-md p-1 font-medium bg-neutral-200 border-neutral-300">
                            {{ auth()->user()->initials() }}
                        </div>
                        <span
                            class="mx-2 text-sm font-medium truncate text-neutral-800/80 group-hover:text-neutral-800">{{ auth()->user()->name }}</span>
                        <div class="ms-auto text-neutral-800/80 group-hover:text-neutral-800">
                            <x-icon name="chevron-up-down" class="w-6 h-6" />
                        </div>
                </x-slot>

                <x-slot name="content">
                    <div class="flex items-center gap-2 p-2">
                        <div class="shrink-0 border rounded-md p-1 font-medium bg-neutral-200 border-neutral-300">
                            {{ auth()->user()->initials() }}
                        </div>
                        <div class="truncate">
                            <div class="text-sm font-semibold text-neutral-800 truncate">
                                {{ auth()->user()->name }}</div>
                            <div class="text-xs text-neutral-500 truncate">
                                {{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <hr class="my-1 border-neutral-300">

                    <a href="{{ route('settings') }}" @click="open = !open"
                        class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-200">
                        <x-icon name="cog-6-tooth" class="w-5 h-5" />
                        Configurações
                    </a>
                    <form method="POST" id="logout" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" @click="open = !open"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm text-red-500 hover:bg-neutral-200 cursor-pointer">
                            <x-icon name="arrow-right-start-on-rectangle-solid" class="w-5 h-5" />
                            Sair
                        </button>
                    </form>

                </x-slot>
            </x-dropdown>

        </aside>

        <div x-show="open" @click="open = false" class="fixed inset-0 bg-black/10 z-30 lg:hidden" x-cloak></div>

        <header class="flex items-center px-6 w-full min-h-14 lg:hidden">
            <button @click="open = !open" class="cursor-pointer rounded-lg p-1 hover:bg-neutral-200">
                <x-icon name="bars-3" class="w-6 h-6" />
            </button>

            <x-dropdown position="bottom-end" class="ms-auto" accent contentClass="w-60">
                <x-slot name="trigger">
                    <button
                        class="w-full flex items-center rounded-lg p-1 hover:bg-neutral-800/5 group cursor-pointer gap-2">
                        <div class="shrink-0 border rounded-md p-1 font-medium bg-neutral-200 border-neutral-300">
                            {{ auth()->user()->initials() }}
                        </div>

                        <div class="ms-auto text-neutral-800/80 group-hover:text-neutral-800">
                            <x-icon name="chevron-down-solid" class="w-4 h-4" />
                        </div>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="flex items-center gap-2 p-2">
                        <div class="shrink-0 border rounded-md p-1 font-medium bg-neutral-200 border-neutral-300">
                            {{ auth()->user()->initials() }}
                        </div>
                        <div class="truncate">
                            <div class="text-sm font-semibold text-neutral-800 truncate">
                                {{ auth()->user()->name }}</div>
                            <div class="text-xs text-neutral-500 truncate">
                                {{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <hr class="my-1 border-neutral-300">

                    <a href="{{ route('settings') }}" @click="open = !open"
                        class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-200">
                        <x-icon name="cog-6-tooth" class="w-5 h-5" />
                        Configurações
                    </a>

                    <button type="submit" form="logout" @click="open = !open"
                        class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm text-red-500 hover:bg-neutral-200 cursor-pointer">
                        <x-icon name="arrow-right-start-on-rectangle-solid" class="w-5 h-5" />
                        Sair
                    </button>

                </x-slot>
            </x-dropdown>
        </header>
    </div>
    <main class="lg:ml-64 p-6 lg:pt-8 lg:px-8 lg:pb-0">
        {{ $slot }}
    </main>

    <x-toast />
</body>

</html>
