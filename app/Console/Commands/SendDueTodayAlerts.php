<?php

namespace App\Console\Commands;

use App\Enums\NotificationLevel;
use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SendDueTodayAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:due-today-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia alertas sobre despesas e faturas com vencimento para hoje e amanhã.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $dueTransactions = $this->getDueTransactions($today, $tomorrow);
        $dueInvoices = $this->getDueInvoices($today, $tomorrow);

        $totalCount = $dueTransactions->count() + count($dueInvoices);
        $transactionTotal = (float) $dueTransactions->sum('amount');
        $invoiceTotal = (float) array_sum(array_column($dueInvoices, 'remaining'));
        $totalAmount = $transactionTotal + $invoiceTotal;

        if ($totalCount === 0) {
            $this->info('Nenhum vencimento encontrado para hoje ou amanhã. Notificação não enviada.');

            return Command::SUCCESS;
        }

        $this->info("Encontrados {$totalCount} vencimento(s) no valor total de R$ {$totalAmount}. Enviando notificações.");

        $details = [
            'Quantidade' => "{$totalCount} ".($totalCount !== 1 ? 'lançamentos' : 'lançamento'),
            'Valor Total' => formatCurrency($totalAmount),
        ];

        // Adiciona cada fatura de cartão detalhada
        foreach ($dueInvoices as $item) {
            $dueLabel = $item['due_date']->isSameDay($today) ? 'Hoje' : 'Amanhã';
            $txCount = $item['transactions_count'];
            $extra = "{$txCount} ".($txCount === 1 ? 'lançamento' : 'lançamentos');
            $key = "[{$dueLabel}] Fatura {$item['card_name']}";
            $details[$key] = formatCurrency($item['remaining'])." ({$extra})";
        }

        // Adiciona cada despesa avulsa detalhada
        foreach ($dueTransactions as $tx) {
            $dueLabel = $tx->date->isSameDay($today) ? 'Hoje' : 'Amanhã';
            $dest = $tx->account ? " ({$tx->account->name})" : '';
            $key = "[{$dueLabel}] {$tx->description}";
            $details[$key] = formatCurrency($tx->amount).$dest;
        }

        $notification = new GeneralNotification(
            title: 'Vencimentos Próximos',
            message: 'Existem lançamentos e/ou faturas com vencimento para hoje ou amanhã.',
            actionUrl: route('financial.transactions.index'),
            level: NotificationLevel::Warning,
            details: $details,
        );

        foreach (User::all() as $user) {
            $user->notify($notification);
        }

        return Command::SUCCESS;
    }

    /**
     * Retorna a coleção de despesas pendentes sem fatura com vencimento no período.
     *
     * @return Collection<int, FinancialTransaction>
     */
    private function getDueTransactions(Carbon $startDate, Carbon $endDate)
    {
        return FinancialTransaction::with('account')
            ->pending()
            ->expenses()
            ->withoutTransfers()
            ->withoutInvoice()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Retorna as faturas de cartão não pagas com vencimento no período com dados estruturados.
     *
     * @return array<int, array{card_name: string, remaining: float, due_date: Carbon, transactions_count: int}>
     */
    private function getDueInvoices(Carbon $startDate, Carbon $endDate): array
    {
        $invoices = FinancialCreditCardInvoice::with([
            'creditCard',
            'transactions' => fn ($query) => $query->withoutDrafts(),
        ])
            ->withTotalAmount()
            ->unpaid()
            ->dueBetween($startDate, $endDate)
            ->orderBy('due_date')
            ->get();

        $result = [];

        foreach ($invoices as $invoice) {
            $remaining = $invoice->total() - (float) $invoice->amount_paid;

            if ($remaining > 0) {
                $result[] = [
                    'card_name' => $invoice->creditCard?->name ?? 'Cartão',
                    'remaining' => $remaining,
                    'due_date' => $invoice->due_date,
                    'transactions_count' => $invoice->transactions->count(),
                ];
            }
        }

        return $result;
    }
}
