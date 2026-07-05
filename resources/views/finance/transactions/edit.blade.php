<x-layouts.financial>
    @php
        $defaultItems = $transaction->items->map(function ($i) {
            return [
                'id' => $i->id,
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => number_format($i->unit_price, 2, ',', ''),
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
        amount: "{{ old('amount', number_format(abs($transaction->amount), 2, ',', '')) }}",
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
    }' x-effect="if (items.length > 0) amount = formatMoney(itemsTotal); if (date) { let d = new Date(date + 'T00:00:00'); let t = new Date(); t.setHours(0,0,0,0); if (d > t) $dispatch('uncheck-posted-edit') }">
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

        <!-- Header with Actions -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-neutral-900">Editar Transação</h1>
            <div class="flex items-center gap-3">
                <x-button color="outline" href="{{ route('financial.transactions.show', $transaction->id) }}" class="bg-white">Cancelar</x-button>
                <x-button type="submit" form="transaction-form" class="bg-neutral-900 hover:bg-black text-white">Salvar Alterações</x-button>
            </div>
        </div>

        @include('finance.transactions.partials.form', ['transaction' => $transaction])
    </form>
</x-layouts.financial>
