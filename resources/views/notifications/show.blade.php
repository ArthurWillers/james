<x-layouts.app>
    <x-page-header title="Detalhes da Notificação">
        <div class="flex items-center gap-2">
            <x-back-button fallback="{{ route('notifications.index') }}" />

            <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Deseja realmente excluir esta notificação?');">
                @csrf
                @method('DELETE')
                <x-button type="submit" color="danger">
                    <x-heroicon-o-trash class="size-5!" />
                    Excluir
                </x-button>
            </form>
        </div>
    </x-page-header>

    <div class="mt-6">
        @php
            $isUnread = is_null($notification->read_at);
            $title = $notification->data['title'] ?? 'Sem título';
            $message = $notification->data['message'] ?? '';
            $actionUrl = $notification->data['action_url'] ?? null;
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Painel de Informações Laterais -->
            <div class="xl:col-span-1 space-y-6">
                <x-card class="!p-0 overflow-hidden">
                    <div class="divide-y divide-neutral-100">
                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">STATUS</p>
                            <div>
                                @if($isUnread)
                                    <x-badge color="blue" size="sm">Não lida</x-badge>
                                @else
                                    <x-badge color="neutral" size="sm">Lida</x-badge>
                                @endif
                            </div>
                        </div>

                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">DATA / HORA DO ENVIO</p>
                            <div class="text-sm font-medium text-neutral-900">{{ formatDateTime($notification->created_at) }}</div>
                        </div>

                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">LIDA EM</p>
                            <div class="text-sm font-medium text-neutral-900">
                                {{ $notification->read_at ? formatDateTime($notification->read_at) : 'Não lida' }}
                            </div>
                        </div>

                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">TIPO DE NOTIFICAÇÃO</p>
                            <div class="text-sm font-medium text-neutral-700">
                                {{ class_basename($notification->type) }}
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Painel Principal de Conteúdo -->
            <div class="xl:col-span-2 space-y-6">
                <x-card class="space-y-4">
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">MENSAGEM</p>
                        <h3 class="text-lg font-semibold text-neutral-900">{{ $title }}</h3>
                        @if($message)
                            <p class="text-sm text-neutral-700 mt-2 leading-relaxed whitespace-pre-line">{{ $message }}</p>
                        @endif
                    </div>

                    @if($actionUrl)
                        <div class="pt-4 border-t border-neutral-100 flex items-center justify-between">
                            <span class="text-xs text-neutral-500">Link associado à notificação:</span>
                            <x-button :href="$actionUrl" target="_blank">
                                <x-heroicon-m-arrow-top-right-on-square class="size-4!" />
                                Acessar link de ação
                            </x-button>
                        </div>
                    @endif
                </x-card>

                <!-- Payload de Dados -->
                <x-card>
                    <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-3">DADOS DO EVENTO</p>
                    <div class="bg-neutral-50 rounded-lg p-4 font-mono text-xs text-neutral-700 overflow-x-auto border border-neutral-200">
                        <pre>{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
