<x-layouts.app>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <x-breadcrumbs>
            <x-breadcrumbs.item href="{{ route('settlements.index') }}">Acertos</x-breadcrumbs.item>
            <x-breadcrumbs.item>Dividir Conta</x-breadcrumbs.item>
        </x-breadcrumbs>
    </div>

    <x-page-header title="Dividir Conta">
        <div class="flex items-center gap-3">
            <x-button color="outline" href="{{ route('settlements.index') }}" class="bg-white">
                Cancelar
            </x-button>
            <x-button type="submit" form="split-form" class="bg-neutral-900 hover:bg-black text-white">
                Dividir Conta
            </x-button>
        </div>
    </x-page-header>

    @php
        $alpineContacts = collect($contacts)->values()->map(fn($c, $index) => [
            'id' => $c->id,
            'name' => $c->name,
            'avatar_url' => $c->avatar,
            'initials' => $c->initials(),
            'amount' => old('contacts.' . $index . '.amount', ''),
        ]);
    @endphp

    <div class="mt-6">
        <form action="{{ route('settlements.groups.store') }}" method="POST" id="split-form" x-data="{
            mode: '{{ old('mode', 'equal') }}',
            totalAmount: '{{ old('total_amount', '') }}',
            myAmount: '{{ old('my_amount', '') }}',
            createTransaction: {{ old('create_transaction', 'true') == '1' || old('create_transaction', 'true') === 'true' ? 'true' : 'false' }},
            targetType: '{{ old('targetType', 'account') }}',
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
            @include('settlements.groups.partials.form')
        </form>
    </div>
</x-layouts.app>
