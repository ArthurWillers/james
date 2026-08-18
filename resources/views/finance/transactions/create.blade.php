<x-layouts.financial>
    <form action="{{ route('financial.transactions.store') }}" method="POST" id="transaction-form" enctype="multipart/form-data" x-data="{
        mode: {{ Js::from(old('mode', 'single')) }},
        type: {{ Js::from(old('type', 'expense')) }},
        targetType: {{ Js::from(old('targetType', 'account')) }},
        amount: {{ Js::from(old('amount')) }},
        date: {{ Js::from(old('date', \Carbon\Carbon::today()->format('Y-m-d'))) }},
        items: {{ Js::from(array_values(old('items', []))) }},
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: '' });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        get itemsTotal() {
            return this.items.reduce((total, item) => {
                let qStr = item.quantity ? item.quantity.toString().replace(',', '.') : '0';
                let q = parseFloat(qStr) || 0;
                let val = item.unit_price ? item.unit_price.toString().replace(',', '.') : '0';
                let p = parseFloat(val) || 0;
                return total + (q * p);
            }, 0);
        },
        formatMoney(value) {
            let options = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
            return value.toLocaleString('pt-BR', options);
        }
    }" x-effect="if (items.length > 0) amount = itemsTotal.toFixed(2); if (date) { let d = new Date(date + 'T00:00:00'); let t = new Date(); t.setHours(0,0,0,0); if (d > t) $dispatch('uncheck-posted') }">
        @csrf
        <input type="hidden" name="targetType" x-model="targetType">

        <div class="flex justify-between items-center mb-6">
            <x-breadcrumbs>
                <x-breadcrumbs.item href="{{ route('financial.transactions.index') }}">Transações</x-breadcrumbs.item>
                <x-breadcrumbs.item>Nova Transação</x-breadcrumbs.item>
            </x-breadcrumbs>
        </div>

        <x-page-header title="Nova Transação" mobileBottom>
            <x-modal.trigger name="nfce-import-modal">
                <x-button type="button" color="outline" class="w-full sm:w-auto">
                    <x-heroicon-o-document-arrow-down class="size-4" />
                    <span>Importar NFC-e</span>
                </x-button>
            </x-modal.trigger>
            <x-form-actions fallback="{{ route('financial.transactions.index') }}" form="transaction-form" />
        </x-page-header>

        @include('finance.transactions.partials.form')

        <div class="flex md:hidden mt-6">
            <x-modal.trigger name="nfce-import-modal" class="w-full">
                <x-button type="button" color="outline" class="w-full">
                    <x-heroicon-o-document-arrow-down class="size-4" />
                    <span>Importar NFC-e</span>
                </x-button>
            </x-modal.trigger>
        </div>

        <x-form-actions fallback="{{ route('financial.transactions.index') }}" form="transaction-form" mobile />
    </form>

    <x-modal name="nfce-import-modal" title="Importar NFC-e" confirmVariant="none">
        <form action="{{ route('financial.transactions.nfce.import') }}" method="POST" class="mt-4 space-y-4" x-data="{
            loading: false,
            url: @js(old('url', '')),
            pasteError: '',
            async pasteUrl() {
                this.pasteError = '';

                if (! window.isSecureContext) {
                    this.pasteError = 'A colagem automática exige HTTPS. Abra a aplicação pelo endereço seguro ou cole a URL manualmente.';
                    return;
                }

                if (! navigator.clipboard?.readText) {
                    this.pasteError = 'Seu navegador não permite ler a área de transferência automaticamente.';
                    return;
                }

                try {
                    this.url = (await navigator.clipboard.readText()).trim();
                } catch (error) {
                    this.pasteError = error?.name === 'NotAllowedError'
                        ? 'O navegador bloqueou o acesso à área de transferência. Permita o acesso para este site ou cole a URL manualmente.'
                        : 'Não foi possível acessar a área de transferência. Cole a URL manualmente.';
                }
            }
        }" @submit="if (loading) { $event.preventDefault(); return; } loading = true">
            @csrf

            <x-form-input
                label="URL pública da NFC-e"
                name="url"
                type="url"
                x-model="url"
                placeholder="https://..."
                autocomplete="url"
            />

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <x-button type="button" color="outline" class="w-full" @click="pasteUrl()">
                    <x-heroicon-o-clipboard-document class="size-4" />
                    Colar URL
                </x-button>
                <p class="text-xs text-red-600" x-show="pasteError" x-text="pasteError"></p>
            </div>

            <p class="text-sm text-neutral-500">Cole a URL exibida no portal da nota fiscal.</p>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                <x-button type="button" color="outline" @click="$dispatch('modal-close', 'nfce-import-modal')">
                    Cancelar
                </x-button>
                <x-button type="submit">
                    <x-heroicon-o-arrow-down-tray class="size-4" />
                    Enviar para importação
                </x-button>
            </div>
        </form>
    </x-modal>

    @if ($errors->has('url'))
        <div class="contents" x-data x-init="$dispatch('modal-open', 'nfce-import-modal')"></div>
    @endif
</x-layouts.financial>
