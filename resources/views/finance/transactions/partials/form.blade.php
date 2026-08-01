<div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
    
    <!-- Left Column: Main Data & Items -->
    <div class="lg:col-span-8 flex flex-col gap-4 sm:gap-6 order-last lg:order-first">
        <!-- Main Data Card -->
        <x-card>
            <div class="flex flex-col gap-4 sm:gap-6">
                <x-form-input label="Descrição" name="description" value="{{ old('description', $transaction->description ?? '') }}" placeholder="Descrição" autofocus />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-start">
                    <div>
                        <x-form-input label="Valor (R$)" name="amount" :currency="true" placeholder="0,00" ::readonly="items.length > 0" ::class="items.length > 0 ? 'bg-neutral-100 text-neutral-500! font-medium' : ''" x-model="amount" />
                        <div class="h-5 mt-1">
                            <p class="text-xs text-primary-600 flex items-center gap-1 font-medium m-0" x-show="items.length > 0"><x-heroicon-o-calculator class="size-3.5"/> Calculado via itens</p>
                        </div>
                    </div>
                    <div>
                        <x-form-input label="Data da Transação" name="date" type="date" value="{{ old('date', isset($transaction) ? $transaction->date->format('Y-m-d') : \Carbon\Carbon::today()->format('Y-m-d')) }}" x-model="date" />
                    </div>
                </div>

                @if(!isset($transaction))
                <div x-show="mode === 'installment'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" style="display: none;">
                    <x-form-input label="Número de Parcelas" name="installments" type="number" min="2" value="{{ old('installments', 2) }}" />
                </div>
                @endif
                
                <div>
                    <x-tags-selector name="tags[]" :options="$tags" label="Tags (Opcional)" :value="old('tags', $defaultTags ?? [])" :primaryValue="old('primary_tag_id', $defaultPrimaryTag ?? null)" xDisablePrimary="items.some(i => i.tags && Object.values(i.tags).length > 0)" />
                </div>
            </div>
        </x-card>

        <x-media.manager :model="isset($transaction) ? $transaction : null" class="mb-6" />

        <!-- Seção de Itens da Transação -->
        <x-finance.transaction-items :tags="$tags" />
    </div>

    <!-- Right Column: Configurações -->
    <div class="lg:col-span-4 flex flex-col order-first lg:order-last">
        <x-card class="space-y-6">
            <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Configurações</h3>

            @if(!isset($transaction))
            {{-- Tipo de Movimento --}}
            <x-radio-block-group legend="Tipo">
                <x-radio-block name="mode" x-model="mode" value="single" icon="heroicon-o-currency-dollar" label="Única" />
                <x-radio-block name="mode" x-model="mode" value="installment" icon="heroicon-o-calendar-days" label="Parcelada" />
            </x-radio-block-group>
            @endif

            {{-- Receita ou Despesa --}}
            <x-radio-block-group legend="Classificação">
                <x-radio-block name="type" x-model="type" value="expense" icon="heroicon-o-arrow-trending-down" label="Despesa" activeClass="peer-checked:text-red-600" inactiveClass="text-red-600 hover:text-red-700" />
                <x-radio-block name="type" x-model="type" value="income" icon="heroicon-o-arrow-trending-up" label="Receita" activeClass="peer-checked:text-green-600" inactiveClass="text-green-600 hover:text-green-700" />
            </x-radio-block-group>

            {{-- Formas de Pagamento --}}
            <div class="space-y-4 pt-2">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-semibold text-neutral-700">Formas de Pagamento</h4>
                    <button type="button" @click="addPayment()" x-show="mode !== 'installment'" class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1 focus:outline-none cursor-pointer">
                        <x-heroicon-o-plus class="size-3.5"/> Adicionar Meio de Pagamento
                    </button>
                </div>

                <template x-for="(payment, index) in payments" :key="index">
                    <div class="p-3 bg-neutral-50 rounded-lg border border-neutral-100 space-y-3 relative group">
                        <button type="button" @click="removePayment(index)" x-show="payments.length > 1" class="absolute top-2 right-2 text-neutral-400 hover:text-red-500 lg:hidden group-hover:block bg-neutral-100 hover:bg-neutral-200 rounded-full p-1 transition-colors cursor-pointer" title="Remover meio de pagamento">
                            <x-heroicon-o-trash class="size-4" />
                        </button>

                        <template x-if="payment.id">
                            <input type="hidden" x-bind:name="'payments['+index+'][id]'" x-model="payment.id">
                        </template>

                        <x-radio-block-group legend="Onde">
                            <x-radio-block name="" x-bind:name="'payments['+index+'][target_type]'" x-model="payment.target_type" value="account" icon="heroicon-o-building-library" label="Conta" />
                            <x-radio-block name="" x-bind:name="'payments['+index+'][target_type]'" x-model="payment.target_type" value="card" icon="heroicon-o-credit-card" label="Cartão" />
                        </x-radio-block-group>
                        
                        <div>
                            <div x-show="payment.target_type === 'account'">
                                <x-form-select name="" x-bind:name="'payments['+index+'][financial_account_id]'" x-model="payment.financial_account_id">
                                    <option value="">Selecione uma conta...</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </x-form-select>
                            </div>
                            <div x-show="payment.target_type === 'card'" style="display: none;">
                                <x-form-select name="" x-bind:name="'payments['+index+'][financial_credit_card_id]'" x-model="payment.financial_credit_card_id">
                                    <option value="">Selecione um cartão...</option>
                                    @foreach($cards as $card)
                                        <option value="{{ $card->id }}">{{ $card->name }}</option>
                                    @endforeach
                                </x-form-select>
                            </div>
                        </div>

                        <!-- Valor Parcial -->
                        <div x-show="payments.length > 1">
                            <x-form-input label="Valor Parcial (R$)" name="" x-bind:name="'payments['+index+'][amount]'" :currency="true" placeholder="0,00" x-model="payment.amount" />
                        </div>
                        

                    </div>
                </template>
            </div>

            <div class="pt-4 border-t border-neutral-100" x-show="payments.some(p => p.target_type === 'account')" x-cloak>
                <x-form.switch label="Transação Efetivada?" name="is_posted" :checked="old('is_posted', isset($transaction) ? $transaction->is_posted : true)" />
                <p class="text-xs text-neutral-500 mt-1">Se desmarcado, os pagamentos via conta corrente ficarão pendentes.</p>
            </div>
        </x-card>
    </div>
</div>
