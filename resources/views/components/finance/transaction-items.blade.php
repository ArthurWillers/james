<x-card class="p-6 flex flex-col">
    <div class="flex justify-between items-center mb-6 shrink-0">
        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Itens da Transação (Opcional)</h3>
        <x-button type="button" @click="addItem" color="accent-ghost" class="text-xs! py-1! px-2!">
            <x-heroicon-o-plus class="size-3" /> Adicionar
        </x-button>
    </div>
    
    <div class="flex flex-col gap-3">
        <!-- Table Header -->
        <div class="flex gap-2 items-center text-xs font-bold text-neutral-400 uppercase tracking-widest px-1 mb-1" x-show="items.length > 0" style="display: none;">
            <div class="flex-1">Descrição do Item</div>
            <div class="w-24">Qtd</div>
            <div class="w-32">Valor (R$)</div>
            <div class="w-8"></div>
        </div>
        
        <template x-for="(item, index) in items" :key="index">
            <div class="flex gap-2 items-start">
                <div class="flex-1">
                    <x-form-input x-model="item.description" ::name="'items['+index+'][description]'" placeholder="Descrição" />
                </div>
                <div class="w-24">
                    <x-form-input x-data @input="$event.target.value = $event.target.value.replace(/[^0-9.,]/g, '')" inputmode="decimal" x-model="item.quantity" ::name="'items['+index+'][quantity]'" placeholder="0" />
                </div>
                <div class="w-32">
                    <x-form-input x-data @input="$event.target.value = $event.target.value.replace(/[^-0-9.,]/g, '')" inputmode="decimal" x-model="item.unit_price" ::name="'items['+index+'][unit_price]'" placeholder="0,00" />
                </div>
                <div class="w-8 shrink-0 flex justify-center pt-1.5">
                    <x-button type="button" @click="removeItem(index)" color="danger-ghost" class="p-1.5!" aria-label="Remover">
                        <x-heroicon-o-trash class="size-4" />
                    </x-button>
                </div>
            </div>
        </template>
        <p x-show="items.length === 0" class="text-sm text-neutral-400 italic mb-2">Nenhum item adicionado nesta transação.</p>
    </div>
</x-card>
