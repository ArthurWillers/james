@php
    $unreadNotifications = auth()->user()->unreadNotifications->take(10);
    $unreadCount = auth()->user()->unreadNotifications->count();
@endphp

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button @click="open = !open"
        class="relative flex items-center justify-center w-9 h-9 rounded-lg text-neutral-500 hover:bg-neutral-800/5 hover:text-neutral-800 cursor-pointer"
        aria-label="Notificações">
        <x-heroicon-o-bell class="w-5 h-5" />
        @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-xxs font-bold leading-none">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition x-cloak
        class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 lg:bottom-auto lg:top-full lg:mt-2 lg:left-auto lg:translate-x-0 lg:right-0 z-50 w-80 rounded-lg border border-neutral-300 bg-white shadow-lg overflow-hidden">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200">
            <span class="text-sm font-semibold text-neutral-800">Notificações</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                    @csrf
                    <button type="submit" class="text-xs text-neutral-500 hover:text-neutral-800 cursor-pointer">
                        Marcar todas como lidas
                    </button>
                </form>
            @endif
        </div>

        {{-- Lista --}}
        <div class="divide-y divide-neutral-100 max-h-80 overflow-y-auto">
            @forelse ($unreadNotifications as $notification)
                <form method="POST" action="{{ route('notifications.markAsRead', $notification) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="w-full text-left px-4 py-3 hover:bg-neutral-50 transition-colors cursor-pointer block">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex-shrink-0 w-2 h-2 rounded-full bg-blue-500"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 truncate">{{ $notification->data['title'] }}</p>
                                <p class="text-xs text-neutral-500 mt-0.5 line-clamp-2">{{ $notification->data['message'] }}</p>
                                <p class="text-xxs text-neutral-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </button>
                </form>
            @empty
                <div class="px-4 py-8 text-center">
                    <x-heroicon-o-bell-slash class="w-8 h-8 text-neutral-300 mx-auto mb-2" />
                    <p class="text-sm text-neutral-500">Sem notificações pendentes</p>
                </div>
            @endforelse
        </div>

        {{-- Rodapé --}}
        <div class="px-4 py-3 border-t border-neutral-200 text-center">
            <a href="{{ route('notifications.index') }}" @click="open = false"
                class="text-xs text-neutral-500 hover:text-neutral-800">
                Ver todas as notificações
            </a>
        </div>
    </div>
</div>
