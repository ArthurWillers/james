<x-layouts.app>
    <x-page-header title="Acertos">

    </x-page-header>

    <div class="mb-8 grid grid-cols-3 gap-2 sm:gap-4">
        <x-finance.kpi-card 
            title="A Receber" 
            :value="formatCurrency($toReceive)" 
            icon="heroicon-o-arrow-trending-up" 
            color="green" 
            :hide-icon-on-mobile="true"
        />
        
        <x-finance.kpi-card 
            title="A Pagar" 
            :value="formatCurrency($toPay)" 
            icon="heroicon-o-arrow-trending-down" 
            color="red" 
            :hide-icon-on-mobile="true"
        />
        
        <x-finance.kpi-card 
            title="Líquido" 
            :value="formatCurrency($netBalance)" 
            icon="heroicon-o-scale" 
            :color="$netBalance == 0 ? 'neutral' : ($netBalance > 0 ? 'green' : 'red')" 
            :hide-icon-on-mobile="true"
        />
    </div>

    <div x-data="{
        search: '',
        isSearching: false,
        limit: 102,
        contacts: {{ Js::from($contacts) }},
        selectedIds: [],
        visibleMap: {},
        hasMorePages: false,
        isEmpty: false,
        
        updateVisibility() {
            const map = {};
            let currentLimit = this.limit;
            const searchLower = this.search.toLowerCase();
            
            const filtered = this.contacts.filter(c => c.name.toLowerCase().includes(searchLower));
            
            if (this.selectedIds.length > 0) {
                for (let i = 0; i < filtered.length; i++) {
                    if (this.selectedIds.includes(String(filtered[i].id))) {
                        if (i >= currentLimit) currentLimit = i + 1;
                    }
                }
            }
            
            if (currentLimit % 3 !== 0) currentLimit += 3 - (currentLimit % 3);
            
            for (let i = 0; i < filtered.length; i++) {
                if (i < currentLimit) {
                    map[filtered[i].id] = true;
                }
            }
            
            this.visibleMap = map;
            this.hasMorePages = filtered.length > currentLimit;
            this.isEmpty = filtered.length === 0;
        },
        
        init() {
            this.updateVisibility();
            this.$watch('search', () => {
                this.limit = 102;
                this.updateVisibility();
            });
            this.$watch('selectedIds', () => {
                this.updateVisibility();
            });
            this.$watch('limit', () => {
                this.updateVisibility();
            });
        },
        
        loadMore() {
            this.limit += 102;
        },
        
        selectGroup(groupId) {
            if (!groupId) return;
            
            this.limit = 102;
            const id = Number(groupId);
            
            const searchLower = this.search.toLowerCase();
            const filtered = this.contacts.filter(c => c.name.toLowerCase().includes(searchLower));
            const groupContacts = filtered.filter(c => c.group_ids.includes(id));
            
            groupContacts.forEach(c => {
                const strId = String(c.id);
                if (!this.selectedIds.includes(strId)) {
                    this.selectedIds.push(strId);
                }
            });
        },
        
        toggleAll() {
            const searchLower = this.search.toLowerCase();
            const filtered = this.contacts.filter(c => c.name.toLowerCase().includes(searchLower));
            
            if (this.selectedIds.length === filtered.length && filtered.length > 0) {
                this.selectedIds = [];
            } else {
                this.selectedIds = filtered.map(c => String(c.id));
            }
        },
        
        archiveSelected() {
            if (this.selectedIds.length === 0) return;
            const form = document.getElementById('archive-form');
            form.querySelectorAll('.dynamic-input').forEach(e => e.remove());
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'contact_ids[]';
                input.value = id;
                input.className = 'dynamic-input';
                form.appendChild(input);
            });
            form.submit();
        },
        unarchiveSelected() {
            if (this.selectedIds.length === 0) return;
            const form = document.getElementById('unarchive-form');
            form.querySelectorAll('.dynamic-input').forEach(e => e.remove());
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'contact_ids[]';
                input.value = id;
                input.className = 'dynamic-input';
                form.appendChild(input);
            });
            form.submit();
        }
    }">
        <div class="flex flex-col sm:flex-row justify-between items-start mb-6 gap-4">
            <div class="flex flex-col sm:flex-row sm:items-start gap-2 w-full sm:w-auto">
                <x-ui.button type="button" @click="toggleAll()" color="outline" title="Selecionar Todos" class="h-11 sm:h-[42px] px-3 bg-white shrink-0">
                    <x-heroicon-o-check-circle class="size-5" />
                </x-ui.button>
                
                <div class="flex flex-col sm:flex-row items-center gap-1 bg-white p-1 rounded-xl border border-neutral-200 shadow-sm w-full flex-1 transition-all focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/40">
                    <div class="relative w-full flex-1 flex items-center">
                        <div class="absolute left-3 flex items-center pointer-events-none">
                            <x-heroicon-m-magnifying-glass class="h-4 w-4 text-neutral-400" />
                        </div>
                        <input type="text" x-model="search" placeholder="Buscar pessoa..." 
                               class="w-full pl-9 pr-3 py-1.5 bg-transparent border-0 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-0">
                    </div>
                    
                    <div class="hidden sm:block w-px h-6 bg-neutral-200 mx-1"></div>
                    
                    <select name="group_select" @change="selectGroup($event.target.value); $event.target.value = ''" 
                            class="w-full sm:w-auto bg-transparent border-0 py-1.5 pl-3 pr-8 text-sm text-neutral-600 focus:outline-none focus:ring-0 focus:bg-neutral-100 rounded-md cursor-pointer transition-colors">
                        <option value="">Selecionar Grupo...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                @if($showArchived)
                    <x-button color="outline" href="{{ route('settlements.index') }}" class="bg-white">
                        <x-heroicon-o-arrow-left class="size-4" />
                        Voltar aos Ativos
                    </x-button>
                @else
                    <x-button color="outline" href="{{ route('settlements.index', ['archived' => 1]) }}" class="bg-white text-neutral-600">
                        <x-heroicon-o-archive-box class="size-4" />
                        Ver Arquivados
                    </x-button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-32">
            @foreach($contacts as $contact)
                <div x-show="visibleMap[{{ $contact->id }}]" 
                     @click="if(!$event.target.closest('a') && !$event.target.closest('label')) document.getElementById('chk_contact_{{ $contact->id }}').click()"
                     class="flex items-center gap-4 p-4 rounded-2xl border border-neutral-200 bg-white hover:border-accent hover:shadow-sm transition-all cursor-pointer" 
                     x-bind:class="{'ring-2 ring-accent border-transparent': selectedIds.includes(String({{ $contact->id }}))}">
                    
                    <div class="shrink-0 flex items-center">
                        <x-form.form-checkbox name="contact_ids[]" value="{{ $contact->id }}" id="chk_contact_{{ $contact->id }}" x-model="selectedIds" class="!m-0" />
                    </div>

                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <x-ui.avatar :model="$contact" size="lg" />
                        
                        <div class="flex-1 min-w-0 flex flex-col items-start text-left">
                            <span class="text-base font-semibold text-neutral-900 truncate w-full">{{ $contact->name }}</span>
                            
                            @if($contact->net_balance > 0)
                                <span class="text-sm font-medium text-emerald-600 truncate w-full">Me deve {{ formatCurrency($contact->net_balance) }}</span>
                            @elseif($contact->net_balance < 0)
                                <span class="text-sm font-medium text-red-600 truncate w-full">Devo {{ formatCurrency(abs($contact->net_balance)) }}</span>
                            @else
                                <span class="text-sm text-neutral-500 truncate w-full">{{ formatCurrency(0) }} &mdash; Quite</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="shrink-0 pl-4 border-l border-neutral-100">
                        <a href="#" class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-50 text-neutral-500 hover:bg-neutral-200 hover:text-neutral-700 transition-colors" title="Ver Detalhes">
                            <x-heroicon-o-chevron-right class="size-5" />
                        </a>
                    </div>
                </div>
            @endforeach
            
            <div x-show="isEmpty" x-cloak class="col-span-full">
                <x-ui.empty-state 
                    icon="heroicon-o-users" 
                    message="Nenhum contato encontrado."
                    action-text="Novo Contato"
                    :action-route="route('contacts.create')"
                />
            </div>
            
            <div x-show="hasMorePages" x-cloak class="col-span-full flex justify-center mt-4">
                <x-button type="button" @click="loadMore()" color="outline" class="bg-white">
                    <x-heroicon-o-arrow-down class="size-4" />
                    Carregar Mais
                </x-button>
            </div>
        </div>

        <!-- Active Bar -->
        <div x-show="selectedIds.length > 0" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-10"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center justify-between gap-6 px-6 py-4 bg-white rounded-2xl shadow-xl border border-neutral-200 min-w-[300px]" style="display: none;">
            
            <div class="flex items-center gap-4">
                <div class="text-sm font-medium text-neutral-700">
                    <span x-text="selectedIds.length"></span> selecionado(s)
                </div>
                
                <div class="w-px h-6 bg-neutral-200"></div>
                
                <div class="flex items-center gap-2">
                    @if($showArchived)
                        <x-button type="button" @click="unarchiveSelected()" color="primary">
                            <x-heroicon-o-arrow-path class="size-4" />
                            Desarquivar
                        </x-button>
                    @else
                        <x-button type="button" @click="archiveSelected()" color="primary" class="bg-amber-500 hover:bg-amber-600 text-white border-amber-500">
                            <x-heroicon-o-archive-box class="size-4" />
                            Arquivar
                        </x-button>
                    @endif
                </div>
            </div>

            <button type="button" @click="selectedIds = []" class="p-2 -mr-2 rounded-xl text-neutral-400 hover:bg-neutral-100 hover:text-neutral-900 transition-colors" title="Limpar seleção">
                <x-heroicon-o-x-mark class="size-5" />
            </button>
        </div>
    </div>

    <form id="archive-form" action="{{ route('settlements.archive') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="contact_ids" id="archive-form-input">
    </form>
    
    <form id="unarchive-form" action="{{ route('settlements.unarchive') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="contact_ids" id="unarchive-form-input">
    </form>
</x-layouts.app>
