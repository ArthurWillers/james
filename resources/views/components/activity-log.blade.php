@if($activities->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'hidden lg:block']) }}>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-neutral-900">Histórico de Atividades</h2>
            <a href="{{ route('audit.index', ['module' => get_class($model), 'subject_id' => $model->id]) }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors">
                Ver histórico completo &rarr;
            </a>
        </div>

        <x-table class="overflow-hidden">
            <x-table.header class="grid-cols-[200px_1fr_120px] grid">
                <x-table.column>DATA/HORA</x-table.column>
                <x-table.column>CAUSADOR</x-table.column>
                <x-table.column>AÇÃO</x-table.column>
            </x-table.header>
            <div class="divide-y divide-neutral-100">
                @foreach($activities as $activity)
                    <x-table.row :href="route('audit.show', $activity)" class="grid-cols-[200px_1fr_120px] grid items-center hover:bg-neutral-50/50 transition-colors">
                        <x-table.cell class="text-neutral-600 text-sm font-medium">{{ formatDateTime($activity->created_at) }}</x-table.cell>
                        <x-table.cell>
                            @if($activity->causer)
                                <div class="flex items-center gap-2">
                                    <x-avatar :model="$activity->causer" size="sm" />
                                    <span class="font-medium text-neutral-900">{{ $activity->causer->name }}</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-neutral-500 font-medium">
                                    <x-heroicon-o-cog-8-tooth class="size-5" />
                                    <span>Sistema / Rotina Automática</span>
                                </div>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            @php
                                $actionTranslations = ['created' => 'Criado', 'updated' => 'Atualizado', 'deleted' => 'Excluído', 'restored' => 'Restaurado', 'forceDeleted' => 'Excluído Permanentemente'];
                                $actionName = $actionTranslations[$activity->description] ?? ucfirst($activity->description);
                                $actionColors = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'restored' => 'yellow', 'forceDeleted' => 'rose'];
                                $color = $actionColors[$activity->description] ?? 'neutral';
                            @endphp
                            <x-badge :color="$color" size="sm">{{ $actionName }}</x-badge>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </div>
        </x-table>
    </div>
@endif
