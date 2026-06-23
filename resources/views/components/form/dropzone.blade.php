@props([
    'name',
    'multiple' => false,
    'accept' => '*',
    'label' => 'Clique para fazer upload',
    'sublabel' => 'ou arraste e solte o arquivo aqui',
])

<div x-data="{
        isDropping: false,
        files: [],
        handleDrop(e) {
            this.isDropping = false;
            this.files = Array.from(e.dataTransfer.files);
            $refs.fileInput.files = e.dataTransfer.files;
            $refs.fileInput.dispatchEvent(new Event('change'));
        },
        handleFileSelect(e) {
            this.files = Array.from(e.target.files);
        },
        removeFile(index) {
            this.files.splice(index, 1);
            
            // Reconstruct DataTransfer to update input.files
            const dt = new DataTransfer();
            this.files.forEach(file => dt.items.add(file));
            $refs.fileInput.files = dt.files;
            $refs.fileInput.dispatchEvent(new Event('change'));
        }
    }"
    class="w-full">
    
    <label 
        @dragover.prevent="isDropping = true"
        @dragleave.prevent="isDropping = false"
        @drop.prevent="handleDrop($event)"
        :class="isDropping ? 'border-[var(--color-accent)] bg-[var(--color-accent)]/5' : 'border-neutral-300 hover:bg-neutral-50'"
        class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-150">
        
        <div class="flex flex-col items-center justify-center pt-5 pb-6 pointer-events-none">
            <svg :class="isDropping ? 'text-[var(--color-accent)]' : 'text-neutral-500'" class="w-8 h-8 mb-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
            </svg>
            <p class="mb-1 text-sm text-neutral-500">
                <span class="font-semibold" x-text="isDropping ? 'Solte o arquivo agora' : '{{ $label }}'"></span>
            </p>
            <p class="text-xs text-neutral-500" x-show="!isDropping">{{ $sublabel }}</p>
        </div>
        
        <input x-ref="fileInput" @change="handleFileSelect" id="{{ $name }}" name="{{ $name }}" type="file" class="hidden" {{ $multiple ? 'multiple' : '' }} accept="{{ $accept }}" />
    </label>

    {{-- File List --}}
    <template x-if="files.length > 0">
        <ul class="mt-3 space-y-2">
            <template x-for="(file, index) in files" :key="index">
                <li class="flex items-center justify-between p-2 text-sm bg-neutral-100 rounded-md border border-neutral-200">
                    <div class="flex items-center space-x-2 overflow-hidden">
                        <x-heroicon-o-document class="w-4 h-4 text-neutral-500 shrink-0" />
                        <span class="truncate font-medium text-neutral-700" x-text="file.name"></span>
                        <span class="text-xs text-neutral-500 shrink-0" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                    </div>
                    <button @click.prevent="removeFile(index)" type="button" class="p-1 text-neutral-400 hover:text-red-500 transition-colors">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </li>
            </template>
        </ul>
    </template>
</div>
