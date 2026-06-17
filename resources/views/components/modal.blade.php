@props([
    'name',
    'title',
    'message' => null,
    'confirmVariant' => 'danger'
])

<div x-data="{ open: false }" 
     @modal-open.window="if ($event.detail === '{{ $name }}') open = true"
     @modal-close.window="if ($event.detail === '{{ $name }}') open = false"
     @keydown.escape.window="open = false"
     class="contents"
>

    <template x-teleport="body">
        <div x-show="open" 
             style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            

            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-neutral-900/50 backdrop-blur-sm transition-opacity" 
                 @click="open = false"
                 aria-hidden="true"></div>


            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            @if($confirmVariant === 'danger')
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <x-icons.outline.exclamation-triangle class="h-6 w-6 text-red-600" />
                                </div>
                            @else
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <x-icons.outline.information-circle class="h-6 w-6 text-blue-600" />
                                </div>
                            @endif
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-neutral-900" id="modal-title">
                                    {{ $title }}
                                </h3>
                                <div class="mt-2">
                                    @if($message)
                                        <p class="text-sm text-neutral-500">
                                            {{ $message }}
                                        </p>
                                    @elseif(isset($content))
                                        <div class="text-sm text-neutral-500">
                                            {{ $content }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-neutral-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <div class="sm:ml-3 sm:w-auto" @click="open = false; $dispatch('modal-confirm')">
                            {{ $slot }}
                        </div>
                        <x-button type="button" 
                                  color="outline"
                                  @click="open = false; $dispatch('modal-cancel')" 
                                  class="mt-3 w-full sm:mt-0 sm:w-auto">
                            Cancelar
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
