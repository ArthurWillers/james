<x-layouts.financial>
    <x-page-header title="Tags Financeiras" :action="route('financial.tags.create')" actionText="Nova Tag" icon="heroicon-o-plus">
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.tags.index') }}" 
        searchPlaceholder="Buscar por nome da tag..." 
        :filters="['search']">
    </x-filter-bar>

    <x-table class="lg:mb-8"
        x-data="{
            selectedTagId: null,
            selectedTagName: '',
            selectedTagUsageCount: 0,
            openDelete(id, name, count) {
                this.selectedTagId = id;
                this.selectedTagName = name;
                this.selectedTagUsageCount = count;
                $dispatch('modal-open', 'delete-tag');
            }
        }">
        
        @if($tags->isNotEmpty())
            <x-table.header class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr]">
                <x-table.column>Nome</x-table.column>
                <x-table.column>Uso (Transações/Itens)</x-table.column>
                <x-table.column align="right">Ações</x-table.column>
            </x-table.header>
        @endif

        <x-table.body>
            @forelse($tags as $tag)
                <x-table.row class="hidden sm:grid sm:grid-cols-[2fr_1fr_1fr] group transition-all" style="--tag-color: {{ $tag->color_hex }};">
                    <x-table.cell>
                        <div class="flex items-center gap-3 w-full">
                            <x-avatar :icon="$tag->icon" class="border-transparent! text-white! w-10 h-10" style="background-color: {{ $tag->color_hex }};" />
                            <div class="flex items-center gap-2 overflow-hidden">
                                <div class="font-semibold text-neutral-900 truncate">{{ $tag->name }}</div>
                                @if($tag->is_protected)
                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20" title="Tag protegida pelo sistema">Protegida</span>
                                @endif
                            </div>
                        </div>
                    </x-table.cell>

                    <x-table.cell>
                        @php
                            $usageCount = $tag->transactions_count + $tag->transaction_items_count;
                        @endphp
                        <span class="text-sm text-neutral-600">
                            Usada em {{ $usageCount }} {{ $usageCount == 1 ? 'registro' : 'registros' }}
                        </span>
                    </x-table.cell>

                    <x-table.cell align="right">
                        <div class="flex justify-end gap-2 w-full">
                            @if(!$tag->is_protected)
                                <x-button type="button" color="outline" href="{{ route('financial.tags.edit', $tag) }}" class="bg-white hover:bg-neutral-50 text-neutral-600 border-neutral-300">
                                    <x-heroicon-o-pencil-square class="size-4" />
                                    Editar
                                </x-button>

                                <x-button type="button" color="outline" class="bg-white hover:bg-red-50 text-red-600 border-red-200" @click="openDelete({{ $tag->id }}, '{{ addslashes($tag->name) }}', {{ $usageCount }})">
                                    <x-heroicon-o-trash class="size-4" />
                                    Excluir
                                </x-button>
                            @else
                                <span class="text-sm text-neutral-400 italic">Nenhuma</span>
                            @endif
                        </div>
                    </x-table.cell>

                    <x-slot name="mobile">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0 flex items-center gap-3">
                                <x-avatar :icon="$tag->icon" class="border-transparent! text-white! w-10 h-10" style="background-color: {{ $tag->color_hex }};" />
                                <div class="overflow-hidden">
                                    <h3 class="text-base font-semibold text-neutral-900 leading-tight mb-1 flex items-center gap-2 truncate">
                                        {{ $tag->name }}
                                        @if($tag->is_protected)
                                            <span class="text-[10px] uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20">Protegida</span>
                                        @endif
                                    </h3>
                                    <div class="flex gap-2 text-sm text-neutral-500 mt-1">
                                        Usada em {{ $usageCount }} {{ $usageCount == 1 ? 'registro' : 'registros' }}
                                    </div>
                                </div>
                            </div>
                            @if(!$tag->is_protected)
                                <div class="shrink-0">
                                    <x-dropdown position="bottom-end" accent>
                                        <x-slot name="trigger">
                                            <button type="button" class="cursor-pointer rounded-md border border-neutral-300 p-2 transition duration-150 ease-in-out hover:bg-neutral-100">
                                                <x-heroicon-o-ellipsis-horizontal class="size-5" />
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <a href="{{ route('financial.tags.edit', $tag) }}" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 cursor-pointer">
                                                <x-heroicon-o-pencil-square class="size-5" />
                                                Editar
                                            </a>

                                            <button type="button" @click="openDelete({{ $tag->id }}, '{{ addslashes($tag->name) }}', {{ $usageCount }})" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-neutral-100 cursor-pointer">
                                                <x-heroicon-o-trash class="size-5" />
                                                Excluir
                                            </button>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            @endif
                        </div>
                    </x-slot>
                </x-table.row>
            @empty
                <x-empty-state 
                    icon="heroicon-o-tag" 
                    title="Nenhuma tag encontrada" 
                    description="Crie uma nova tag para começar a organizar suas transações."
                />
            @endforelse
        </x-table.body>

        <x-modal 
            name="delete-tag"
            title="Excluir Tag" 
            confirmVariant="danger">
            <x-slot name="content">
                <template x-if="selectedTagUsageCount == 0">
                    <div>
                        <p class="text-sm text-neutral-600 mb-4">
                            Você está prestes a excluir a tag <span class="font-medium text-neutral-900" x-text="selectedTagName"></span>.
                        </p>
                        
                        <p class="text-sm font-medium text-neutral-900 mb-4">
                            Tem certeza que deseja continuar?
                        </p>
                    </div>
                </template>

                <template x-if="selectedTagUsageCount > 0">
                    <div>
                        <div class="bg-red-50 text-red-800 p-4 rounded-md text-sm mb-4">
                            <span class="font-semibold flex items-center gap-1.5 mb-1">
                                <x-heroicon-m-x-circle class="size-5" />
                                Ação Bloqueada
                            </span>
                            Não é possível excluir a tag <span class="font-bold" x-text="selectedTagName"></span> pois ela está sendo usada em <span class="font-bold" x-text="selectedTagUsageCount"></span> <span x-text="selectedTagUsageCount == 1 ? 'registro' : 'registros'"></span>.
                        </div>
                    </div>
                </template>
            </x-slot>
            <template x-if="selectedTagUsageCount == 0">
                <form :action="'{{ route('financial.tags.destroy', 0) }}'.replace('/0', '/' + selectedTagId)" method="POST" class="m-0 w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" color="red" class="w-full sm:w-auto">
                        Excluir Permanentemente
                    </x-button>
                </form>
            </template>
        </x-modal>
    </x-table>

    <div class="mt-6 pb-6">
        {{ $tags->links() }}
    </div>
</x-layouts.financial>
