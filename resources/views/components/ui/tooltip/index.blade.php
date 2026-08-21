@props(['text' => ''])

<div x-data="{ open: false }" 
     @mouseenter="open = true" 
     @mouseleave="open = false" 
     @focusin="open = true" 
     @focusout="open = false" 
     class="relative inline-flex group">
     
    {{ $slot }}

    <div x-show="open" 
         x-transition.opacity.duration.200ms
         x-cloak
         class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2.5 py-1 bg-neutral-800 text-white text-xs font-medium rounded-md shadow-lg whitespace-nowrap z-50 pointer-events-none">
        {{ $text }}
        {{-- Seta do tooltip --}}
        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-neutral-800"></div>
    </div>
</div>
