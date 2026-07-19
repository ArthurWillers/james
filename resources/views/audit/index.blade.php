<x-layouts.app>
    <x-page-header title="Log de Auditoria" />

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-card>
            <x-table>
                <x-table.header class="grid-cols-[80px_1fr_1.5fr_1.5fr_1fr_40px] hidden sm:grid">
                    <x-table.column>ID</x-table.column>
                    <x-table.column>Ação</x-table.column>
                    <x-table.column>Entidade</x-table.column>
                    <x-table.column>Autor</x-table.column>
                    <x-table.column>Data</x-table.column>
                    <x-table.column></x-table.column>
                </x-table.header>
                <div class="divide-y divide-neutral-100">
                    @forelse($activities as $activity)
                        <x-table.row href="{{ route('audit.show', $activity) }}" class="grid-cols-[80px_1fr_1.5fr_1.5fr_1fr_40px] hidden sm:grid">
                            <x-table.cell class="text-neutral-500 font-mono text-sm">#{{ $activity->id }}</x-table.cell>
                            <x-table.cell class="font-medium text-neutral-900">{{ ucfirst($activity->description) }}</x-table.cell>
                            <x-table.cell class="text-neutral-500">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</x-table.cell>
                            <x-table.cell class="text-neutral-500">{{ $activity->causer_id ? ($activity->causer->name ?? 'Usuário #'.$activity->causer_id) : 'Sistema' }}</x-table.cell>
                            <x-table.cell class="text-neutral-500">{{ formatDateTime($activity->created_at) }}</x-table.cell>
                            <x-table.cell align="right">
                                <x-heroicon-m-chevron-right class="w-5 h-5 text-neutral-400 group-hover/row:text-primary-600 transition-colors" />
                            </x-table.cell>
                            
                            <x-slot:mobile>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-neutral-900">{{ ucfirst($activity->description) }}</div>
                                        <div class="text-sm text-neutral-500 mt-1">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</div>
                                        <div class="text-xs text-neutral-500 mt-1">{{ $activity->causer_id ? ($activity->causer->name ?? 'Usuário #'.$activity->causer_id) : 'Sistema' }}</div>
                                    </div>
                                    <div class="text-right text-sm">
                                        <div class="text-neutral-900">{{ formatDateTime($activity->created_at) }}</div>
                                        <div class="text-neutral-400 text-xs font-mono mt-1">#{{ $activity->id }}</div>
                                    </div>
                                </div>
                            </x-slot:mobile>
                        </x-table.row>
                    @empty
                        <div class="px-6 py-12 text-center text-neutral-500">
                            Nenhum log de auditoria encontrado.
                        </div>
                    @endforelse
                </div>
            </x-table>
                
                @if(method_exists($activities, 'links'))
                    <div class="p-4 border-t border-neutral-200">
                        {{ $activities->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
