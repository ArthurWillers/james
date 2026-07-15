<x-layouts.financial>
    <form action="{{ route('financial.transactions.store') }}" method="POST" id="transaction-form" enctype="multipart/form-data" x-data='{
        mode: "{{ old('mode', 'single') }}",
        type: "{{ old('type', 'expense') }}",
        targetType: "{{ old('targetType', 'account') }}",
        amount: "{{ old('amount') }}",
        date: "{{ old('date', \Carbon\Carbon::today()->format('Y-m-d')) }}",
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
    }' x-effect="if (items.length > 0) amount = itemsTotal.toFixed(2); if (date) { let d = new Date(date + 'T00:00:00'); let t = new Date(); t.setHours(0,0,0,0); if (d > t) $dispatch('uncheck-posted') }">
        @csrf
        <input type="hidden" name="targetType" x-model="targetType">

        <div class="flex justify-between items-center mb-6">
            <x-breadcrumbs>
                <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
                <x-breadcrumbs.item>Nova Transação</x-breadcrumbs.item>
            </x-breadcrumbs>
        </div>

        <x-page-header title="Nova Transação" mobileBottom>
            <x-form-actions fallback="{{ route('financial.transactions.index') }}" form="transaction-form" />
        </x-page-header>

        @include('finance.transactions.partials.form')

        <x-form-actions fallback="{{ route('financial.transactions.index') }}" form="transaction-form" mobile />
    </form>
</x-layouts.financial>
