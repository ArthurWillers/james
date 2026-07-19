<x-layouts.app>
    <x-page-header title="Detalhes da Auditoria #{{ $activity->id }}" :backRoute="url()->previous()" />

    <div class="mt-6">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Painel de Informações -->
            <div class="xl:col-span-1 space-y-6">
                <x-card>
                    <dl class="divide-y divide-neutral-100">
                        <div class="px-5 py-4">
                            <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">MÓDULO AFETADO</dt>
                            <dd class="text-sm font-medium text-neutral-900">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</dd>
                        </div>
                        
                        <div class="px-5 py-4">
                            <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">AÇÃO REALIZADA</dt>
                            <dd>
                                @php
                                    $actionColors = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'restored' => 'yellow'];
                                    $color = $actionColors[$activity->description] ?? 'neutral';
                                @endphp
                                <x-badge :color="$color" size="sm">{{ ucfirst($activity->description) }}</x-badge>
                            </dd>
                        </div>
                        
                        <div class="px-5 py-4">
                            <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">DATA / HORA</dt>
                            <dd class="text-sm font-medium text-neutral-900">{{ formatDateTime($activity->created_at) }}</dd>
                        </div>
                        
                        <div class="px-5 py-4">
                            <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">DESCRIÇÃO</dt>
                            <dd class="text-sm font-medium text-neutral-900">{{ $activity->description }}</dd>
                        </div>
                        
                        <div class="px-5 py-4">
                            <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-3">CAUSADOR</dt>
                            <dd>
                                @if($activity->causer)
                                    <div class="flex items-center gap-3">
                                        <x-avatar :model="$activity->causer" size="md" />
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $activity->causer->name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $activity->causer->email ?? 'Usuário #'.$activity->causer_id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm font-medium text-neutral-500">Sistema / Rotina Automática</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </x-card>
            </div>

                <!-- Painel de Alterações -->
            <div class="xl:col-span-2">
                <x-card>
                    <div class="p-5 border-b border-neutral-100">
                        <h3 class="text-sm font-bold text-neutral-900">Alterações de Dados</h3>
                    </div>
                    
                    <div class="p-0">
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
                            <x-empty-state 
                                icon="heroicon-o-code-bracket" 
                                message="Nenhum dado específico alterado ou registrado." 
                            />
                        @else
                            <x-table class="border-0 shadow-none rounded-none">
                                <x-table.header class="grid-cols-[1fr_1.5fr_1.5fr] hidden sm:grid border-t-0">
                                    <x-table.column>Campo</x-table.column>
                                    <x-table.column>Anterior (Old)</x-table.column>
                                    <x-table.column>Atual (New)</x-table.column>
                                </x-table.header>
                                <div class="divide-y divide-neutral-100">
                                    @foreach($keys as $key)
                                        <x-table.row class="grid-cols-[1fr_1.5fr_1.5fr] hidden sm:grid border-0 hover:bg-neutral-50/50">
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
</x-layouts.app>
