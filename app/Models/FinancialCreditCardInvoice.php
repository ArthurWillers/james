<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'financial_credit_card_id',
    'reference_month',
    'closing_date',
    'due_date',
    'paid_at',
    'interest_transaction_id',
])]
class FinancialCreditCardInvoice extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reference_month' => 'date',
            'closing_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    /**
     * Get the credit card that owns this invoice.
     *
     * @return BelongsTo<FinancialCreditCard, $this>
     */
    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(FinancialCreditCard::class, 'financial_credit_card_id');
    }

    /**
     * Get the transactions associated with the invoice.
     *
     * @return HasMany<FinancialTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Get the interest transaction associated with the invoice.
     *
     * @return BelongsTo<FinancialTransaction, $this>
     */
    public function interestTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'interest_transaction_id');
    }

    /**
     * Check if the invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Calculate the total amount of the invoice.
     * Expenses sum, incomes subtract.
     */
    public function total(): float
    {
        return (float) $this->transactions()
            ->where('is_posted', true)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount WHEN type = 'income' THEN -amount ELSE 0 END), 0) as total")
            ->value('total');
    }

    /**
     * Find or create the correct invoice based on the purchase date and card closing/due days.
     */
    public static function resolveForDate(FinancialCreditCard $card, Carbon $date): self
    {
        $referenceMonth = $date->day <= $card->closing_day
            ? $date->copy()->startOfMonth()
            : $date->copy()->addMonth()->startOfMonth();

        $closingDate = $referenceMonth->copy()->day(
            min($card->closing_day, $referenceMonth->daysInMonth)
        );

        $dueMonth = $card->due_day < $card->closing_day
            ? $referenceMonth->copy()->addMonth()
            : $referenceMonth->copy();

        $dueDate = $dueMonth->day(min($card->due_day, $dueMonth->daysInMonth));

        return static::firstOrCreate(
            [
                'financial_credit_card_id' => $card->id,
                'reference_month' => $referenceMonth->toDateString(),
            ],
            [
                'closing_date' => $closingDate,
                'due_date' => $dueDate,
            ]
        );
    }
}
