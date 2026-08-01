<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class FinancialTransactionPayment extends Model
{
    /** @use HasFactory<\Database\Factories\FinancialTransactionPaymentFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'financial_transaction_id',
        'financial_account_id',
        'financial_credit_card_invoice_id',
        'amount',
        'is_posted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_posted' => 'boolean',
        ];
    }

    /**
     * Get the transaction associated with the payment.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'financial_transaction_id');
    }

    /**
     * Get the account associated with the payment.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    /**
     * Get the invoice associated with the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FinancialCreditCardInvoice::class, 'financial_credit_card_invoice_id');
    }

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('financial_transaction_payment');
    }
}
