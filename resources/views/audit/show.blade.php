<x-layouts.app>
    <x-page-header title="Detalhes do Log #{{ $activity->id }}" :backRoute="url()->previous()" />

    <div class="mt-6">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Painel de Informações -->
            <div class="xl:col-span-1 space-y-6">
                <x-card class="!p-0 overflow-hidden">
                    <div class="divide-y divide-neutral-100">
                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">MÓDULO AFETADO</p>
                            <div class="text-sm font-medium text-neutral-900">
                                @php
                                    $modelName = class_basename($activity->subject_type);
                                    $routeName = str($modelName)->kebab()->plural() . '.show';
                                    $subjectUrl = Route::has($routeName) ? route($routeName, $activity->subject_id) : null;
                                @endphp
                                @if($subjectUrl)
                                    <a href="{{ $subjectUrl }}" class="text-accent hover:underline flex items-center gap-1">
                                        {{ $modelName }} #{{ $activity->subject_id }}
                                        <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
                                    </a>
                                @else
                                    {{ $modelName }} #{{ $activity->subject_id }}
                                @endif
                            </div>
                        </div>
                        
                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">AÇÃO REALIZADA</p>
                            <div>
                                @php
                                    $actionTranslations = ['created' => 'Criado', 'updated' => 'Atualizado', 'deleted' => 'Excluído', 'restored' => 'Restaurado'];
                                    $actionName = $actionTranslations[$activity->description] ?? ucfirst($activity->description);
                                    $actionColors = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'restored' => 'yellow'];
                                    $color = $actionColors[$activity->description] ?? 'neutral';
                                @endphp
                                <x-badge :color="$color" size="sm">{{ $actionName }}</x-badge>
                            </div>
                        </div>
                        
                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-1">DATA / HORA</p>
                            <div class="text-sm font-medium text-neutral-900">{{ formatDateTime($activity->created_at) }}</div>
                        </div>
                        
                        <div class="px-5 py-4">
                            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-3">CAUSADOR</p>
                            <div>
                                @if($activity->causer)
                                    <div class="flex items-center gap-3">
                                        <x-avatar :model="$activity->causer" size="md" />
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $activity->causer->name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $activity->causer->email ?? 'Usuário #'.$activity->causer_id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3">
                                        <x-avatar icon="heroicon-o-cpu-chip" size="md" />
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">Sistema</div>
                                            <div class="text-xs text-neutral-500">Rotina Automática</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Painel de Alterações -->
            <div class="xl:col-span-2">
                
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
                    $hasOld = count($old) > 0;
                    $gridClass = $hasOld ? 'grid-cols-[1fr_1.5fr_1.5fr]' : 'grid-cols-[1fr_1.5fr]';
                @endphp

                @if($keys->isEmpty())
                    <x-empty-state 
                        icon="heroicon-o-code-bracket" 
                        title="Nenhum dado alterado"
                        description="Nenhum dado específico alterado ou registrado." 
                    />
                @else
                    <x-table>
                        <x-table.header class="{{ $gridClass }} hidden sm:grid">
                            <x-table.column>Campo</x-table.column>
                            @if($hasOld)
                                <x-table.column>Anterior</x-table.column>
                            @endif
                            <x-table.column>Atual</x-table.column>
                        </x-table.header>
                        <div class="divide-y divide-neutral-100">
                            @foreach($keys as $key)
                                @php
                                    $isDate = str_ends_with($key, '_at') || str_ends_with($key, '_date') || $key === 'date';
                                    
                                    $formatValue = function($val) use ($isDate) {
                                        if (is_array($val) || is_object($val)) return json_encode($val);
                                        if ($isDate && !empty($val)) {
                                            try {
                                                return formatDateTime($val);
                                            } catch (\Exception $e) {
                                                return $val;
                                            }
                                        }
                                        return $val ?? 'Nulo';
                                    };
                                @endphp
                                <x-table.row class="{{ $gridClass }} hidden sm:grid">
                                    <x-table.cell class="font-medium text-neutral-900 whitespace-normal break-words">{{ $key }}</x-table.cell>
                                    @if($hasOld)
                                        <x-table.cell class="text-red-600 font-medium break-all">
                                            @if(array_key_exists($key, $old))
                                                {{ $formatValue($old[$key]) }}
                                            @else
                                                <span class="text-neutral-400 font-normal">-</span>
                                            @endif
                                        </x-table.cell>
                                    @endif
                                    <x-table.cell class="text-green-600 font-medium break-all">
                                        @if(array_key_exists($key, $attributes))
                                            {{ $formatValue($attributes[$key]) }}
                                        @else
                                            <span class="text-neutral-400 font-normal">-</span>
                                        @endif
                                    </x-table.cell>
                                    
                                    <x-slot:mobile>
                                        <div class="font-medium text-neutral-900 mb-2 whitespace-normal break-words">{{ $key }}</div>
                                        <div class="grid {{ $hasOld ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 text-sm">
                                            @if($hasOld)
                                                <div class="text-red-600 font-medium break-all">
                                                    <span class="text-neutral-400 font-normal block text-xs mb-0.5">Anterior:</span>
                                                    @if(array_key_exists($key, $old))
                                                        {{ $formatValue($old[$key]) }}
                                                    @else
                                                        <span class="text-neutral-400 font-normal">-</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="text-green-600 font-medium break-all">
                                                <span class="text-neutral-400 font-normal block text-xs mb-0.5">Atual:</span>
                                                @if(array_key_exists($key, $attributes))
                                                    {{ $formatValue($attributes[$key]) }}
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
        </div>
    </div>
</x-layouts.app>
