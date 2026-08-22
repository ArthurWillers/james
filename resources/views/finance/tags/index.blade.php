<x-layouts.financial>
    <x-page-header title="Tags Financeiras" :action="route('financial.tags.create')" actionText="Nova Tag" icon="heroicon-o-plus">
    </x-page-header>

    <x-filter-bar 
        action="{{ route('financial.tags.index') }}" 
        searchPlaceholder="Buscar por nome da tag..." 
        :filters="['search']">
    </x-filter-bar>

    <x-table class="lg:mb-8">
        
        @if($tags->isNotEmpty())
            <x-table.header class="hidden sm:grid sm:grid-cols-[2fr_1fr_80px]">
                <x-table.column>Nome</x-table.column>
                <x-table.column>Uso (Transações/Itens)</x-table.column>
                <x-table.column></x-table.column>
            </x-table.header>
        @endif

        <x-table.body>
            @forelse($tags as $tag)
                <x-table.row href="{{ route('financial.tags.show', $tag) }}" class="t-learn hidden sm:grid sm:grid-cols-[2fr_1fr_80px] group transition-all hover:bg-neutral-50" style="--tag-color: {{ $tag->color_hex }};">
                    <x-table.cell>
                        <div class="flex items-center gap-3 w-full">
                            <x-avatar :icon="$tag->icon" class="border-transparent! text-white! w-10 h-10" style="background-color: {{ $tag->color_hex }};" />
                            <div class="flex items-center gap-2 overflow-hidden">
                                <div class="font-semibold text-neutral-900 truncate">{{ $tag->name }}</div>
                                @if($tag->is_protected)
                                <x-tooltip text="Tag protegida pelo sistema">
                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Protegida</span>
                                </x-tooltip>
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
                        <x-heroicon-m-chevron-right class="t-learn-chevron size-5 text-neutral-400 group-hover:text-accent transition-colors" />
                    </x-table.cell>

                    <x-slot name="mobile">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0 flex items-center gap-3">
                                <x-avatar :icon="$tag->icon" class="border-transparent! text-white! w-10 h-10" style="background-color: {{ $tag->color_hex }};" />
                                <div class="overflow-hidden">
                                    <h3 class="text-base font-semibold text-neutral-900 leading-tight mb-1 flex items-center gap-2 truncate">
                                        {{ $tag->name }}
                                        @if($tag->is_protected)
                                            <span class="text-xxs uppercase font-bold text-yellow-700 bg-yellow-50 px-1.5 py-0.5 rounded ring-1 ring-inset ring-yellow-600/20">Protegida</span>
                                        @endif
                                    </h3>
                                    <div class="flex gap-2 text-sm text-neutral-500 mt-1">
                                        Usada em {{ $usageCount }} {{ $usageCount == 1 ? 'registro' : 'registros' }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-neutral-400 group-hover/card:text-accent transition-colors self-center pr-2">
                                <x-heroicon-o-chevron-right class="t-learn-chevron size-5" />
                            </div>
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
    </x-table>

    <div class="mt-6 pb-6">
        {{ $tags->links() }}
    </div>
</x-layouts.financial>
