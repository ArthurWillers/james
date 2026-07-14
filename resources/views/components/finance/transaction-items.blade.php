@props(['tags'])

<x-card class="flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Itens da Transação (Opcional)</h3>
        <x-button type="button" @click="addItem" color="accent-ghost" class="text-xs! py-1! px-2!">
            <x-heroicon-o-plus class="size-3" /> Adicionar
        </x-button>
    </div>
    
    <div class="flex flex-col gap-3">
        <!-- Table Header -->
        <div class="hidden sm:flex gap-2 items-center text-xs font-bold text-neutral-400 uppercase tracking-widest px-1 mb-1" x-show="items.length > 0" style="display: none;">
            <div class="flex-1">Descrição do Item</div>
            <div class="w-24">Qtd</div>
            <div class="w-32">Valor (R$)</div>
            <div class="w-8"></div>
        </div>
        
        <template x-for="(item, index) in items" :key="index">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-start py-2 sm:py-1 border-b border-neutral-100 sm:border-0 pb-3 sm:pb-1 mb-1 sm:mb-0 last:border-0">
                <div class="w-full sm:flex-1">
                    <x-form-input x-model="item.description" ::name="'items['+index+'][description]'" placeholder="Descrição do item" />
                </div>
                <div class="flex gap-2 w-full sm:w-auto items-start">
                    <div class="w-24 shrink-0">
                        <x-form-input x-data @input="$event.target.value = $event.target.value.replace(/[^0-9.,]/g, '')" inputmode="decimal" x-model="item.quantity" ::name="'items['+index+'][quantity]'" placeholder="Qtd" />
                    </div>
                    <div class="flex-1 sm:w-28 shrink-0">
                        <x-form-input :currency="true" x-model="item.unit_price" ::name="'items['+index+'][unit_price]'" placeholder="R$ 0,00" />
                    </div>
                    <div class="flex items-center gap-1 shrink-0 pt-1.5 px-1">
                    <x-tags-selector 
                        x-name="`items[${index}][tags][]`" 
                        :options="$tags" 
                        x-value="item.tags ? Object.values(item.tags).map(Number) : []"
                        x-primary-value="item.primary_tag_id ? Number(item.primary_tag_id) : null"
                        @tags-changed="item.tags = $event.detail.ids; item.primary_tag_id = $event.detail.primaryId; items = [...items]"
                    >
                        <x-slot:trigger>
                            <button type="button" class="cursor-pointer p-1.5 text-neutral-400 hover:text-accent hover:bg-neutral-100 rounded-lg transition-colors relative" title="Gerenciar Tags">
                                <x-heroicon-o-tag class="size-4" />
                                <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-accent text-[9px] font-bold text-white shadow-sm ring-2 ring-white" x-text="selectedIds.length" x-show="selectedIds.length > 0"></span>
                            </button>
                        </x-slot:trigger>
                    </x-tags-selector>

                    <x-button type="button" @click="removeItem(index)" color="danger-ghost" class="p-1.5! hover:bg-red-50" aria-label="Remover">
                        <x-heroicon-o-trash class="size-4" />
                    </x-button>
                </div>
            </div>
        </template>
        <p class="text-sm text-neutral-400 italic mb-2" x-show="items.length === 0">Nenhum item adicionado nesta transação.</p>
    </div>
</x-card>
