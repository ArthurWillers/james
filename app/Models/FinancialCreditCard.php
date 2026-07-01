<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'name',
    'financial_account_id',
    'credit_limit',
    'closing_day',
    'due_day',
])]
class FinancialCreditCard extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'closing_day' => 'integer',
            'due_day' => 'integer',
        ];
    }

    /**
     * Get the financial account that pays this credit card's invoices.
     *
     * @return BelongsTo<FinancialAccount, $this>
     */
    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    /**
     * Get the invoices associated with the credit card.
     *
     * @return HasMany<FinancialCreditCardInvoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(FinancialCreditCardInvoice::class);
    }

    /**
     * Get the recurrences charged directly to this credit card.
     *
     * @return HasMany<FinancialRecurrence, $this>
     */
    public function recurrences(): HasMany
    {
        return $this->hasMany(FinancialRecurrence::class);
    }

    /**
     * Calculate used limit across all non-paid invoices.
     */
    public function usedLimit(): float
    {
        return (float) $this->invoices()
            ->whereNull('paid_at')
            ->get()
            ->sum(fn ($invoice) => max(0, $invoice->total() - $invoice->amount_paid));
    }

    /**
     * Update closing schedule and recalculate affected open invoices.
     */
    public function updateClosingSchedule(int $closingDay, int $dueDay): void
    {
        $this->update([
            'closing_day' => $closingDay,
            'due_day' => $dueDay,
        ]);

        $this->invoices()
            ->whereNull('paid_at')
            ->get()
            ->filter(fn ($invoice) => $invoice->status() === 'open')
            ->each(fn ($invoice) => $invoice->recalculateDates());
    }

    /**
     * Create installment purchase spreading over invoices.
     */
    public function createInstallmentPurchase(
        Carbon $purchaseDate,
        float $totalAmount,
        int $installments,
        string $description,
        ?array $tagIds = null
    ): void {
        $firstInvoice = FinancialCreditCardInvoice::resolveForDate($this, $purchaseDate);
        $installmentAmount = round($totalAmount / $installments, 2);

        for ($i = 1; $i <= $installments; $i++) {
            if ($i === 1) {
                $invoice = $firstInvoice;
            } else {
                $invoice = FinancialCreditCardInvoice::resolveForDate(
                    $this,
                    $purchaseDate->copy()->addMonthsNoOverflow($i - 1)
                );
            }

            // Adjust amount for the last installment if there are rounding diffs
            $amount = $i === $installments
                ? $totalAmount - ($installmentAmount * ($installments - 1))
                : $installmentAmount;

            $transaction = $invoice->transactions()->create([
                'financial_account_id' => null,
                'date' => $purchaseDate,
                'type' => 'expense',
                'amount' => $amount,
                'description' => $description,
                'is_posted' => false,
                'installment_current' => $i,
                'installment_total' => $installments,
            ]);

            if (! empty($tagIds)) {
                $transaction->tags()->attach($tagIds);
            }
        }
    }
}
