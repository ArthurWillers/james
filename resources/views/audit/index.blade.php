<x-layouts.app>
    <x-page-header title="Logs do Sistema" />

    <div class="mt-6">
        <x-table>
            <x-table.header class="grid-cols-[200px_1fr_1.5fr_120px] hidden sm:grid">
                <x-table.column>DATA/HORA</x-table.column>
                <x-table.column>USUÁRIO</x-table.column>
                <x-table.column>MÓDULO</x-table.column>
                <x-table.column>AÇÃO</x-table.column>
            </x-table.header>
            <div class="divide-y divide-neutral-100">
                @forelse($activities as $activity)
                    <x-table.row href="{{ route('audit.show', $activity) }}" class="grid-cols-[200px_1fr_1.5fr_120px] hidden sm:grid items-center">
                        <x-table.cell class="text-neutral-600 text-sm font-medium">{{ formatDateTime($activity->created_at) }}</x-table.cell>
                        <x-table.cell>
                            @if($activity->causer)
                                <div class="flex items-center gap-2">
                                    <x-avatar :model="$activity->causer" size="sm" />
                                    <span class="font-medium text-neutral-900">{{ $activity->causer->name }}</span>
                                </div>
                            @else
                                <span class="text-neutral-500 font-medium">Sistema</span>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="font-medium text-neutral-700">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</x-table.cell>
                        <x-table.cell>
                            @php
                                $actionColors = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'restored' => 'yellow'];
                                $color = $actionColors[$activity->description] ?? 'neutral';
                            @endphp
                            <x-badge :color="$color" size="sm">{{ ucfirst($activity->description) }}</x-badge>
                        </x-table.cell>
                        
                        <x-slot:mobile>
                            <div class="flex justify-between items-start">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $actionColors = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'restored' => 'yellow'];
                                            $color = $actionColors[$activity->description] ?? 'neutral';
                                        @endphp
                                        <x-badge :color="$color" size="sm">{{ ucfirst($activity->description) }}</x-badge>
                                        <span class="text-sm font-medium text-neutral-900">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                    </div>
                                    <div class="text-xs text-neutral-500">
                                        @if($activity->causer)
                                            {{ $activity->causer->name }}
                                        @else
                                            Sistema
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right text-sm">
                                    <div class="text-neutral-900 font-medium">{{ formatDateTime($activity->created_at) }}</div>
                                </div>
                            </div>
                        </x-slot:mobile>
                    </x-table.row>
                @empty
                    <x-empty-state 
                        icon="heroicon-o-document-text" 
                        title="Nenhum log encontrado" 
                        description="Não há registros de auditoria no sistema no momento." 
                    />
                @endforelse
            </div>
        </x-table>
        
        @if(method_exists($activities, 'links'))
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
