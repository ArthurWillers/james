<x-layouts.financial>
    <form action="{{ route('financial.transactions.store') }}" method="POST" id="transaction-form" enctype="multipart/form-data" @submit="validateForm($event)" x-data='{
        errorMessage: null,
        mode: "{{ old('mode', 'single') }}",
        type: "{{ old('type', 'expense') }}",
        amount: "{{ old('amount') }}",
        date: "{{ old('date', \Carbon\Carbon::today()->format('Y-m-d')) }}",
        payments: {!! json_encode(array_values(old("payments", [
            ['target_type' => 'account', 'financial_account_id' => '', 'financial_credit_card_id' => '', 'amount' => null, 'is_posted' => true]
        ]))) !!},
        addPayment() {
            this.payments.push({ target_type: "account", financial_account_id: "", financial_credit_card_id: "", amount: null, is_posted: true });
        },
        removePayment(index) {
            this.payments.splice(index, 1);
        },
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
        },
        validateForm(e) {
            let total = parseFloat(this.amount ? this.amount.toString().replace(",", ".") : "0") || 0;
            let paymentsTotal = this.payments.reduce((sum, p) => {
                let pAmount = parseFloat(p.amount ? p.amount.toString().replace(",", ".") : "0") || 0;
                return sum + pAmount;
            }, 0);
            
            if (this.payments.length > 1 && Math.abs(total - paymentsTotal) > 0.01) {
                this.errorMessage = "A soma dos meios de pagamento (R$ " + this.formatMoney(paymentsTotal) + ") deve ser exatamente igual ao valor da transação (R$ " + this.formatMoney(total) + ").";
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: "smooth" });
                return false;
            }
            
            this.errorMessage = null;
            return true;
        }
    }' x-effect="if (mode === 'installment' && payments.length > 1) { payments = [payments[0]]; } if (items.length > 0) amount = itemsTotal.toFixed(2); if (date) { let d = new Date(date + 'T00:00:00'); let t = new Date(); t.setHours(0,0,0,0); }">
        @csrf

        <div class="flex justify-between items-center mb-6">
            <x-breadcrumbs>
                <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
                <x-breadcrumbs.item>Nova Transação</x-breadcrumbs.item>
            </x-breadcrumbs>
        </div>

        <x-page-header title="Nova Transação" mobileBottom>
            <x-form-actions fallback="{{ route('financial.transactions.index') }}" form="transaction-form" />
        </x-page-header>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                <div>
                    <h3 class="text-sm font-semibold text-red-800">Foram encontrados os seguintes erros:</h3>
                    <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div x-show="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3" style="display: none;" x-cloak>
            <x-heroicon-o-x-circle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
            <div>
                <h3 class="text-sm font-semibold text-red-800">Atenção</h3>
                <p class="mt-1 text-sm text-red-700" x-text="errorMessage"></p>
            </div>
        </div>

        @include('finance.transactions.partials.form')

        <x-form-actions fallback="{{ route('financial.transactions.index') }}" form="transaction-form" mobile />
    </form>
</x-layouts.financial>
