<x-layouts.financial>
    @php
        $defaultItems = $transaction->items->map(function ($i) {
            return [
                'id' => $i->id,
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => number_format($i->unit_price, 2, '.', ''),
                'tags' => $i->tags->pluck('id')->toArray(),
                'primary_tag_id' => $i->tags->firstWhere('pivot.is_primary', true)?->id,
            ];
        })->toArray();
        $itemsJson = json_encode(array_values(old('items', $defaultItems)));
        
        $defaultTags = $transaction->tags->pluck('id')->toArray();
        $defaultPrimaryTag = $transaction->tags->firstWhere('pivot.is_primary', true)?->id;
    @endphp
    <form action="{{ route('financial.transactions.update', $transaction->id) }}" method="POST" id="transaction-form" x-data='{
        type: "{{ old('type', $transaction->type) }}",
        targetType: "{{ old('targetType', $transaction->invoice ? 'card' : 'account') }}",
        amount: "{{ old('amount', number_format(abs($transaction->amount), 2, '.', '')) }}",
        date: "{{ old('date', $transaction->date->format('Y-m-d')) }}",
        items: {!! $itemsJson !!},
        addItem() {
            this.items.push({ description: "", quantity: 1, unit_price: "", tags: [] });
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
    }' x-effect="if (items.length > 0) amount = itemsTotal.toFixed(2); if (date) { let d = new Date(date + 'T00:00:00'); let t = new Date(); t.setHours(0,0,0,0); if (d > t) $dispatch('uncheck-posted-edit') }">
        @csrf
        @method('PUT')
        <input type="hidden" name="targetType" x-model="targetType">

        <div class="flex justify-between items-center mb-6">
            <x-breadcrumbs>
                <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
                <x-breadcrumbs.item href="{{ route('financial.transactions.show', $transaction->id) }}">#{{ $transaction->id }}</x-breadcrumbs.item>
                <x-breadcrumbs.item>Editar</x-breadcrumbs.item>
            </x-breadcrumbs>
        </div>

        <x-page-header title="Editar Transação" mobileBottom>
            <x-form-actions fallback="{{ route('financial.transactions.show', $transaction->id) }}" form="transaction-form" submitText="Salvar Alterações" />
        </x-page-header>

        @include('finance.transactions.partials.form', ['transaction' => $transaction])

        <x-form-actions fallback="{{ route('financial.transactions.show', $transaction->id) }}" form="transaction-form" submitText="Salvar Alterações" mobile />
    </form>
</x-layouts.financial>
