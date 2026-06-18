<x-layouts.app>
    <div class="flex justify-between items-center mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('contacts.index') }}">Contatos</x-breadcrumbs.item>
            
            <x-breadcrumbs.item>Novo Contato</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Novo Contato">
        <x-button color="outline" href="{{ route('contacts.index') }}" class="bg-white">
            <x-icons.outline.arrow-left class="size-4" />
            Cancelar
        </x-button>

        <x-button type="submit" form="create-contact-form">
            <x-icons.outline.check class="size-4" />
            Salvar
        </x-button>
    </x-page-header>

    <form id="create-contact-form" action="{{ route('contacts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-card class="mb-4 p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                <div class="flex flex-col items-center gap-4 shrink-0" x-data="{
                    previewUrl: null,
                    isModalOpen: false,
                    cropper: null,
                    fileSelected(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.$refs.cropperImage.src = e.target.result;
                            this.isModalOpen = true;
                            this.$nextTick(() => {
                                if (this.cropper) {
                                    this.cropper.destroy();
                                }
                                this.cropper = new Cropper(this.$refs.cropperImage, {
                                    aspectRatio: 1,
                                    viewMode: 1,
                                    autoCropArea: 1,
                                });
                            });
                        };
                        reader.readAsDataURL(file);
                    },
                    closeModal(isCancel = true) {
                        this.isModalOpen = false;
                        if (this.cropper) {
                            this.cropper.destroy();
                            this.cropper = null;
                        }
                        if (isCancel) {
                            this.$refs.fileInput.value = '';
                        }
                    },
                    applyCrop() {
                        if (!this.cropper) return;
                        
                        this.cropper.getCroppedCanvas({
                            width: 500,
                            height: 500
                        }).toBlob((blob) => {
                            const file = new File([blob], 'avatar.webp', { type: 'image/webp' });
                            
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            this.$refs.fileInput.files = dataTransfer.files;
                            
                            this.previewUrl = URL.createObjectURL(blob);
                            this.closeModal(false);
                        }, 'image/webp', 0.9);
                    }
                }">

                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Novo Avatar" class="shrink-0 border rounded-md object-cover bg-neutral-200 border-[var(--color-accent)] w-24 h-24 text-4xl">
                    </template>
                    <template x-if="!previewUrl">
                        <x-avatar :model="new \App\Models\Contact()" size="2xl" />
                    </template>

                    <div class="flex flex-col items-center gap-2">
                        <label class="cursor-pointer">
                            <span class="text-sm font-medium text-accent hover:text-accent/80 transition-colors">Adicionar foto</span>
                            <input type="file" x-ref="fileInput" name="avatar" class="hidden" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" @change="fileSelected">
                        </label>
                    </div>
                    <x-form-error name="avatar" />


                    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @keydown.escape.window="closeModal">
                        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full overflow-hidden flex flex-col" @click.away="closeModal">
                            <div class="p-4 border-b flex justify-between items-center">
                                <h3 class="font-bold text-neutral-800">Recortar Foto</h3>
                                <button type="button" @click="closeModal" class="text-neutral-400 hover:text-neutral-600 cursor-pointer">
                                    <x-icons.outline.x-mark class="size-5" />
                                </button>
                            </div>
                            <div class="bg-neutral-100 flex justify-center items-center overflow-hidden h-[60vh]">
                                <img x-ref="cropperImage" class="max-w-full max-h-full block">
                            </div>
                            <div class="p-4 border-t bg-neutral-50 flex justify-end gap-2">
                                <x-button type="button" color="outline" class="bg-white cursor-pointer" @click="closeModal">Cancelar</x-button>
                                <x-button type="button" class="cursor-pointer" @click="applyCrop">
                                    <x-icons.outline.check class="size-4" /> Cortar e Aplicar
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 w-full flex flex-col gap-4">
                    <div>
                        <x-form-input name="name" label="Nome" value="{{ old('name') }}" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-form-combobox 
                                name="relationship_category" 
                                label="Categoria" 
                                :value="old('relationship_category')" 
                                :options="$categories" 
                            />
                        </div>
                        <div>
                            <x-form-input type="date" name="birthdate" label="Data de Nascimento" value="{{ old('birthdate') }}" />
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-form-key-value-repeater 
                name="phones" 
                title="Telefones" 
                :items="$phones" 
                value-placeholder="Número" 
                empty-message="Nenhum telefone cadastrado." 
            />

            <x-form-key-value-repeater 
                name="emails" 
                title="E-mails" 
                :items="$emails" 
                value-placeholder="Endereço de e-mail" 
                empty-message="Nenhum e-mail cadastrado." 
            />
        </div>

        <x-card class="mb-4 p-6">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-6">Notas</h3>
            <div class="grid w-full items-center gap-1.5" x-data="{ mde: null }" x-init="mde = new EasyMDE({ element: $refs.editor, forceSync: true, status: false, spellChecker: false })">
                <textarea x-ref="editor" id="notes" name="notes" rows="6" placeholder="Anotações sobre o contato..." class="w-full border border-neutral-200 appearance-none text-sm rounded-xl block py-2.5 px-4 bg-white disabled:shadow-none shadow-xs focus:shadow-lg text-neutral-700 disabled:text-neutral-400 placeholder-neutral-400 disabled:placeholder-neutral-400/70 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40 transition-colors duration-300 {{ $errors->has('notes') ? 'border-red-500 focus:border-red-500 focus:ring-red-400/30' : '' }}">{{ old('notes') }}</textarea>
                <x-form-error name="notes" />
            </div>
        </x-card>
    </form>
</x-layouts.app>
