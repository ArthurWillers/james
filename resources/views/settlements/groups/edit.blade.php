<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Editar Divisão</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Editar Divisão de Conta">
        <div class="flex items-center gap-2">
            <x-button color="outline" href="{{ route('settlements.index') }}" class="bg-white">
                Cancelar
            </x-button>
            <x-button type="submit" form="split-form" class="bg-neutral-900 hover:bg-black text-white">
                Salvar
            </x-button>
        </div>
    </x-page-header>



    @php
        $existingTagIds = [];
        $existingPrimaryTagId = null;
        if ($settlementGroup->financialTransaction) {
            $myItem = $settlementGroup->financialTransaction->items->firstWhere('description', 'Minha Parte');
            if ($myItem) {
                $existingTagIds = $myItem->tags->pluck('id')->toArray();
                $existingPrimaryTagId = $myItem->tags->firstWhere('pivot.is_primary', true)?->id;
            }
        }
        
        $existingContacts = $settlementGroup->settlements->map(fn($s) => [
            'id' => $s->contact->id,
            'name' => $s->contact->name,
            'avatar_url' => $s->contact->avatar,
            'initials' => $s->contact->initials(),
            'amount' => number_format($s->amount, 2, '.', ''),
        ])->values();
        
        $existingMyAmount = $settlementGroup->total_amount - $settlementGroup->settlements->sum('amount');

        $alpineContacts = old('contacts') ? collect(old('contacts'))->map(fn($c, $i) => [
            'id' => $c['id'],
            'name' => $existingContacts[$i]['name'] ?? '',
            'avatar_url' => $existingContacts[$i]['avatar_url'] ?? null,
            'initials' => $existingContacts[$i]['initials'] ?? '',
            'amount' => $c['amount'] ?? '',
        ])->values() : $existingContacts;
    @endphp

    <div class="mt-6">
        <form action="{{ route('settlements.groups.update', $settlementGroup) }}" method="POST" id="split-form" x-data="{
            mode: '{{ old('mode', $settlementGroup->mode) }}',
            totalAmount: '{{ old('total_amount', number_format($settlementGroup->total_amount, 2, '.', '')) }}',
            myAmount: '{{ old('my_amount', number_format($existingMyAmount, 2, '.', '')) }}',
            createTransaction: {{ old('create_transaction', $settlementGroup->financial_transaction_id ? 'true' : 'false') == '1' || old('create_transaction', $settlementGroup->financial_transaction_id ? 'true' : 'false') === 'true' ? 'true' : 'false' }},
            targetType: '{{ old('targetType', optional($settlementGroup->financialTransaction)->invoice ? 'card' : 'account') }}',
            contacts: {{ json_encode($alpineContacts) }},

            get totalPeople() {
                return this.contacts.length + 1;
            },

            get sharePerPerson() {
                let total = parseFloat(this.totalAmount) || 0;
                if (this.totalPeople <= 0 || total <= 0) return 0;
                return Math.floor((total * 100) / this.totalPeople) / 100;
            },

            get calculatedMyAmount() {
                if (this.mode === 'equal') {
                    let total = parseFloat(this.totalAmount) || 0;
                    let contactsShare = this.sharePerPerson * this.contacts.length;
                    return Math.round((total - contactsShare) * 100) / 100;
                }
                return parseFloat(this.myAmount) || 0;
            },

            get calculatedTotal() {
                if (this.mode === 'exact') {
                    let sum = parseFloat(this.myAmount) || 0;
                    this.contacts.forEach(c => {
                        sum += parseFloat(c.amount) || 0;
                    });
                    return Math.round(sum * 100) / 100;
                }
                return parseFloat(this.totalAmount) || 0;
            },

            formatMoney(value) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
            },

            init() {
                this.$watch('mode', () => {
                    if (this.mode === 'equal') {
                        this.recalcEqualShares();
                    }
                });
                this.$watch('totalAmount', () => {
                    if (this.mode === 'equal') {
                        this.recalcEqualShares();
                    }
                });
            },

            recalcEqualShares() {
                let total = parseFloat(this.totalAmount) || 0;
                if (total <= 0 || this.totalPeople <= 0) {
                    this.contacts.forEach(c => c.amount = '');
                    this.myAmount = '';
                    return;
                }
                let share = this.sharePerPerson;
                this.contacts.forEach(c => c.amount = share.toFixed(2));
                this.myAmount = this.calculatedMyAmount.toFixed(2);
            }
        }">
            @csrf
            @method('PUT')
            @include('settlements.groups.partials.form')
        </form>
    </div>
</x-layouts.app>
