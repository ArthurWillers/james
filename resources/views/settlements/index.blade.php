<x-layouts.app>
    <x-page-header title="Acertos">
        <div class="flex items-center gap-3">
            @if($hasGroups)
                <x-button color="outline" href="{{ route('settlements.groups.index') }}" class="bg-white">
                    <x-heroicon-o-users class="size-4" />
                    <span class="hidden sm:inline">Contas Divididas</span>
                    <span class="sm:hidden">Grupos</span>
                </x-button>
            @endif
            @if($hasHistory)
                <x-button color="outline" href="{{ route('settlements.history') }}" class="bg-white">
                    <x-heroicon-o-clock class="size-4" />
                    <span class="hidden sm:inline">Histórico Global</span>
                    <span class="sm:hidden">Histórico</span>
                </x-button>
            @endif
        </div>
    </x-page-header>

    <div class="mb-8 grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-4">
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
            :hide-icon-on-mobile="false"
            class="col-span-2 md:col-span-1"
        >
            {{ $netBalance > 0 ? 'Você tem a receber no geral' : ($netBalance < 0 ? 'Você tem a pagar no geral' : 'Tudo quitado') }}
        </x-finance.kpi-card>
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
        userPixKey: '{{ $pixKeys->first() ?? '' }}',
        selectedPixKey: '{{ $pixKeys->first() ?? '' }}',
        generatedText: '',
        copied: false,
        
        formatCurrencyJS(value) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
        },
        
        generateGroupText() {
            const selectedContacts = this.contacts.filter(c => this.selectedIds.includes(String(c.id)));
            const meDeve = selectedContacts.filter(item => item.net_balance > 0).sort((a, b) => b.net_balance - a.net_balance);
            const euDevo = selectedContacts.filter(item => item.net_balance < 0).sort((a, b) => a.net_balance - b.net_balance);
            const zerados = selectedContacts.filter(item => item.net_balance === 0).sort((a, b) => a.name.localeCompare(b.name));

            const aReceber = [];
            const aPagar = [];
            const quites = [];

            meDeve.forEach(item => {
                const formatted = this.formatCurrencyJS(Math.abs(item.net_balance)).replace(/\s/g, '');
                aReceber.push(`- ${item.name}: ${formatted}`);
            });

            euDevo.forEach(item => {
                const formatted = this.formatCurrencyJS(Math.abs(item.net_balance)).replace(/\s/g, '');
                aPagar.push(`- ${item.name}: ${formatted}`);
            });

            zerados.forEach(item => {
                quites.push(`- ${item.name}`);
            });

            let parts = [];
            
            if (aReceber.length > 0) {
                parts.push(`*Me Deve:*\n${aReceber.join('\n')}`);
            }
            
            if (aPagar.length > 0) {
                parts.push(`*Eu Devo:*\n${aPagar.join('\n')}`);
            }
            
            if (quites.length > 0) {
                parts.push(`*Tudo Certo:*\n${quites.join('\n')}`);
            }

            let text = parts.join('\n\n');

            if (this.selectedPixKey) {
                text += `\n\nChave PIX: *${this.selectedPixKey}*`;
            }

            this.generatedText = text;
        },
        
        openShareModal() {
            this.generateGroupText();
            window.dispatchEvent(new CustomEvent('modal-open', { detail: 'share-modal' }));
        },
        
        async executeCopy() {
            try {
                await navigator.clipboard.writeText(this.generatedText);
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                    window.dispatchEvent(new CustomEvent('modal-close', { detail: 'share-modal' }));
                }, 1500);
            } catch (e) {
                console.error('Failed to copy text', e);
            }
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
            this.$watch('selectedPixKey', () => {
                this.generateGroupText();
            });
        },
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
            <x-contacts.selection-bar :groups="$groups" />
            
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                @if($showArchived)
                    <x-button color="outline" href="{{ route('settlements.index') }}" class="bg-white">
                        <x-heroicon-o-arrow-left class="size-4" />
                        Voltar aos Ativos
                    </x-button>
                @else
                    @if($hasArchived)
                        <x-button color="outline" href="{{ route('settlements.index', ['archived' => 1]) }}" class="bg-white text-neutral-600">
                            <x-heroicon-o-archive-box class="size-4" />
                            Ver Arquivados
                        </x-button>
                    @endif
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-28">
            @foreach($contacts as $contact)
                <x-contacts.selectable-card :contact="$contact" selected-model="selectedIds" :show-balance="true" x-show="visibleMap[{{ $contact->id }}]">
                    <div class="shrink-0 pl-4 border-l border-neutral-100">
                        <a href="{{ route('settlements.contact.show', $contact) }}" class="flex items-center justify-center w-10 h-10 rounded-full bg-neutral-50 text-neutral-500 hover:bg-neutral-200 hover:text-neutral-700 transition-colors" title="Ver Detalhes">
                            <x-heroicon-o-chevron-right class="size-5" />
                        </a>
                    </div>
                </x-contacts.selectable-card>
            @endforeach
            
            <div x-show="isEmpty" x-cloak class="col-span-full">
                @if($showArchived)
                    <x-empty-state 
                        icon="heroicon-o-users" 
                        message="Nenhum contato arquivado encontrado."
                    />
                @else
                    <x-empty-state 
                        icon="heroicon-o-users" 
                        message="Nenhum contato encontrado."
                        action-text="Novo Contato"
                        :action-route="route('contacts.create')"
                    />
                @endif
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
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center justify-between gap-4 sm:gap-6 px-6 py-4 bg-white rounded-2xl shadow-xl border border-neutral-200 min-w-[300px]" style="display: none;">
            
            <div class="flex items-center gap-4">
                <div class="text-sm font-medium text-neutral-700">
                    <span x-text="selectedIds.length"></span> selecionado(s)
                </div>
                
                <div class="w-px h-6 bg-neutral-200"></div>
                
                <div class="flex items-center gap-2">
                    @if($showArchived)
                        <x-modal.trigger name="bulk-unarchive">
                            <x-button type="button" color="primary">
                                <x-heroicon-o-arrow-path class="size-4" />
                                <span class="hidden sm:inline">Desarquivar</span>
                            </x-button>
                        </x-modal.trigger>
                    @else
                        <x-button type="button" @click="openShareModal()" color="primary" class="bg-neutral-800 hover:bg-neutral-900 border-neutral-800 text-white transition-all">
                            <x-heroicon-o-share class="size-4" /> 
                            <span class="hidden sm:inline">Compartilhar</span>
                        </x-button>
                        <x-button type="button" @click="window.location = '{{ route('settlements.groups.create') }}?contacts=' + selectedIds.join(',')" color="primary">
                            <x-heroicon-o-scissors class="size-4" />
                            <span class="hidden sm:inline">Dividir Conta</span>
                        </x-button>
                        <x-modal.trigger name="bulk-archive">
                            <x-button type="button" color="primary" class="bg-amber-500 hover:bg-amber-600 text-white border-amber-500">
                                <x-heroicon-o-archive-box class="size-4" />
                                <span class="hidden sm:inline">Arquivar</span>
                            </x-button>
                        </x-modal.trigger>
                    @endif
                </div>
            </div>

            <button type="button" class="p-2 -mr-2 rounded-xl text-neutral-400 hover:bg-neutral-100 hover:text-neutral-900 transition-colors" title="Limpar seleção" @click="selectedIds = []">
                <x-heroicon-o-x-mark class="size-5" />
            </button>
        </div>

        <x-modal 
            name="bulk-archive"
            title="Arquivar Acertos" 
            message="Tem certeza que deseja arquivar os acertos dos contatos selecionados? Eles não aparecerão na lista principal até que sejam desarquivados." 
            confirmVariant="primary">
            <x-button type="button" class="w-full sm:w-auto" @click="archiveSelected()">
                Sim, arquivar
            </x-button>
        </x-modal>

        <x-modal 
            name="bulk-unarchive"
            title="Desarquivar Acertos" 
            message="Tem certeza que deseja desarquivar os acertos dos contatos selecionados? Eles voltarão a aparecer na lista principal." 
            confirmVariant="primary">
            <x-button type="button" class="w-full sm:w-auto" @click="unarchiveSelected()">
                Sim, desarquivar
            </x-button>
        </x-modal>

        <x-modal 
            name="share-modal"
            title="Copiar Mensagem" 
            confirmVariant="primary"
            hideFooter="true">
            <x-slot:content>
                <div class="space-y-4">
                    <div>
                        <x-form-select name="pix_key" label="Chave PIX (Opcional)" x-model="selectedPixKey" class="w-full text-sm">
                            <option value="">Sem chave PIX</option>
                            @foreach($pixKeys as $key)
                                <option value="{{ $key }}">{{ $key }}</option>
                            @endforeach
                        </x-form-select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Mensagem</label>
                        <textarea x-model="generatedText" rows="6" class="w-full rounded-lg border-neutral-300 focus:border-neutral-500 focus:ring-neutral-500 text-sm font-mono"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-2">
                        <x-button type="button" @click="window.dispatchEvent(new CustomEvent('modal-close', { detail: 'share-modal' }))" color="outline" class="w-full sm:w-auto">
                            Fechar
                        </x-button>
                        <x-button type="button" @click="window.open(`https://wa.me/?text=${encodeURIComponent(generatedText)}`, '_blank')" color="outline" class="w-full sm:w-auto">
                            <x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-4 text-green-600" />
                            <span>WhatsApp</span>
                        </x-button>
                        <x-button type="button" @click="executeCopy()" color="primary" class="bg-neutral-800 hover:bg-neutral-900 border-neutral-800 text-white w-full sm:w-auto">
                            <span class="flex items-center gap-1.5" x-show="!copied"><x-heroicon-o-clipboard-document class="size-4" /> Copiar</span>
                            <span x-show="copied" x-cloak class="flex items-center gap-1.5 text-green-400"><x-heroicon-o-check class="size-4" /> Copiado!</span>
                        </x-button>
                    </div>
                </div>
            </x-slot:content>
        </x-modal>
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
