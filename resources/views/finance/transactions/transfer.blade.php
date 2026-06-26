<x-layouts.financial>
    <form action="{{ route('financial.transactions.transfer.store') }}" method="POST" id="transfer-form" x-data="{
        amount: '',
        formatMoney(value) {
            return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }">
        @csrf

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <x-breadcrumbs class="mb-2">
                    <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
                    <x-breadcrumbs.item>Nova Transferência</x-breadcrumbs.item>
                </x-breadcrumbs>
                <h1 class="text-2xl font-bold text-neutral-900">Nova Transferência</h1>
            </div>
            <div class="flex items-center gap-3">
                <x-button color="outline" href="{{ route('financial.transactions.index') }}" class="bg-white">Cancelar</x-button>
                <x-button type="submit" form="transfer-form" class="bg-neutral-900 hover:bg-black text-white">Salvar Transferência</x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Left Column --}}
            <div class="lg:col-span-8 flex flex-col gap-6">

                {{-- Dados Principais --}}
                <x-card class="p-6">
                    <div class="flex flex-col gap-6">
                        <x-form-input label="Descrição" name="description" value="{{ old('description', 'Transferência entre contas') }}" placeholder="Ex: Transferência poupança" autofocus />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form-input label="Valor (R$)" name="amount" x-model="amount" :numeric="true" placeholder="0,00" />
                            <x-form-input label="Data" name="date" type="date" value="{{ old('date', \Carbon\Carbon::today()->format('Y-m-d')) }}" />
                        </div>
                    </div>
                </x-card>

                {{-- Taxa/Imposto --}}
                <x-card class="p-6">
                    <div class="mb-4">
                        <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Taxa / IOF / Imposto</h3>
                        <p class="text-sm text-neutral-500 mt-0.5">Opcional. Se preenchido, será debitado da conta de origem.</p>
                    </div>

                    <x-form-input label="Valor da Taxa (R$)" name="fee_amount" :numeric="true" placeholder="0,00" value="{{ old('fee_amount') }}" />
                </x-card>
            </div>

            {{-- Right Column --}}
            <div class="lg:col-span-4 flex flex-col gap-6">
                <x-card class="p-6 space-y-6">
                    <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-widest mb-4">Contas</h3>

                    <div class="flex flex-col gap-4">
                        <div>
                            <x-form-select name="from_account_id" label="Conta de Origem">
                                <option value="">Selecione a conta de origem...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </x-form-select>
                            @error('from_account_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-px bg-neutral-200"></div>
                            <div class="shrink-0 flex items-center justify-center size-8 rounded-full bg-neutral-100 text-neutral-500">
                                <x-heroicon-o-arrow-down class="size-4" />
                            </div>
                            <div class="flex-1 h-px bg-neutral-200"></div>
                        </div>

                        <div>
                            <x-form-select name="to_account_id" label="Conta de Destino">
                                <option value="">Selecione a conta de destino...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </x-form-select>
                            @error('to_account_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('from_account_id')
                                @if(str_contains($message, 'different') || str_contains($message, 'diferente'))
                                    <p class="mt-1 text-sm text-red-600">A conta de destino deve ser diferente da origem.</p>
                                @endif
                            @enderror
                        </div>
                    </div>
                </x-card>

                {{-- Info --}}
                <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-500 space-y-2">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-information-circle class="size-4 mt-0.5 shrink-0 text-neutral-400" />
                        <p>Uma transferência cria duas transações vinculadas: uma <strong class="text-neutral-700">saída</strong> na conta de origem e uma <strong class="text-neutral-700">entrada</strong> na conta de destino.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-layouts.financial>
