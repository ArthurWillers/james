<x-finance.kpi-card
    title="Acertos"
    value="{{ formatCurrency(abs($netBalance)) }}"
    icon="heroicon-o-scale"
    :color="$netBalance > 0 ? 'green' : ($netBalance < 0 ? 'red' : 'neutral')"
    href="{{ route('settlements.contact.show', $contact) }}"
    class="h-full"
>
    {{ $netBalance > 0 ? 'Você tem a receber' : ($netBalance < 0 ? 'Você tem a pagar' : 'Tudo quitado') }}
</x-finance.kpi-card>
