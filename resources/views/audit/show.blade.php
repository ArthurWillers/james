<x-layouts.app>
    <x-page-header title="Detalhes da Auditoria #{{ $activity->id }}" :backRoute="url()->previous()" />

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Painel de Informações -->
                <div class="md:col-span-1 space-y-6">
                    <x-card>
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-neutral-900 mb-4">Informações Gerais</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-neutral-500">Ação</p>
                                    <p class="font-medium text-neutral-900">{{ ucfirst($activity->description) }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-neutral-500">Autor (Causer)</p>
                                    <p class="font-medium text-neutral-900">
                                        @if($activity->causer)
                                            {{ $activity->causer->name ?? 'Usuário #'.$activity->causer_id }}
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-neutral-100 px-2 py-1 text-xs font-medium text-neutral-600 ring-1 ring-inset ring-neutral-500/10">Sistema / Rotina Automática</span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-neutral-500">Entidade (Subject)</p>
                                    <p class="font-medium text-neutral-900">
                                        {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-neutral-500">Data do Evento</p>
                                    <p class="font-medium text-neutral-900">{{ formatDateTime($activity->created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Painel de Alterações -->
                <div class="md:col-span-2">
                    <x-card>
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-neutral-900 mb-4">Alterações de Atributos</h3>
                            
                            @php
                                // Regra Spatie v5: Buscar primeiro em attribute_changes, fallback para properties
                                $changes = $activity->attribute_changes ?? $activity->properties;
                                $changesArray = is_object($changes) && method_exists($changes, 'toArray') ? $changes->toArray() : (array) $changes;
                                
                                $old = $changesArray['old'] ?? [];
                                $attributes = $changesArray['attributes'] ?? [];
                                
                                // Converter objetos para array caso existam (ex: casts JSON)
                                $old = is_object($old) ? (array) $old : $old;
                                $attributes = is_object($attributes) ? (array) $attributes : $attributes;
                                
                                $keys = collect(array_keys($old))->merge(array_keys($attributes))->unique();
                            @endphp

                            @if($keys->isEmpty())
                                <div class="bg-neutral-50 border border-neutral-200 rounded-lg p-8 text-center">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-neutral-400 mx-auto mb-3" />
                                    <p class="text-neutral-500">Nenhuma alteração de atributos registrada neste evento.</p>
                                </div>
                            @else
                                <x-table>
                                    <x-table.header class="grid-cols-[1fr_1.5fr_1.5fr] hidden sm:grid">
                                        <x-table.column>Campo</x-table.column>
                                        <x-table.column>Anterior (Old)</x-table.column>
                                        <x-table.column>Atual (New)</x-table.column>
                                    </x-table.header>
                                    <div class="divide-y divide-neutral-100">
                                        @foreach($keys as $key)
                                            <x-table.row class="grid-cols-[1fr_1.5fr_1.5fr] hidden sm:grid">
                                                <x-table.cell class="font-medium text-neutral-900">{{ $key }}</x-table.cell>
                                                <x-table.cell class="text-red-600 font-medium break-all">
                                                    @if(array_key_exists($key, $old))
                                                        {{ is_array($old[$key]) || is_object($old[$key]) ? json_encode($old[$key]) : ($old[$key] ?? 'Nulo') }}
                                                    @else
                                                        <span class="text-neutral-400 font-normal">-</span>
                                                    @endif
                                                </x-table.cell>
                                                <x-table.cell class="text-green-600 font-medium break-all">
                                                    @if(array_key_exists($key, $attributes))
                                                        {{ is_array($attributes[$key]) || is_object($attributes[$key]) ? json_encode($attributes[$key]) : ($attributes[$key] ?? 'Nulo') }}
                                                    @else
                                                        <span class="text-neutral-400 font-normal">-</span>
                                                    @endif
                                                </x-table.cell>
                                                
                                                <x-slot:mobile>
                                                    <div class="font-medium text-neutral-900 mb-2">{{ $key }}</div>
                                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                                        <div class="text-red-600 font-medium break-all">
                                                            <span class="text-neutral-400 font-normal block text-xs mb-0.5">Anterior:</span>
                                                            @if(array_key_exists($key, $old))
                                                                {{ is_array($old[$key]) || is_object($old[$key]) ? json_encode($old[$key]) : ($old[$key] ?? 'Nulo') }}
                                                            @else
                                                                <span class="text-neutral-400 font-normal">-</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-green-600 font-medium break-all">
                                                            <span class="text-neutral-400 font-normal block text-xs mb-0.5">Atual:</span>
                                                            @if(array_key_exists($key, $attributes))
                                                                {{ is_array($attributes[$key]) || is_object($attributes[$key]) ? json_encode($attributes[$key]) : ($attributes[$key] ?? 'Nulo') }}
                                                            @else
                                                                <span class="text-neutral-400 font-normal">-</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </x-slot:mobile>
                                            </x-table.row>
                                        @endforeach
                                    </div>
                                </x-table>
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
