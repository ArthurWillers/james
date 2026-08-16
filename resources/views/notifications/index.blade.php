<x-layouts.app>
    <x-page-header title="Notificações">
        @if ($notifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                @csrf
                <x-button type="submit" color="secondary">
                    <x-heroicon-o-check-circle class="size-5!" />
                    Marcar todas como lidas
                </x-button>
            </form>
        @endif
    </x-page-header>

    <x-table>
        <x-table.header class="grid-cols-[1fr_220px] hidden sm:grid">
            <x-table.column>NOTIFICAÇÃO</x-table.column>
            <x-table.column>DATA</x-table.column>
        </x-table.header>

        <div class="divide-y divide-neutral-100">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $actionUrl = $notification->data['action_url'] ?? null;
                @endphp

                <x-table.row class="grid-cols-[1fr_220px] hidden sm:grid items-center {{ $isUnread ? 'bg-blue-50/50' : '' }}">
                    <x-table.cell>
                        <div class="flex items-start gap-3">
                            @if ($isUnread)
                                <div class="mt-2 flex-shrink-0 w-2 h-2 rounded-full bg-blue-500"></div>
                            @else
                                <div class="mt-2 flex-shrink-0 w-2 h-2 rounded-full bg-neutral-200"></div>
                            @endif
                            <div>
                                <p class="font-medium text-neutral-900 {{ $isUnread ? '' : 'text-neutral-600' }}">
                                    {{ $notification->data['title'] }}
                                </p>
                                <p class="text-sm text-neutral-500 mt-0.5">{{ $notification->data['message'] }}</p>
                                @if ($actionUrl)
                                    <a href="{{ $actionUrl }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                        Ver detalhes →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </x-table.cell>
                    <x-table.cell class="text-sm text-neutral-500">
                        <div class="flex items-center justify-between gap-2">
                            <span>{{ formatDateTime($notification->created_at) }}</span>
                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.markAsRead', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="text-xs text-neutral-400 hover:text-neutral-700 cursor-pointer whitespace-nowrap">
                                        Marcar como lida
                                    </button>
                                </form>
                            @endif
                        </div>
                    </x-table.cell>

                    <x-slot:mobile>
                        <div class="flex items-start gap-3">
                            @if ($isUnread)
                                <div class="mt-1.5 flex-shrink-0 w-2 h-2 rounded-full bg-blue-500"></div>
                            @else
                                <div class="mt-1.5 flex-shrink-0 w-2 h-2 rounded-full bg-neutral-200"></div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-medium text-neutral-900 truncate">
                                        {{ $notification->data['title'] }}
                                    </p>
                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('notifications.markAsRead', $notification) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xxs text-neutral-400 hover:text-neutral-700 cursor-pointer shrink-0">
                                                Lida
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <p class="text-xs text-neutral-500 mt-0.5">{{ $notification->data['message'] }}</p>
                                <p class="text-xxs text-neutral-400 mt-1">{{ formatDateTime($notification->created_at) }}</p>
                            </div>
                        </div>
                    </x-slot:mobile>
                </x-table.row>
            @empty
                <x-empty-state
                    icon="heroicon-o-bell"
                    title="Nenhuma notificação"
                    description="Você não possui notificações no momento."
                />
            @endforelse
        </div>
    </x-table>

    @if ($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</x-layouts.app>
