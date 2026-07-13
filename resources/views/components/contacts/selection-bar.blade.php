@props([
    'groups' => [],
    'searchModel' => 'search',
    'searchPlaceholder' => 'Buscar pessoa...',
    'toggleAction' => 'toggleAll()',
    'groupChangeAction' => "selectGroup(\$event.target.value); \$event.target.value = ''",
])

<div class="flex flex-col sm:flex-row sm:items-start gap-2 w-full sm:w-auto">
    <x-button type="button" @click="{{ $toggleAction }}" color="outline" title="Selecionar Todos" class="h-11 sm:h-[42px] px-3 bg-white shrink-0">
        <x-heroicon-o-check-circle class="size-5" />
    </x-button>
    
    <div class="flex flex-col sm:flex-row items-center gap-1 bg-white p-1 rounded-xl border border-neutral-200 shadow-sm w-full flex-1 transition-all focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/40">
        <div class="relative w-full flex-1 flex items-center">
            <div class="absolute left-3 flex items-center pointer-events-none">
                <x-heroicon-m-magnifying-glass class="h-4 w-4 text-neutral-400" />
            </div>
            <input type="text" x-model="{{ $searchModel }}" placeholder="{{ $searchPlaceholder }}" 
                   class="w-full pl-9 pr-3 py-1.5 bg-transparent border-0 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-0">
        </div>
        
        @if(!empty($groups) && count($groups) > 0)
            <div class="hidden sm:block w-px h-6 bg-neutral-200 mx-1"></div>
            
            <select name="group_select" @change="{{ $groupChangeAction }}" 
                    class="w-full sm:w-auto bg-transparent border-0 py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                <option value="">Selecionar Grupo...</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
