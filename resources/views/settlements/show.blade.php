<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>{{ $contact->name }}</x-breadcrumbs.item>
        </x-breadcrumbs>

        <div class="flex items-center gap-2">
            <x-modal.trigger name="share-modal-{{ $contact->id }}">
                <x-button type="button" color="outline">
                    <x-heroicon-o-share class="size-4" />
                    <span>Compartilhar</span>
                </x-button>
            </x-modal.trigger>

            <x-modal 
                name="share-modal-{{ $contact->id }}"
                title="Copiar Mensagem" 
                confirmVariant="primary"
                hideFooter="true">
                <x-slot:content>
                    <div class="space-y-4" x-data="{
                        baseText: {{ Js::from($baseMessageText) }},
                        selectedPixKey: '{{ $pixKeys->first() ?? '' }}',
                        copied: false,
                        
                        get generatedText() {
                            if (this.selectedPixKey && {{ $netBalance > 0 ? 'true' : 'false' }}) {
                                return this.baseText + `Chave PIX: *${this.selectedPixKey}*\n`;
                            }
                            return this.baseText;
                        },
                        
                        async copyText() {
                            try {
                                await navigator.clipboard.writeText(this.generatedText);
                                this.copied = true;
                                setTimeout(() => {
                                    this.copied = false;
                                    window.dispatchEvent(new CustomEvent('modal-close', { detail: 'share-modal-{{ $contact->id }}' }));
                                }, 1500);
                            } catch (e) {
                                console.error('Failed to copy text', e);
                            }
                        }
                    }">
                        <div x-show="netBalance > 0">
                            <x-form-select name="pix_key" label="Chave PIX (Opcional)" x-model="selectedPixKey" class="w-full text-sm">
                                <option value="">Sem chave PIX</option>
                                @foreach($pixKeys as $key)
                                    <option value="{{ $key }}">{{ $key }}</option>
                                @endforeach
                            </x-form-select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Mensagem</label>
                            <textarea :value="generatedText" readonly rows="6" class="w-full rounded-lg border-neutral-300 focus:border-neutral-500 focus:ring-neutral-500 text-sm font-mono bg-neutral-50"></textarea>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-2">
                            <x-button type="button" @click="window.dispatchEvent(new CustomEvent('modal-close', { detail: 'share-modal-{{ $contact->id }}' }))" color="outline" class="w-full sm:w-auto">
                                Fechar
                            </x-button>
                            <x-button type="button" @click="window.open(`https://wa.me/?text=${encodeURIComponent(generatedText)}`, '_blank')" color="outline" class="w-full sm:w-auto">
                                <x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-4 text-green-600" />
                                <span>WhatsApp</span>
                            </x-button>
                            <x-button type="button" @click="copyText()" color="primary" class="bg-neutral-800 hover:bg-neutral-900 border-neutral-800 text-white w-full sm:w-auto">
                                <span x-show="!copied" class="flex items-center gap-1.5"><x-heroicon-o-clipboard-document class="size-4" /> Copiar</span>
                                <span x-show="copied" x-cloak class="flex items-center gap-1.5 text-green-400"><x-heroicon-o-check class="size-4" /> Copiado!</span>
                            </x-button>
                        </div>
                    </div>
                </x-slot:content>
            </x-modal>

            <x-modal.trigger name="archive-contact-{{ $contact->id }}">
                <x-button type="button" color="outline" class="bg-white">
                    <x-heroicon-o-archive-box class="size-4" />
                    <span class="hidden sm:inline">Arquivar</span>
                </x-button>
            </x-modal.trigger>

            <x-modal 
                name="archive-contact-{{ $contact->id }}"
                title="Arquivar Acertos" 
                message="Tem certeza que deseja arquivar os acertos com este contato? Ele não aparecerá na lista principal até que seja desarquivado." 
                confirmVariant="primary">
                <form action="{{ route('settlements.archive') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="contact_ids[]" value="{{ $contact->id }}">
                    <x-button type="submit" class="w-full sm:w-auto">
                        Sim, arquivar
                    </x-button>
                </form>
            </x-modal>
            
            @if($settleUrl)
                <x-button href="{{ $settleUrl }}" color="outline" class="bg-white">
                    <x-heroicon-o-check-circle class="size-4" />
                    <span>Quitar Dívida</span>
                </x-button>
            @endif

            <x-button href="{{ route('settlements.create', $contact) }}">
                <x-heroicon-o-plus class="size-4" />
                <span>Novo Lançamento</span>
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <!-- Contact Header -->
        <x-ui.card href="{{ route('contacts.show', $contact->id) }}" class="flex items-center gap-4 h-full">
            <x-ui.avatar :model="$contact" size="xl" />
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">{{ $contact->name }}</h1>
                <div class="flex items-center gap-2 text-neutral-500 text-sm mt-1">
                    <span>{{ $contact->relationship_category ?? 'Contato' }}</span>
                    @if($contact->phones && count($contact->phones) > 0)
                        <x-heroicon-m-minus class="size-3 text-neutral-300" />
                        <span class="flex items-center gap-1">
                            <x-heroicon-o-phone class="size-3.5" />
                            {{ collect($contact->phones)->first()['value'] ?? '' }}
                        </span>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <!-- Acertos KPI Card -->
        <x-finance.kpi-card
            title="Saldo Líquido"
            value="{{ formatCurrency(abs($netBalance)) }}"
            icon="heroicon-o-scale"
            :color="$netBalance > 0 ? 'green' : ($netBalance < 0 ? 'red' : 'neutral')"
            class="h-full"
        >
            {{ $netBalance > 0 ? 'Você tem a receber' : ($netBalance < 0 ? 'Você tem a pagar' : 'Tudo quitado') }}
        </x-finance.kpi-card>
    </div>

    <!-- Ledger Table -->
    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Histórico de Transações</h2>
    
    <div class="mb-12">
        <x-ui.table>
            <x-ui.table.header class="hidden sm:grid grid-cols-5">
                <x-ui.table.column>Data</x-ui.table.column>
                <x-ui.table.column>Descrição</x-ui.table.column>
                <x-ui.table.column>Tipo</x-ui.table.column>
                <x-ui.table.column>Pagamento</x-ui.table.column>
                <x-ui.table.column class="text-right">Valor</x-ui.table.column>
            </x-ui.table.header>

            <x-ui.table.body>
                @forelse($settlements as $settlement)
                    @php
                        $isPositiveForMe = in_array($settlement->type->value, [\App\Enums\SettlementType::TheyOwe->value, \App\Enums\SettlementType::IPaid->value]);
                        $amountColor = $isPositiveForMe ? 'text-emerald-600' : 'text-red-600';
                        $amountPrefix = $isPositiveForMe ? '+' : '-';
                    @endphp

                    <x-ui.table.row href="{{ $settlement->settlement_group_id ? route('settlements.groups.show', $settlement->settlement_group_id) : route('settlements.show_item', $settlement) }}" class="hidden sm:grid grid-cols-5">
                        <x-ui.table.cell class="text-neutral-500">
                            {{ formatShort($settlement->date) }}
                        </x-ui.table.cell>
                        
                        <x-ui.table.cell>
                            <span class="text-neutral-700 font-medium truncate">{{ $settlement->description }}</span>
                        </x-ui.table.cell>

                        <x-ui.table.cell>
                            <x-ui.badge :color="$settlement->type->color()" class="flex items-center gap-1 w-fit">
                                <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5" />
                                <span>{{ $settlement->type->label() }}</span>
                            </x-ui.badge>
                        </x-ui.table.cell>

                        <x-ui.table.cell class="text-neutral-500 text-sm">
                            @php
                                $transaction = $settlement->financialTransaction ?? $settlement->group?->financialTransaction;
                            @endphp
                            @if($transaction)
                                @if($transaction->invoice)
                                    <div class="flex items-center gap-1.5 truncate text-neutral-600">
                                        <x-heroicon-o-credit-card class="size-4 shrink-0" />
                                        <span class="truncate">{{ $transaction->invoice->creditCard->name }}</span>
                                    </div>
                                @elseif($transaction->account)
                                    <div class="flex items-center gap-1.5 truncate text-neutral-600">
                                        <x-heroicon-o-building-library class="size-4 shrink-0" />
                                        <span class="truncate">{{ $transaction->account->name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5 truncate text-neutral-600">
                                        <x-heroicon-o-currency-dollar class="size-4 shrink-0" />
                                        <span class="truncate">Transação</span>
                                    </div>
                                @endif
                            @else
                                <span class="text-neutral-300">-</span>
                            @endif
                        </x-ui.table.cell>

                        <x-ui.table.cell class="text-right font-semibold {{ $amountColor }}">
                            {{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}
                        </x-ui.table.cell>

                        <!-- Mobile View -->
                        <x-slot:mobile>
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex flex-col gap-1 min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge :color="$settlement->type->color()" class="flex items-center shrink-0 w-fit">
                                            <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5" />
                                        </x-ui.badge>
                                        <span class="font-medium text-neutral-900 truncate">{{ $settlement->description }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-neutral-500">
                                        <span>{{ formatShort($settlement->date) }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-semibold {{ $amountColor }}">{{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}</span>
                                    <div class="text-xs text-neutral-500 mt-0.5">{{ $settlement->type->label() }}</div>
                                </div>
                            </div>
                        </x-slot:mobile>
                    </x-ui.table.row>
                @empty
                    <div class="col-span-full">
                        <x-ui.empty-state 
                            icon="heroicon-o-queue-list" 
                            title="Nenhuma transação" 
                            description="Você ainda não registrou nenhum acerto com este contato." 
                        />
                    </div>
                @endforelse
            </x-ui.table.body>
        </x-ui.table>

        <div class="mt-6">
            {{ $settlements->links() }}
        </div>
    </div>
</x-layouts.app>
