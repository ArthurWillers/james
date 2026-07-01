<?php

namespace App\Console\Commands;

use App\Models\FinancialCreditCardInvoice;
use App\Models\FinancialRecurrence;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessFinancialRecurrences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:process-recurrences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa as recorrências financeiras e gera as transações.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $this->info("Iniciando processamento de recorrências para: {$today->toDateString()}");
        $recurrences = FinancialRecurrence::where('is_active', true)
            ->where('next_processing_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $today);
            })
            ->get();

        $processedCount = 0;

        foreach ($recurrences as $recurrence) {
            $nextDate = $recurrence->next_processing_date->copy();

            // Processa todas as ocorrências passadas se tiver atrasado vários meses
            while ($nextDate !== null && $nextDate->lte($today)) {
                if ($recurrence->end_date && $nextDate->gt($recurrence->end_date)) {
                    break;
                }

                $this->processRecurrence($recurrence, $nextDate->copy());
                $processedCount++;

                $nextDate = $this->calculateNextDate($recurrence, $nextDate);
                $recurrence->update(['next_processing_date' => $nextDate]);
            }
        }

        $this->info("Processamento concluído. Transações geradas: {$processedCount}");
    }

    private function calculateNextDate(FinancialRecurrence $recurrence, Carbon $lastProcessedDate): ?Carbon
    {
        if ($recurrence->frequency === 'monthly') {
            $monthsDiff = $recurrence->start_date->diffInMonths($lastProcessedDate) + 1;
            return $recurrence->start_date->copy()->addMonthsNoOverflow($monthsDiff);
        }

        if ($recurrence->frequency === 'yearly') {
            $yearsDiff = $recurrence->start_date->diffInYears($lastProcessedDate) + 1;
            return $recurrence->start_date->copy()->addYearsNoOverflow($yearsDiff);
        }

        return null;
    }

    private function processRecurrence(FinancialRecurrence $recurrence, Carbon $date): void
    {
        try {
            if ($recurrence->financial_credit_card_id) {
                // Process for Credit Card
                $invoice = FinancialCreditCardInvoice::resolveForDate($recurrence->financialCreditCard, $date);

                $transaction = $invoice->transactions()->create([
                    'financial_account_id' => null, // Na fatura
                    'date' => $date,
                    'type' => $recurrence->type,
                    'amount' => $recurrence->amount,
                    'description' => $recurrence->title,
                    'is_posted' => false, // Nasce não postada por padrão
                    'financial_recurrence_id' => $recurrence->id,
                ]);
            } else if ($recurrence->financial_account_id) {
                // Process for Account
                $transaction = $recurrence->financialAccount->transactions()->create([
                    'date' => $date,
                    'type' => $recurrence->type,
                    'amount' => $recurrence->amount,
                    'description' => $recurrence->title,
                    'is_posted' => true, // Conta corrente nasce postada, conf. regras
                    'financial_recurrence_id' => $recurrence->id,
                ]);
            }

            // Sync tags se houver
            $tagIds = $recurrence->tags()->pluck('financial_tags.id')->toArray();
            if (!empty($tagIds)) {
                $syncData = [];
                foreach ($tagIds as $tagId) {
                    $syncData[$tagId] = ['is_primary' => false]; // TODO: Could enhance to inherit primary tag
                }
                $transaction->tags()->sync($syncData);
            }



            $this->info("Recorrência '{$recurrence->title}' processada para {$date->toDateString()}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar recorrência ID {$recurrence->id}: " . $e->getMessage());
            $this->error("Erro ao processar recorrência '{$recurrence->title}'");
        }
    }
}
