<x-layouts.financial>
    <form action="{{ route('financial.transactions.store') }}" method="POST" id="transaction-form" x-data='{
        mode: "{{ old('mode', 'single') }}",
        type: "{{ old('type', 'expense') }}",
        targetType: "{{ old('targetType', 'account') }}",
        amount: "{{ old('amount') }}",
        items: {!! json_encode(array_values(old("items", []))) !!},
        addItem() {
            this.items.push({ description: "", quantity: 1, unit_price: "" });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        get itemsTotal() {
            return this.items.reduce((total, item) => {
                let qStr = item.quantity ? item.quantity.toString().replace(",", ".") : "0";
                let q = parseFloat(qStr) || 0;
                let val = item.unit_price ? item.unit_price.toString().replace(",", ".") : "0";
                let p = parseFloat(val) || 0;
                return total + (q * p);
            }, 0);
        },
        formatMoney(value) {
            let options = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
            return value.toLocaleString("pt-BR", options);
        }
    }' x-effect="if (items.length > 0) amount = formatMoney(itemsTotal)">
        @csrf
        <input type="hidden" name="targetType" x-model="targetType">

        <!-- Header with Actions -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-neutral-900">Nova Transação</h1>
            <div class="flex items-center gap-3">
                <x-button color="outline" href="{{ route('financial.transactions.index') }}" class="bg-white">Cancelar</x-button>
                <x-button type="submit" form="transaction-form" class="bg-neutral-900 hover:bg-black text-white">Salvar</x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Main Data & Items -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- Main Data Card -->
                <x-card class="p-6">
                    <div class="flex flex-col gap-6">
                        <x-form-input label="Descrição" name="description" value="{{ old('description') }}" placeholder="Descrição" autofocus />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div>
                                <x-form-input label="Valor (R$)" name="amount" x-model="amount" :numeric="true" placeholder="0,00" ::readonly="items.length > 0" ::class="items.length > 0 ? 'bg-neutral-100 !text-neutral-500 font-medium' : ''" />
                                <div class="h-5 mt-1">
                                    <p x-show="items.length > 0" class="text-xs text-primary-600 flex items-center gap-1 font-medium m-0"><x-heroicon-o-calculator class="size-3.5"/> Calculado via itens</p>
                                </div>
                            </div>
                            <div>
                                <x-form-input label="Data da Transação" name="date" type="date" value="{{ old('date', \Carbon\Carbon::today()->format('Y-m-d')) }}" />
                            </div>
                        </div>

                        <div x-show="mode === 'installment'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" style="display: none;">
                            <x-form-input label="Número de Parcelas" name="installments" type="number" min="2" value="{{ old('installments', 2) }}" />
                        </div>
                    </div>
                </x-card>

                <!-- Seção de Itens da Transação -->
                <x-finance.transaction-items />
            </div>

            <!-- Right Column: Configurações -->
            <div class="lg:col-span-4 flex flex-col">
                <x-card class="p-6 space-y-6">
                    <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Configurações</h3>

                    {{-- Tipo de Movimento --}}
                    <x-form.radio-block-group legend="Tipo">
                        <x-form.radio-block name="mode" x-model="mode" value="single" icon="heroicon-o-currency-dollar" label="Única" />
                        <x-form.radio-block name="mode" x-model="mode" value="installment" icon="heroicon-o-calendar-days" label="Parcelada" />
                    </x-form.radio-block-group>

                    {{-- Receita ou Despesa --}}
                    <x-form.radio-block-group legend="Classificação">
                        <x-form.radio-block name="type" x-model="type" value="expense" icon="heroicon-o-arrow-trending-down" label="Despesa" activeClass="peer-checked:text-red-600" inactiveClass="text-red-600 hover:text-red-700" />
                        <x-form.radio-block name="type" x-model="type" value="income" icon="heroicon-o-arrow-trending-up" label="Receita" activeClass="peer-checked:text-green-600" inactiveClass="text-green-600 hover:text-green-700" />
                    </x-form.radio-block-group>

                    {{-- Conta ou Cartão --}}
                    <div class="space-y-4 pt-2">
                        <x-form.radio-block-group legend="Onde">
                            <x-form.radio-block name="targetType_dummy" x-model="targetType" value="account" icon="heroicon-o-building-library" label="Conta" activeClass="peer-checked:text-neutral-900 peer-checked:ring-2 peer-checked:ring-primary-500" class="ring-1 ring-neutral-200" />
                            <x-form.radio-block name="targetType_dummy" x-model="targetType" value="card" icon="heroicon-o-credit-card" label="Cartão" activeClass="peer-checked:text-neutral-900 peer-checked:ring-2 peer-checked:ring-primary-500" class="ring-1 ring-neutral-200" />
                        </x-form.radio-block-group>
                        
                        <div>
                            <div x-show="targetType === 'account'">
                                <x-form-select name="financial_account_id">
                                    <option value="">Selecione uma conta...</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('financial_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                    @endforeach
                                </x-form-select>
                            </div>
                            <div x-show="targetType === 'card'" style="display: none;">
                                <x-form-select name="financial_credit_card_id">
                                    <option value="">Selecione um cartão...</option>
                                    @foreach($cards as $card)
                                        <option value="{{ $card->id }}" {{ old('financial_credit_card_id') == $card->id ? 'selected' : '' }}>{{ $card->name }}</option>
                                    @endforeach
                                </x-form-select>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</x-layouts.financial>
