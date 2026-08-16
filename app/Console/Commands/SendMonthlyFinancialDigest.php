<?php

namespace App\Console\Commands;

use App\Enums\NotificationLevel;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendMonthlyFinancialDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:monthly-digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia o resumo financeiro consolidado do mês anterior.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lastMonth = Carbon::today()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();

        $totals = FinancialTransaction::withoutTransfers()
            ->withoutPartialPayments()
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
            ")
            ->first();

        $totalIncome = (float) $totals->total_income;
        $totalExpense = (float) $totals->total_expense;
        $netResult = $totalIncome - $totalExpense;

        $monthLabel = \Str::title($lastMonth->isoFormat('MMMM [de] YYYY'));

        $level = $netResult >= 0 ? NotificationLevel::Success : NotificationLevel::Info;

        $notification = new GeneralNotification(
            title: "Resumo Financeiro — {$monthLabel}",
            message: "O balanço financeiro de {$monthLabel} foi consolidado com sucesso.",
            actionUrl: route('financial.dashboard'),
            level: $level,
            details: [
                'Receitas' => formatCurrency($totalIncome),
                'Despesas' => formatCurrency($totalExpense),
                'Resultado Líquido' => formatCurrency($netResult),
            ],
        );

        foreach (User::all() as $user) {
            $user->notify($notification);
        }

        $this->info("Resumo financeiro de {$monthLabel} enviado com sucesso.");

        return Command::SUCCESS;
    }
}
