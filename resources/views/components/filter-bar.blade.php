@props([
    'action' => '',
    'searchPlaceholder' => 'Buscar...',
    'searchName' => 'search',
    'searchValue' => request('search'),
    'filters' => ['search'],
    'showSearch' => true,
    'buttonClass' => 'sm:w-8 h-8',
])

@php
    $hasFilters = collect($filters)->contains(fn ($filter) => request()->filled($filter));
@endphp

<form {{ $attributes->merge(['action' => $action, 'method' => 'GET', 'class' => 'flex flex-col sm:flex-row items-center gap-1 mb-8 bg-white p-1 rounded-xl border border-neutral-200 shadow-sm w-full sm:w-fit transition-all focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/40 py-4']) }}
    x-data="{ loading: false }" @submit="loading = true">
    
    @if($showSearch)
    <div class="relative w-full sm:w-80 flex items-center">
        <div class="absolute left-3 flex items-center pointer-events-none">
            <x-heroicon-m-magnifying-glass class="h-4 w-4 text-neutral-400" />
        </div>
        <input type="text" name="{{ $searchName }}" value="{{ $searchValue }}" placeholder="{{ $searchPlaceholder }}" 
               class="w-full pl-9 pr-3 py-1.5 bg-transparent border-0 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-0">
    </div>
    @endif
    
    @if($slot->isNotEmpty())
        @if($showSearch)
            <div class="hidden sm:block w-px h-6 bg-neutral-200 mx-1"></div>
        @endif
        {{ $slot }}
    @endif

    @if($hasFilters)
        <div class="hidden sm:block w-px h-6 bg-neutral-200 mx-1"></div>
        <a href="{{ $action }}" class="flex items-center justify-center text-xs font-semibold text-neutral-500 hover:text-neutral-800 px-3 whitespace-nowrap py-2 sm:py-0">
            Limpar
        </a>
    @endif

    <div class="w-full sm:w-auto mt-2 sm:mt-0 sm:ml-1">
        <button type="submit" aria-label="Buscar/Filtrar" class="flex items-center justify-center w-full {{ $buttonClass }} rounded-lg bg-white border border-neutral-200 hover:bg-neutral-50 text-neutral-500 hover:text-neutral-900 shadow-sm transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/50 focus:ring-offset-1">
            @if($showSearch)
                <x-heroicon-m-magnifying-glass class="w-4 h-4" />
            @else
                <x-heroicon-m-funnel class="w-4 h-4" />
            @endif
        </button>
    </div>
</form>
