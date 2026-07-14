<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item href="{{ route('settlements.history') }}">Histórico Global</x-breadcrumbs.item>
            <x-breadcrumbs.item>Detalhes do Acerto</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="{{ $settlement->description ?: 'Acerto' }}">
            <x-back-button fallback="{{ route('settlements.history') }}" />
            @if(!$settlement->trashed())
                @if(!$settlement->settlement_group_id)
                    <x-delete-modal
                        item-name="este acerto"
                        description="Caso tenha sido gerada uma transação financeira atrelada, ela também será movida para a lixeira."
                        action="{{ route('settlements.destroy', $settlement) }}"
                    />
                    <x-button color="outline" href="{{ route('settlements.edit', $settlement) }}" class="bg-white flex-1 sm:flex-initial">
                        <x-heroicon-o-pencil class="size-4" />
                        <span class="whitespace-nowrap">Editar</span>
                    </x-button>
                @endif
            @endif
    </x-page-header>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
        <!-- Left Column: Items -->
        <div class="lg:col-span-8 flex flex-col gap-4 sm:gap-6">
            <x-card class="border-neutral-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6">
                    <div class="flex items-center gap-4">
                        <x-avatar :model="$settlement->contact" size="lg" />
                        <div>
                            <h2 class="text-lg font-bold text-neutral-900">{{ $settlement->contact->name }}</h2>
                            <a href="{{ route('settlements.contact.show', $settlement->contact_id) }}" class="text-sm font-medium text-accent hover:text-accent-dark transition-colors inline-flex items-center gap-1 mt-0.5">
                                Ver Extrato <x-heroicon-m-arrow-right class="size-3" />
                            </a>
                        </div>
                    </div>
                    
                    <div class="sm:text-right">
                        @php
                            $isPositiveForMe = in_array($settlement->type->value, [\App\Enums\SettlementType::TheyOwe->value, \App\Enums\SettlementType::IPaid->value]);
                            $amountColor = $isPositiveForMe ? 'text-emerald-600' : 'text-red-600';
                            $amountPrefix = $isPositiveForMe ? '+' : '-';
                        @endphp
                        <div class="text-2xl font-bold {{ $amountColor }}">
                            {{ $amountPrefix }} {{ formatCurrency($settlement->amount) }}
                        </div>
                        <div class="mt-1">
                            <x-badge :color="$settlement->type->color()" class="inline-flex">
                                <x-dynamic-component :component="$settlement->type->icon()" class="size-3.5 mr-1.5" />
                                {{ $settlement->type->label() }}
                            </x-badge>
                        </div>
                    </div>
                </div>
                
                @if($settlement->financialTransaction)
                    <div class="mt-8 pt-6 border-t border-neutral-100">
                        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Meio de Pagamento</h3>
                        <div class="flex items-center gap-4">
                            @if($settlement->financialTransaction->invoice)
                                <x-avatar icon="heroicon-o-credit-card" size="lg" />
                                <div>
                                    <div class="font-medium text-neutral-900">{{ $settlement->financialTransaction->invoice->creditCard->name }}</div>
                                    <div class="text-sm text-neutral-500">Cartão de Crédito • Fatura de {{ formatMonthYear($settlement->financialTransaction->invoice->closing_date) }}</div>
                                </div>
                            @elseif($settlement->financialTransaction->account)
                                <x-avatar icon="heroicon-o-building-library" size="lg" />
                                <div>
                                    <div class="font-medium text-neutral-900">{{ $settlement->financialTransaction->account->name }}</div>
                                    <div class="text-sm text-neutral-500">Conta Corrente</div>
                                </div>
                            @else
                                <x-avatar icon="heroicon-o-currency-dollar" size="lg" />
                                <div>
                                    <div class="font-medium text-neutral-900">Transação Avulsa</div>
                                    <div class="text-sm text-neutral-500">Sem conta ou cartão vinculado</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($settlement->hasMedia('attachments'))
                    <x-lightbox class="mt-8 pt-6 border-t border-neutral-100">
                        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Anexos</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($settlement->getMedia('attachments') as $media)
                                @php
                                    $isImage = in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/jpg']);
                                    $fileUrl = route('settlements.attachment', [$settlement, $media, $media->file_name]);
                                @endphp
                                @if($isImage)
                                    <button type="button" class="flex items-center text-left gap-3 p-3 border border-neutral-200 rounded-lg bg-neutral-50 hover:bg-neutral-100 hover:border-neutral-300 transition-colors group w-full cursor-pointer" @click="openLightbox('{{ $fileUrl }}', '{{ $media->file_name }}')">
                                        <x-avatar :image="$fileUrl" class="w-10! h-10!" radius="md" />
                                        <div class="truncate text-sm text-neutral-700">
                                            <div class="truncate font-medium" title="{{ $media->file_name }}">{{ $media->file_name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $media->human_readable_size }}</div>
                                        </div>
                                    </button>
                                @else
                                    <a href="{{ $fileUrl }}" target="_blank"
                                       class="flex items-center gap-3 p-3 border border-neutral-200 rounded-lg bg-neutral-50 hover:bg-neutral-100 hover:border-neutral-300 transition-colors group">
                                        <x-avatar icon="heroicon-o-document" class="w-10! h-10! group-hover:text-neutral-700 transition-colors" radius="md" variant="white" />
                                        <div class="truncate text-sm text-neutral-700">
                                            <div class="truncate font-medium" title="{{ $media->file_name }}">{{ $media->file_name }}</div>
                                            <div class="text-xs text-neutral-500">{{ $media->human_readable_size }}</div>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </x-lightbox>
                @endif
            </x-card>
        </div>

        <!-- Right Column: Meta -->
        <div class="lg:col-span-4 flex flex-col gap-4 sm:gap-6">
            <x-card>
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Detalhes</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-neutral-500 mb-1">Data</div>
                        <div class="font-medium text-neutral-900">{{ formatShort($settlement->date) }}</div>
                    </div>
                    
                    @if($settlement->settlement_group_id)
                        <div class="pt-4 border-t border-neutral-100">
                            <div class="text-xs text-neutral-500 mb-2">Parte de Divisão de Conta</div>
                            <a href="{{ route('settlements.groups.show', $settlement->settlement_group_id) }}" class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 hover:border-neutral-300 transition-colors bg-neutral-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white border border-neutral-200 flex items-center justify-center text-neutral-500">
                                        <x-heroicon-o-users class="size-4" />
                                    </div>
                                    <span class="text-sm font-medium text-neutral-700">Ver Divisão</span>
                                </div>
                                <x-heroicon-m-chevron-right class="size-4 text-neutral-400" />
                            </a>
                        </div>
                    @endif

                    @if($settlement->financialTransaction)
                        <div class="pt-4 border-t border-neutral-100">
                            <div class="text-xs text-neutral-500 mb-2">Transação Financeira</div>
                            <a href="{{ route('financial.transactions.show', $settlement->financialTransaction) }}" class="flex items-center justify-between p-3 rounded-lg border border-neutral-200 hover:border-neutral-300 transition-colors bg-neutral-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white border border-neutral-200 flex items-center justify-center text-neutral-500">
                                        <x-heroicon-o-receipt-percent class="size-4" />
                                    </div>
                                    <span class="text-sm font-medium text-neutral-700">Ver Transação</span>
                                </div>
                                <x-heroicon-m-chevron-right class="size-4 text-neutral-400" />
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-metadata-card :model="$settlement" />
        </div>
    </div>

</x-layouts.app>
