<div x-data="{
    lightboxOpen: false,
    imageUrl: '',
    fileName: '',
    openLightbox(url, name) {
        this.imageUrl = url;
        this.fileName = name;
        this.lightboxOpen = true;
        document.body.style.overflow = 'hidden';
    },
    closeLightbox() {
        this.lightboxOpen = false;
        document.body.style.overflow = '';
    }
}" {{ $attributes }}>
    {{ $slot }}

    <!-- Lightbox Modal -->
    <template x-teleport="body">
        <div x-show="lightboxOpen" style="display: none; background-color: rgba(0,0,0,0.95);" class="fixed inset-0 z-50 flex flex-col p-4 md:p-8" @keydown.escape.window="closeLightbox">
            <!-- Top Bar / Actions -->
            <div class="flex justify-end gap-3 mb-4 shrink-0 z-50">
                <!-- Download Button -->
                <a :href="imageUrl" :download="fileName" style="background-color: rgba(0,0,0,0.5); color: white;" class="p-2.5 hover:bg-black transition-colors rounded-full flex items-center justify-center cursor-pointer" title="Baixar Original">
                    <x-heroicon-o-arrow-down-tray class="size-6" />
                </a>
                <!-- Close button -->
                <button @click="closeLightbox" style="background-color: rgba(0,0,0,0.5); color: white;" class="p-2.5 hover:bg-black transition-colors rounded-full cursor-pointer">
                    <x-heroicon-o-x-mark class="size-6" />
                </button>
            </div>

            <!-- Image Container -->
            <div class="flex-1 min-h-0 flex items-center justify-center" @click.self="closeLightbox">
                <img :src="imageUrl" :alt="fileName" class="max-w-full max-h-full object-contain rounded-md shadow-2xl" />
            </div>
        </div>
    </template>
</div>
